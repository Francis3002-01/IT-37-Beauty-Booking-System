<?php
/* BACKEND LOGIC FOR STAFF.PHP
 * Add and edit staff records.
 * Deletion is allowed only for staff without a linked user account.
 */

// Handle outcomes and return to staff.php
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

// Retrieve and trim POST values
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

// Only accept POST requests
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    returnToStaff('error', 'Please submit the staff form.');
}

// Validate action
$action = getStaffFormValue('action');

if (!in_array($action, ['create', 'edit', 'delete'], true)) {
    returnToStaff('error', 'Invalid staff action.');
}

// Validate staff ID for edit and delete
$staffID = getStaffFormValue('staffID');

if ($action === 'edit' OR$action === 'delete') {
    if (
        !ctype_digit($staffID) OR
        (float)$staffID < 1 OR
        (float)$staffID > 2147483647
    ) {
        returnToStaff('error', 'Invalid staff ID.');
    }
}

// DELETE STAFF
if ($action === 'delete') {
    try {
        require_once __DIR__ . '/../config/database.php';

        $statement =$conn->prepare(
            'SELECT userID FROM `user` WHERE staffID = ? LIMIT 1'
        );
        $statement->bind_param('i',$staffID);
        $statement->execute();$statement->store_result();

        $used =$statement->num_rows > 0;
        $statement->close();

        if ($used) {$conn->close();

            returnToStaff(
                'error',
                'This staff member cannot be deleted because they have a user account. Set them to Inactive and disable the account instead.'
            );
        }

        $statement =$conn->prepare(
            'DELETE FROM `staff` WHERE staffID = ?'
        );
        $statement->bind_param('i', $staffID);$statement->execute();

        $deleted =$statement->affected_rows;
        $statement->close();$conn->close();

        if ($deleted == 0) {
            returnToStaff(
                'error',
                'The selected record no longer exists.'
            );
        }

        returnToStaff('success', 'Staff member deleted successfully.');

    } catch (mysqli_sql_exception $error) {
        if ($error->getCode() == 1451) {
            returnToStaff(
                'error',
                'This record cannot be deleted because it is referenced by another record.'
            );
        }

        error_log('Staff member deletion failed: ' . $error->getMessage());

        returnToStaff(
            'error',
            'Unable to delete the record. Please try again.'
        );
    }
}

// Retrieve staff form values
$firstName = getStaffFormValue('firstName');$lastName = getStaffFormValue('lastName');
$address = getStaffFormValue('address');$contactNo = getStaffFormValue('contactNo');
$staffType = getStaffFormValue('staffType');$employmentStatus = getStaffFormValue('employmentStatus');
$dateJoined = getStaffFormValue('dateJoined');$dateSeparated = getStaffFormValue('dateSeparated');

// Validate staff role and employment status
if ($staffType !== 'Receptionist') {
    returnToStaff('error', 'Select a valid role.');
}

if (!in_array(
    $employmentStatus,
    ['Active', 'On Leave', 'Inactive'],
    true
)) {
    returnToStaff('error', 'Select a valid employment status.');
}

// Validate dates
function validStaffDate($value)
{
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$value)) {
        return false;
    }

    $parts = explode('-',$value);

    return $parts[0] >= 1000 &&
        checkdate(
            (int)$parts[1],
            (int)$parts[2],             (int)$parts[0]
        );
}

if (!validStaffDate($dateJoined)) {
    returnToStaff('error', 'Enter a valid date joined.');
}

if ($employmentStatus === 'Inactive' &&$dateSeparated === '') {
    returnToStaff(
        'error',
        'Date separated is required when employment status is Inactive.'
    );
}

// Active and On Leave staff have not separated
if (
    $employmentStatus === 'Active' OR$employmentStatus === 'On Leave'
) {
    $dateSeparated = '';
}

if ($dateSeparated !== '') {
    if (
        !validStaffDate($dateSeparated) OR
        $dateSeparated <$dateJoined
    ) {
        returnToStaff(
            'error',
            'Date separated must be a valid date on or after date joined.'
        );
    }
}

// Empty optional date becomes SQL NULL
if ($dateSeparated === '') {$dateSeparated = null;
}

// Validate names
if ($firstName === '' OR mb_strlen($firstName) > 50) {
    returnToStaff(
        'error',
        'First name must contain 1 to 50 characters.'
    );
}

if ($lastName === '' OR mb_strlen($lastName) > 50) {
    returnToStaff(
        'error',
        'Last name must contain 1 to 50 characters.'
    );
}

// Allow accented letters, spaces, apostrophes, periods and hyphens
if (
    !preg_match('/\p{L}/u', $firstName) OR
    !preg_match("/^[\p{L}\p{M} .’'\-]+$/u", $firstName)
) {
    returnToStaff(
        'error',
        'First name must contain letters. It may use spaces, apostrophes, periods or hyphens.'
    );
}

if (
    !preg_match('/\p{L}/u', $lastName) OR
    !preg_match("/^[\p{L}\p{M} .’'\-]+$/u", $lastName)
) {
    returnToStaff(
        'error',
        'Last name must contain letters. It may use spaces, apostrophes, periods or hyphens.'
    );
}

// Validate address
if ($address === '' OR mb_strlen($address) > 255) {
    returnToStaff(
        'error',
        'Address is required and must contain at most 255 characters.'
    );
}

if (
    !preg_match('/\p{L}/u', $address) OR
    !preg_match("/^[\p{L}\p{M}\p{N} .,\r\n’'\-\/#():]+$/u", $address)
) {
    returnToStaff(
        'error',
        'Address must contain letters and use ordinary address text, numbers and punctuation.'
    );
}

// Validate contact number
if (!ctype_digit($contactNo) OR strlen($contactNo) !== 11) {
    returnToStaff(
        'error',
        'Contact number must contain exactly 11 digits.'
    );
}

try {
    require_once __DIR__ . '/../config/database.php';

    if (!isset($conn) OR !($conn instanceof mysqli)) {
        returnToStaff(
            'error',
            'The database connection is unavailable.'
        );
    }

    // Check for duplicate full names
    $excludedID = 0;

    if ($action === 'edit') {
        $excludedID = (int)$staffID;
    }

    $sql = 'SELECT staffID FROM staff
        WHERE TRIM(firstName) = ?
          AND TRIM(lastName) = ?
          AND staffID != ?
        LIMIT 1';

    $statement =$conn->prepare($sql);$statement->bind_param(
        'ssi',
        $firstName,
        $lastName,$excludedID
    );
    $statement->execute();$statement->store_result();

    $duplicateName =$statement->num_rows > 0;
    $statement->close();

    if ($duplicateName) {$conn->close();

        returnToStaff(
            'error',
            'A staff member with the same first and last name already exists.'
        );
    }

    // CREATE STAFF
    if ($action === 'create') {

        $userName = getStaffFormValue('userName');

        if ($userName === '' OR mb_strlen($userName) > 50) {
            returnToStaff('error', 'Username is required and must contain at most 50 characters.');
        }

        // Check if username already exists in user table
        $stmtCheck =$conn->prepare('SELECT userID FROM `user` WHERE userName = ? LIMIT 1');
        $stmtCheck->bind_param('s',$userName);
        $stmtCheck->execute();$stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            $stmtCheck->close();$conn->close();
            returnToStaff('error', 'Username is already taken.');
        }
        $stmtCheck->close();

        // Auto-generate random 10-character password & hash it
        $generatedPassword = substr(bin2hex(random_bytes(8)), 0, 10);
        $passwordHash = password_hash($generatedPassword, PASSWORD_DEFAULT);

        // Begin transaction
        $conn->begin_transaction();

        try {
            // 1. Insert into staff table
            $sqlStaff = 'INSERT INTO staff
                (staffType, lastName, firstName, address, contactNo,
                 employmentStatus, dateJoined, dateSeparated)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

            $statement =$conn->prepare($sqlStaff);$statement->bind_param(
                'ssssssss',
                $staffType,$lastName,
                $firstName,$address,
                $contactNo,$employmentStatus,
                $dateJoined,$dateSeparated
            );
            $statement->execute();
            $newStaffID =$conn->insert_id;
            $statement->close();

            // 2. Insert into user table with generated password
            $sqlUser = 'INSERT INTO `user` (staffID, userName, passwordHash, role, accountStatus)
                        VALUES (?, ?, ?, ?, ?)';

            $stmtUser =$conn->prepare($sqlUser);$accountStatus = 'Active';

            $stmtUser->bind_param(
                'issss',
                $newStaffID,
                $userName,$passwordHash,
                $staffType,$accountStatus
            );
            $stmtUser->execute();$stmtUser->close();

            // Commit transaction
            $conn->commit();$conn->close();

            returnToStaff(
                'success',
                "Staff account created successfully! Username: {$userName} - Generated Password: {$generatedPassword}"
            );

        } catch (mysqli_sql_exception $e) {
            $conn->rollback();$conn->close();
            error_log('Staff/User creation failed: ' . $e->getMessage());
            returnToStaff('error', 'Unable to complete staff account creation.');
        }

    // EDIT STAFF
    } elseif ($action === 'edit') {
        $statement =$conn->prepare(
            'SELECT staffID FROM staff WHERE staffID = ?'
        );
        $statement->bind_param('i',$staffID);
        $statement->execute();$statement->store_result();

        $staffExists =$statement->num_rows > 0;
        $statement->close();

        if (!$staffExists) {$conn->close();

            returnToStaff(
                'error',
                'The selected staff member no longer exists.'
            );
        }

        $sql = 'UPDATE staff
            SET staffType = ?,
                lastName = ?,
                firstName = ?,
                address = ?,
                contactNo = ?,
                employmentStatus = ?,
                dateJoined = ?,
                dateSeparated = ?
            WHERE staffID = ?';

        $statement =$conn->prepare($sql);$statement->bind_param(
            'ssssssssi',
            $staffType,
            $lastName,$firstName,
            $address,$contactNo,
            $employmentStatus,$dateJoined,
            $dateSeparated,$staffID
        );

        $statement->execute();
        $statement->close();$conn->close();

        returnToStaff('success', 'Staff updated successfully.');
    }

} catch (mysqli_sql_exception $error) {
    error_log('Staff save failed: ' . $error->getMessage());

    returnToStaff(
        'error',
        'Unable to save the staff. Please try again.'
    );
}
