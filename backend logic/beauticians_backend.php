<?php
/* BACKEND LOGIC FOR BEAUTICIANS.PHP
 * ------------------------------------------------------------------
 * Add and edit are functional.
 * Deletion needs the appointment relationship before usage can be checked.
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

//implement deletion logic soon okay
if ($action == 'delete') {
    returnToBeauticians('error', 'Deletion is unavailable until appointment usage checks are configured.');
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
    && $beauticianType != 'Nail Artist' && $beauticianType != 'Esthetician') {
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
        $sql = 'INSERT INTO beauticians (beauticianType, lastName, firstName, address, contactNo, employmentStatus, dateJoined, dateSeparated) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $statement = $conn->prepare($sql);
        $statement->bind_param('ssssssss', $beauticianType, $lastName, $firstName, $address, $contactNo, $employmentStatus, $dateJoined, $dateSeparated);
        $statement->execute();
        $statement->close();
        $conn->close();
        returnToBeauticians('success', 'Beautician added successfully.');
    } else if ($action == 'edit') { //update existing beautician in database
        $statement = $conn->prepare('SELECT beauticianID FROM beauticians WHERE beauticianID = ?');
        $statement->bind_param('i', $beauticianID);
        $statement->execute();
        $statement->store_result();
        $beauticianExists = $statement->num_rows > 0;
        $statement->close();
        if (!$beauticianExists) {
            $conn->close();
            returnToBeauticians('error', 'The selected beautician no longer exists.');
        }
        $sql = 'UPDATE beauticians SET beauticianType = ?, lastName = ?, firstName = ?, address = ?, contactNo = ?, employmentStatus = ?, dateJoined = ?, dateSeparated = ? WHERE beauticianID = ?';
        $statement = $conn->prepare($sql);
        $statement->bind_param('ssssssssi', $beauticianType, $lastName, $firstName, $address, $contactNo, $employmentStatus, $dateJoined, $dateSeparated, $beauticianID);
        $statement->execute();
        $statement->close();
        $conn->close();
        returnToBeauticians('success', 'Beautician updated successfully.');
    }
} catch (mysqli_sql_exception $error) { //js in case bai
    error_log('Beautician save failed: ' . $error->getMessage());
    returnToBeauticians('error', 'Unable to save the beautician. Please try again.');
} //gotta add deletion logic soon okay
