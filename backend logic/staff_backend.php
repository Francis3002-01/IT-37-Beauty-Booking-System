<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!in_array(strtolower($_SESSION['user_role'] ?? ''), ['admin', 'staff'], true)) {
    http_response_code(403);
    exit('You must be logged in as Admin or Staff to manage staff records.');
}

/* BACKEND LOGIC FOR STAFF.PHP
 * ------------------------------------------------------------------
 * Add and edit are functional.
 * Deletion possible only for staff without a linked user account.
 * [table] staff: staffID, staffType, lastName, firstName,
 * address, contactNo, employmentStatus, dateJoined, dateSeparated.
 */

//function to handle outcomes (errors and success messages) and return to staff.php
function returnToStaff($status,$message)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['staff_status'] =$status;
    $_SESSION['staff_message'] =$message;
    header('Location: ../staff.php', true, 303);
    exit;
}

//retrieving and trimming string POST parameters
function getStaffFormValue($fieldName)
{
    if (!isset($_POST[$fieldName])) {
        return '';
    }
    if (!is_string($_POST[$fieldName])) {
        returnToStaff('error', 'Invalid form field: ' . $fieldName);
    }
    return trim($_POST[$fieldName]);
}

//check the image contents and read bytes for MySQL BLOB storage
function uploadStaffImage()
{
    if (!isset($_FILES['profileImage'])) return null;
    $file =$_FILES['profileImage'];
    if (!isset($file['error']) or !is_int($file['error'])) {
        throw new RuntimeException('Invalid profile picture upload.');
    }
    if ($file['error'] == UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] != UPLOAD_ERR_OK) {
        throw new RuntimeException('Profile picture upload failed. Choose an image up to 2 MB.');
    }
    if (!isset($file['tmp_name']) or !is_string($file['tmp_name']) or !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid profile picture upload.');
    }
    $image = @getimagesize($file['tmp_name']);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!$image or $image['mime'] !=$mime) {
        throw new RuntimeException('Choose a valid JPG, PNG or WebP image.');
    }
    if ($mime != 'image/jpeg' and $mime != 'image/png' and$mime != 'image/webp') {
        throw new RuntimeException('Choose a JPG, PNG or WebP image.');
    }
    $data = file_get_contents($file['tmp_name']);
    if ($data === false) throw new RuntimeException('Unable to read the profile picture.');
    return ['data' => $data, 'mime' =>$mime];
}

//ensure request method is POST, else return to staff.php with error message
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    returnToStaff('error', 'Please submit the staff form.');
}

//guarantee that actions can only be create, edit, or delete. If not, return to staff.php with error message
$action = getStaffFormValue('action');
if ($action != 'create' and $action != 'edit' and$action != 'delete') {
    returnToStaff('error', 'Invalid staff action.');
}

//passed pre-validation, get staffID for edit and delete
$staffID = getStaffFormValue('staffID');

//for editing and deleting, ensure staffID is a valid positive integer within the range of a 32-bit signed integer. If not, return to staff.php with error message
if ($action == 'edit' or$action == 'delete') {
    if (!ctype_digit($staffID) or $staffID < 1 or$staffID > 2147483647) {
        returnToStaff('error', 'Invalid staff ID.');
    }
}

//staff links to user accounts, so delete both safely using a transaction
if ($action == 'delete') {
    try {
        require_once __DIR__ . '/../config/database.php';
        
        // 1. Optional check: Prevent deleting Active staff members directly
        $checkStmt =$conn->prepare('SELECT employmentStatus FROM staff WHERE staffID = ? LIMIT 1');
        $checkStmt->bind_param('i',$staffID);
        $checkStmt->execute();$checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $checkStmt->bind_result($employmentStatus);
            $checkStmt->fetch();$checkStmt->close();

            if ($employmentStatus === 'Active') {$conn->close();
                returnToStaff('error', 'Active staff members cannot be deleted. Please set them to Inactive first if needed.');
            }
        } else {
            $checkStmt->close();$conn->close();
            returnToStaff('error', 'The selected record no longer exists.');
        }

        // 2. Begin transaction for safe deletion of user account and staff record
        $conn->begin_transaction();

        // Delete the linked user account first
        $statement =$conn->prepare('DELETE FROM `user` WHERE staffID = ?');
        $statement->bind_param('i',$staffID);
        $statement->execute();$statement->close();

        // Delete the staff member
        $statement =$conn->prepare('DELETE FROM `staff` WHERE staffID = ?');
        $statement->bind_param('i', $staffID);$statement->execute();
        $deleted =$statement->affected_rows;
        $statement->close();

        $conn->commit();$conn->close();

        if ($deleted == 0) {
            returnToStaff('error', 'The selected record no longer exists.');
        }
        returnToStaff('success', 'Staff member and their user account deleted successfully.');
        
    } catch (mysqli_sql_exception $error) {
        if (isset($conn) and$conn instanceof mysqli) {
            @$conn->rollback();
        }
        // Foreign key constraint error code 1451 (referenced by appointments/transactions)
        if ($error->getCode() == 1451) {
            returnToStaff('error', 'This record cannot be deleted because it is referenced by past appointments or transactions.');
        }
        error_log('Staff member deletion failed: ' . $error->getMessage());
        returnToStaff('error', 'Unable to delete the record. Please try again.');
    }
}

//store form values in variables for validation to prep for insertion, edits, and deletion.
$firstName = getStaffFormValue('firstName');
$lastName = getStaffFormValue('lastName');$address = getStaffFormValue('address');
$contactNo = getStaffFormValue('contactNo');$staffType = getStaffFormValue('staffType');
$employmentStatus = getStaffFormValue('employmentStatus');$dateJoined = getStaffFormValue('dateJoined');
$dateSeparated = getStaffFormValue('dateSeparated');$username = getStaffFormValue('username');

//check role and employment status before saving.
if ($staffType != 'Receptionist') {
    returnToStaff('error', 'Select a valid role.');
}
if ($employmentStatus != 'Active' and $employmentStatus != 'On Leave' and$employmentStatus != 'Inactive') {
    returnToStaff('error', 'Select a valid employment status.');
}

//check actual calendar dates, not only their text format
function validStaffDate($value)
{
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$value)) return false;
    $parts = explode('-',$value);
    return $parts[0] >= 1000 and checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}
if (!validStaffDate($dateJoined)) {
    returnToStaff('error', 'Enter a valid date joined.');
}
if ($employmentStatus == 'Inactive' and$dateSeparated == '') {
    returnToStaff('error', 'Date separated is required when employment status is Inactive.');
}
if ($employmentStatus == 'Active' or $employmentStatus == 'On Leave') {$dateSeparated = '';
}
if ($dateSeparated != '') {
    if (!validStaffDate($dateSeparated) or $dateSeparated <$dateJoined) {
        returnToStaff('error', 'Date separated must be a valid date on or after date joined.');
    }
}
if ($dateSeparated == '')$dateSeparated = null;

//name cannot be empty
if ($firstName == '' or mb_strlen($firstName) > 50) {
    returnToStaff('error', 'First name must contain 1 to 50 characters.');
}
if ($lastName == '' or mb_strlen($lastName) > 50) {
    returnToStaff('error', 'Last name must contain 1 to 50 characters.');
}

// Username validation for both create and edit
if ($username == '' or mb_strlen($username) > 50) {
    returnToStaff('error', 'Username is required and must be at most 50 characters.');
}

//allow accented letters, spaces, apostrophes, periods and hyphens
if (!preg_match('/\p{L}/u', $firstName) or !preg_match("/^[\p{L}\p{M} .’'\-]+$/u", $firstName)) {
    returnToStaff('error', 'First name must contain letters.');
}
if (!preg_match('/\p{L}/u', $lastName) or !preg_match("/^[\p{L}\p{M} .’'\-]+$/u", $lastName)) {
    returnToStaff('error', 'Last name must contain letters.');
}

//address cannot be empty and allow symbols
if ($address == '' or mb_strlen($address) > 255) {
    returnToStaff('error', 'Address is required and must contain at most 255 characters.');
}

//contactno cannot be empty and must be exactly 11 digits
if (!ctype_digit($contactNo) or strlen($contactNo) != 11) {
    returnToStaff('error', 'Contact number must contain exactly 11 digits.');
}

$imageData = null;
$imageMime = null;
try {
    require_once __DIR__ . '/../config/database.php';

    if (!isset($conn) or !($conn instanceof mysqli)) {
        returnToStaff('error', 'The database connection is unavailable.');
    }

    // Check duplicate full name in staff table
    $excludedID = 0;
    if ($action == 'edit') {
        $excludedID = (int)$staffID;
    }
    $sql = 'SELECT staffID FROM staff WHERE TRIM(firstName) = ? AND TRIM(lastName) = ? AND staffID != ? LIMIT 1';
    $statement = $conn->prepare($sql);
    $statement->bind_param('ssi',$firstName, $lastName,$excludedID);
    $statement->execute();$statement->store_result();
    $duplicateName =$statement->num_rows > 0;
    $statement->close();

    if ($duplicateName) {$conn->close();
        returnToStaff('error', 'A staff member with the same first and last name already exists.');
    }

    if ($action == 'create') { // Insert new staff and corresponding user account
        // Check if username already exists in `user` table
        $checkUser =$conn->prepare('SELECT userID FROM `user` WHERE userName = ? LIMIT 1');
        $checkUser->bind_param('s',$username);
        $checkUser->execute();$checkUser->store_result();
        $usernameExists =$checkUser->num_rows > 0;
        $checkUser->close();

        if ($usernameExists) {$conn->close();
            returnToStaff('error', 'This username is already taken. Please choose another one.');
        }

        // Begin transaction cleanly
        $conn->begin_transaction();

        // 1. Insert Staff
        $sql = 'INSERT INTO staff (staffType, lastName, firstName, address, contactNo, employmentStatus, dateJoined, dateSeparated, imageData, imageMime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $statement =$conn->prepare($sql);$image = uploadStaffImage();
        $imageData =$image['data'] ?? null;
        $imageMime =$image['mime'] ?? null;
        $statement->bind_param('ssssssssss',$staffType, $lastName,$firstName, $address,$contactNo, $employmentStatus,$dateJoined, $dateSeparated,$imageData, $imageMime);$statement->execute();
        $newStaffId =$conn->insert_id;
        $statement->close();

        // 2. Generate secure random alphanumeric password (8 characters)
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';$rawPassword = '';
        $maxIndex = strlen($chars) - 1;
        for ($i = 0; $i < 8; $i++) {$rawPassword .= $chars[random_int(0,$maxIndex)];
        }
        
        $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
        $role = 'Receptionist';$accountStatus = 'Active';

        // 3. Insert User Account linked to staffID using the exact typed username
        $userSql = 'INSERT INTO `user` (staffID, userName, passwordHash, role, accountStatus) VALUES (?, ?, ?, ?, ?)';
        $userStmt = $conn->prepare($userSql);
        $userStmt->bind_param('issss',$newStaffId, $username,$passwordHash, $role,$accountStatus);
        $userStmt->execute();$userStmt->close();

        $conn->commit();$conn->close();

        // Success message displaying the generated readable password
        returnToStaff('success', "Staff added successfully! Username: '{$username}' Password: {$rawPassword}");

    } else if ($action == 'edit') { // Update existing staff and user account in database
        $statement =$conn->prepare('SELECT staffID FROM staff WHERE staffID = ?');
        $statement->bind_param('i',$staffID);
        $statement->execute();$statement->store_result();
        $staffExists =$statement->num_rows > 0;
        $statement->close();
        
        if (!$staffExists) {$conn->close();
            returnToStaff('error', 'The selected staff member no longer exists.');
        }

        // Check if the username is already taken by *another* user account
        $checkUser =$conn->prepare('SELECT userID FROM `user` WHERE userName = ? AND staffID != ? LIMIT 1');
        $checkUser->bind_param('si', $username,$staffID);
        $checkUser->execute();$checkUser->store_result();
        $usernameTaken =$checkUser->num_rows > 0;
        $checkUser->close();

        if ($usernameTaken) {$conn->close();
            returnToStaff('error', 'This username is already taken by another account.');
        }

        $conn->begin_transaction();

        // 1. Update Staff Table (10 parameters + 1 staffID = 11 values total)
        $sql = 'UPDATE staff SET staffType = ?, lastName = ?, firstName = ?, address = ?, contactNo = ?, employmentStatus = ?, dateJoined = ?, dateSeparated = ?, imageData = COALESCE(?, imageData), imageMime = COALESCE(?, imageMime) WHERE staffID = ?';
        $statement =$conn->prepare($sql);$image = uploadStaffImage();
        $imageData =$image['data'] ?? null;
        $imageMime =$image['mime'] ?? null;
        $statement->bind_param('ssssssssssi',$staffType, $lastName,$firstName, $address,$contactNo, $employmentStatus,$dateJoined, $dateSeparated,$imageData, $imageMime,$staffID);
        $statement->execute();$statement->close();

        // 2. Update User Table Username
        $userSql = 'UPDATE `user` SET userName = ? WHERE staffID = ?';
        $userStmt =$conn->prepare($userSql);$userStmt->bind_param('si', $username,$staffID);
        $userStmt->execute();$userStmt->close();

        $conn->commit();$conn->close();

        returnToStaff('success', 'Staff and user account updated successfully.');
    }
} catch (mysqli_sql_exception $error) {
    if (isset($conn) and$conn instanceof mysqli) {
        @$conn->rollback();
    }
    error_log('Staff save failed: ' . $error->getMessage());
    returnToStaff('error', 'Unable to save the staff. Please try again.');
} catch (RuntimeException $error) {
    if (isset($conn) and$conn instanceof mysqli) {
        @$conn->rollback();
    }
    returnToStaff('error', $error->getMessage());
}