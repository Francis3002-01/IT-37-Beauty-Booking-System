<?php
/* BACKEND LOGIC FOR BEAUTICIANS.PHP
 * ------------------------------------------------------------------
 * Add and edit are functional.
 * Deletion possible only for beauticians not yet in any appointments
 * [table] beauticians: beauticianID, beauticianType, lastName, firstName,
 * address, contactNo, employmentStatus, dateJoined, dateSeparated.
 */

//function to handle outcomes (errors and success messages) and return to beauticians.php
function returnToBeauticians($status, $message)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['beautician_status'] = $status;
    $_SESSION['beautician_message'] = $message;
    header('Location: ../beauticians.php', true, 303);
    exit;
}

//retrieving and trimming string POST parameters
function getBeauticianFormValue($fieldName)
{
    if (!isset($_POST[$fieldName])) {
        return '';
    }
    if (!is_string($_POST[$fieldName])) {
        returnToBeauticians('error', 'Invalid form field: ' . $fieldName);
    }
    return trim($_POST[$fieldName]);
}

//check the image contents and read bytes for MySQL BLOB storage
function uploadBeauticianImage()
{
    if (!isset($_FILES['profileImage'])) return null;
    $file = $_FILES['profileImage'];
    if (!isset($file['error']) || !is_int($file['error'])) {
        throw new RuntimeException('Invalid profile picture upload.');
    }
    if ($file['error'] == UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] != UPLOAD_ERR_OK) {
        throw new RuntimeException('Profile picture upload failed. Choose an image up to 2 MB.');
    }
    if (!isset($file['tmp_name']) || !is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid profile picture upload.');
    }
    if (filesize($file['tmp_name']) > 2 * 1024 * 1024) {
        throw new RuntimeException('Profile picture must be no larger than 2 MB.');
    }
    $image = @getimagesize($file['tmp_name']);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!$image || $image['mime'] != $mime) {
        throw new RuntimeException('Choose a valid JPG, PNG or WebP image.');
    }
    if ($mime != 'image/jpeg' && $mime != 'image/png' && $mime != 'image/webp') {
        throw new RuntimeException('Choose a JPG, PNG or WebP image.');
    }
    $data = file_get_contents($file['tmp_name']);
    if ($data === false) throw new RuntimeException('Unable to read the profile picture.');
    return ['data' => $data, 'mime' => $mime];
}

//ensure request method is POST, else return to beauticians.php with error message
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    returnToBeauticians('error', 'Please submit the beautician form.');
}

//guarantee that actions can only be create, edit, or delete. If not, return to beauticians.php with error message
$action = getBeauticianFormValue('action');
if ($action != 'create' && $action != 'edit' && $action != 'delete') {
    returnToBeauticians('error', 'Invalid beautician action.');
}

//passed pre-validation, get beauticianID for edit and delete
$beauticianID = getBeauticianFormValue('beauticianID');

//for editing and deleting, ensure beauticianID is a valid positive integer within the range of a 32-bit signed integer. If not, return to beauticians.php with error message
if ($action == 'edit' || $action == 'delete') {
    if (!ctype_digit($beauticianID) || $beauticianID < 1 || $beauticianID > 2147483647) {
        returnToBeauticians('error', 'Invalid beautician ID.');
    }
}

//check if beautician is in an appointment
if ($action == 'delete') {
    try {
        require_once __DIR__ . '/../config/database.php';
        $statement = $conn->prepare('SELECT appointmentID FROM apt_beautician WHERE beauticianID = ? LIMIT 1');
        $statement->bind_param('i', $beauticianID);
        $statement->execute();
        $statement->store_result();
        $used = $statement->num_rows > 0;
        $statement->close();
        if ($used) {
            $conn->close();
            returnToBeauticians('error', 'This beautician cannot be deleted because they are used in an appointment.');
        }
        $statement = $conn->prepare('SELECT availabilityID FROM availability WHERE beauticianID = ? LIMIT 1');
        $statement->bind_param('i', $beauticianID);
        $statement->execute();
        $statement->store_result();
        $used = $statement->num_rows > 0;
        $statement->close();
        if ($used) {
            $conn->close();
            returnToBeauticians('error', 'This beautician cannot be deleted because they have availability schedules. Remove unused schedules first.');
        }
        $statement = $conn->prepare('DELETE FROM `beauticians` WHERE beauticianID = ?');
        $statement->bind_param('i', $beauticianID);
        $statement->execute();
        $deleted = $statement->affected_rows;
        $statement->close();
        $conn->close();
        if ($deleted == 0) {
            returnToBeauticians('error', 'The selected record no longer exists.');
        }
        returnToBeauticians('success', 'Beautician deleted successfully.');
    } catch (mysqli_sql_exception $error) {
        if ($error->getCode() == 1451) {
            returnToBeauticians('error', 'This record cannot be deleted because it is referenced by another record.');
        }
        error_log('Beautician deletion failed: ' . $error->getMessage());
        returnToBeauticians('error', 'Unable to delete the record. Please try again.');
    }
}

//store form values in variables for validation to prep for insertion, edits, and deletion.
$firstName = getBeauticianFormValue('firstName');
$lastName = getBeauticianFormValue('lastName');
$address = getBeauticianFormValue('address');
$contactNo = getBeauticianFormValue('contactNo');
$beauticianType = getBeauticianFormValue('beauticianType');
$employmentStatus = getBeauticianFormValue('employmentStatus');
$dateJoined = getBeauticianFormValue('dateJoined');
$dateSeparated = getBeauticianFormValue('dateSeparated');

//check specialization and employment status before saving.  god we should really array this
if ($beauticianType != 'Hair Stylist' && $beauticianType != 'Makeup Artist'
    && $beauticianType != 'Nail Artist' && $beauticianType != 'Esthetician'
    && $beauticianType != 'Massage Therapist') {
    returnToBeauticians('error', 'Select a valid specialization.');
}
if ($employmentStatus != 'Active' && $employmentStatus != 'On Leave' && $employmentStatus != 'Inactive') {
    returnToBeauticians('error', 'Select a valid employment status.');
}

//check actual calendar dates, not only their text format
function validBeauticianDate($value)
{
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value)) return false;
    $parts = explode('-', $value);
    return $parts[0] >= 1000 && checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
}
if (!validBeauticianDate($dateJoined)) {
    returnToBeauticians('error', 'Enter a valid date joined.');
}
if ($employmentStatus == 'Inactive' && $dateSeparated == '') {
    returnToBeauticians('error', 'Date separated is required when employment status is Inactive.');
}
//active and on leave beauticians have not separated, so clear the date
if ($employmentStatus == 'Active' || $employmentStatus == 'On Leave') {
    $dateSeparated = '';
}
if ($dateSeparated != '') {
    if (!validBeauticianDate($dateSeparated) || $dateSeparated < $dateJoined) {
        returnToBeauticians('error', 'Date separated must be a valid date on or after date joined.');
    }
}
//an empty optional date is stored as SQL NULL
if ($dateSeparated == '') $dateSeparated = null;


//name cannot be empty
if ($firstName == '' || mb_strlen($firstName) > 50) {
    returnToBeauticians('error', 'First name must contain 1 to 50 characters.');
}
if ($lastName == '' || mb_strlen($lastName) > 50) {
    returnToBeauticians('error', 'Last name must contain 1 to 50 characters.');
}

//allow accented letters, spaces, apostrophes, periods and hyphens lngc
if (!preg_match('/\p{L}/u', $firstName)
    || !preg_match("/^[\p{L}\p{M} .’'\-]+$/u", $firstName)) {
    returnToBeauticians('error', 'First name must contain letters. It may use spaces, apostrophes, periods or hyphens.');
}
if (!preg_match('/\p{L}/u', $lastName)
    || !preg_match("/^[\p{L}\p{M} .’'\-]+$/u", $lastName)) {
    returnToBeauticians('error', 'Last name must contain letters. It may use spaces, apostrophes, periods or hyphens.');
}

//address cannot be empty and allow symbols
if ($address == '' || mb_strlen($address) > 255) {
    returnToBeauticians('error', 'Address is required and must contain at most 255 characters.');
}
if (!preg_match('/\p{L}/u', $address)
    || !preg_match("/^[\p{L}\p{M}\p{N} .,\r\n’'\-\/#():]+$/u", $address)) {
    returnToBeauticians('error', 'Address must contain letters and use ordinary address text, numbers and punctuation.');
}

//contactno cannot be empty and must be exactly 11 digits
if (!ctype_digit($contactNo) || strlen($contactNo) != 11) {
    returnToBeauticians('error', 'Contact number must contain exactly 11 digits.');
}

$imageData = null;
$imageMime = null;
try {
    require_once __DIR__ . '/../config/database.php';

    if (!isset($conn) || !($conn instanceof mysqli)) { //check if $conn is active
        returnToBeauticians('error', 'The database connection is unavailable.');
    }

    //check if duplicate full name exists in db. gets a copy of the beauticianID to compare with the current beauticianID. If it exists, return to beauticians.php with error message
    $excludedID = 0;
    if ($action == 'edit') {
        $excludedID = (int) $beauticianID;
    }
    $sql = 'SELECT beauticianID FROM beauticians
        WHERE TRIM(firstName) = ? AND TRIM(lastName) = ? AND beauticianID != ?
        LIMIT 1';
    $statement = $conn->prepare($sql);
    $statement->bind_param('ssi', $firstName, $lastName, $excludedID);
    $statement->execute();
    $statement->store_result();
    $duplicateName = $statement->num_rows > 0;
    $statement->close();

    if ($duplicateName) {
        $conn->close();
        returnToBeauticians('error', 'A beautician with the same first and last name already exists.');
    }

    if ($action == 'create') { //insert new beautician into database
        $sql = 'INSERT INTO beauticians (beauticianType, lastName, firstName, address, contactNo, employmentStatus, dateJoined, dateSeparated, imageData, imageMime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $statement = $conn->prepare($sql);
        $image = uploadBeauticianImage();
        $imageData = $image['data'] ?? null;
        $imageMime = $image['mime'] ?? null;
        $statement->bind_param('ssssssssss', $beauticianType, $lastName, $firstName, $address, $contactNo, $employmentStatus, $dateJoined, $dateSeparated, $imageData, $imageMime);
        $statement->execute();
        $statement->close();
        $conn->close();
        returnToBeauticians('success', 'Beautician added successfully.');
    } else if ($action == 'edit') { //update existing beautician in database
        $statement = $conn->prepare('SELECT beauticianID FROM beauticians WHERE beauticianID = ?');
        $statement->bind_param('i', $beauticianID);
        $statement->execute();
        $record = $statement->get_result()->fetch_assoc();
        $beauticianExists = $record !== null;
        $statement->close();
        if (!$beauticianExists) {
            $conn->close();
            returnToBeauticians('error', 'The selected beautician no longer exists.');
        }
        $sql = 'UPDATE beauticians SET beauticianType = ?, lastName = ?, firstName = ?, address = ?, contactNo = ?, employmentStatus = ?, dateJoined = ?, dateSeparated = ?, imageData = COALESCE(?, imageData), imageMime = COALESCE(?, imageMime) WHERE beauticianID = ?';
        $statement = $conn->prepare($sql);
        $image = uploadBeauticianImage();
        $imageData = $image['data'] ?? null;
        $imageMime = $image['mime'] ?? null;
        $statement->bind_param('ssssssssssi', $beauticianType, $lastName, $firstName, $address, $contactNo, $employmentStatus, $dateJoined, $dateSeparated, $imageData, $imageMime, $beauticianID);
        $statement->execute();
        $statement->close();
        $conn->close();
        returnToBeauticians('success', 'Beautician updated successfully.');
    }
} catch (mysqli_sql_exception $error) { //js in case bai
    error_log('Beautician save failed: ' . $error->getMessage());
    returnToBeauticians('error', 'Unable to save the beautician. Please try again.');
} catch (RuntimeException $error) {
    returnToBeauticians('error', $error->getMessage());
}
