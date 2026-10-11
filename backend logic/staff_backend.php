<?php
/* BACKEND LOGIC FOR STAFF.PHP
 * ------------------------------------------------------------------
 * Add and edit are functional.
 * Deletion possible only for staff not yet in any appointments 
 * [table] staff: staffID, staffType, lastName, firstName,
 * address, contactNo, employmentStatus, dateJoined, dateSeparated.
 */

//function to handle outcomes (errors and success messages) and return to staff.php
function returnToStaff($status, $message)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['staff_status'] = $status;
    $_SESSION['staff_message'] = $message;
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

//ensure request method is POST, else return to staff.php with error message
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    returnToStaff('error', 'Please submit the staff form.');
}

//guarantee that actions can only be create, edit, or delete. If not, return to staff.php with error message
$action = getStaffFormValue('action');
if ($action != 'create' && $action != 'edit' && $action != 'delete') {
    returnToStaff('error', 'Invalid staff action.');
}

//passed pre-validation, get staffID for edit and delete
$staffID = getStaffFormValue('staffID');

//for editing and deleting, ensure staffID is a valid positive integer within the range of a 32-bit signed integer. If not, return to staff.php with error message
if ($action == 'edit' || $action == 'delete') {
    if (!ctype_digit($staffID) || $staffID < 1 || $staffID > 2147483647) {
        returnToStaff('error', 'Invalid staff ID.');
    }
}

//check if staff is already in an appointment
if ($action == 'delete') {
    try {
        require_once __DIR__ . '/../config/database.php';
        $statement = $conn->prepare('SELECT appointmentID FROM apt_staff WHERE staffID = ? LIMIT 1');
        $statement->bind_param('i', $staffID);
        $statement->execute();
        $statement->store_result();
        $used = $statement->num_rows > 0;
        $statement->close();
        if ($used) {
            $conn->close();
            returnToStaff('error', 'This staff member cannot be deleted because they have a user account. Set them to Inactive and disable the account instead.');
        }
        $statement = $conn->prepare('DELETE FROM `staff` WHERE staffID = ?');
        $statement->bind_param('i', $staffID);
        $statement->execute();
        $deleted = $statement->affected_rows;
        $statement->close();
        $conn->close();
        if ($deleted == 0) {
            returnToStaff('error', 'The selected record no longer exists.');
        }
        returnToStaff('success', 'Staff member deleted successfully.');
    } catch (mysqli_sql_exception $error) {
        if ($error->getCode() == 1451) {
            returnToStaff('error', 'This record cannot be deleted because it is referenced by another record.');
        }
        error_log('Staff member deletion failed: ' . $error->getMessage());
        returnToStaff('error', 'Unable to delete the record. Please try again.');
    }
}

//store form values in variables for validation to prep for insertion, edits, and deletion.
$firstName = getStaffFormValue('firstName');
$lastName = getStaffFormValue('lastName');
$address = getStaffFormValue('address');
$contactNo = getStaffFormValue('contactNo');
$staffType = getStaffFormValue('staffType');
$employmentStatus = getStaffFormValue('employmentStatus');
$dateJoined = getStaffFormValue('dateJoined');
$dateSeparated = getStaffFormValue('dateSeparated');

//check role and employment status before saving.  god we should really array this
if ($staffType != 'Receptionist') {
    returnToStaff('error', 'Select a valid role.');
}
if ($employmentStatus != 'Active' && $employmentStatus != 'On Leave' && $employmentStatus != 'Inactive') {
    returnToStaff('error', 'Select a valid employment status.');
}

//check actual calendar dates, not only their text format
function validStaffDate($value)
{
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value)) return false;
    $parts = explode('-', $value);
    return $parts[0] >= 1000 && checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
}
if (!validStaffDate($dateJoined)) {
    returnToStaff('error', 'Enter a valid date joined.');
}
if ($employmentStatus == 'Inactive' && $dateSeparated == '') {
    returnToStaff('error', 'Date separated is required when employment status is Inactive.');
}
//active and on leave staff have not separated, so clear the date
if ($employmentStatus == 'Active' || $employmentStatus == 'On Leave') {
    $dateSeparated = '';
}
if ($dateSeparated != '') {
    if (!validStaffDate($dateSeparated) || $dateSeparated < $dateJoined) {
        returnToStaff('error', 'Date separated must be a valid date on or after date joined.');
    }
}
//an empty optional date is stored as SQL NULL
if ($dateSeparated == '') $dateSeparated = null;


//name cannot be empty
if ($firstName == '' || mb_strlen($firstName) > 50) {
    returnToStaff('error', 'First name must contain 1 to 50 characters.');
}
if ($lastName == '' || mb_strlen($lastName) > 50) {
    returnToStaff('error', 'Last name must contain 1 to 50 characters.');
}

//allow accented letters, spaces, apostrophes, periods and hyphens lngc
if (!preg_match('/\p{L}/u', $firstName)
    || !preg_match("/^[\p{L}\p{M} .’'\-]+$/u", $firstName)) {
    returnToStaff('error', 'First name must contain letters. It may use spaces, apostrophes, periods or hyphens.');
}
if (!preg_match('/\p{L}/u', $lastName)
    || !preg_match("/^[\p{L}\p{M} .’'\-]+$/u", $lastName)) {
    returnToStaff('error', 'Last name must contain letters. It may use spaces, apostrophes, periods or hyphens.');
}

//address cannot be empty and allow symbols
if ($address == '' || mb_strlen($address) > 255) {
    returnToStaff('error', 'Address is required and must contain at most 255 characters.');
}
if (!preg_match('/\p{L}/u', $address)
    || !preg_match("/^[\p{L}\p{M}\p{N} .,\r\n’'\-\/#():]+$/u", $address)) {
    returnToStaff('error', 'Address must contain letters and use ordinary address text, numbers and punctuation.');
}

//contactno cannot be empty and must be exactly 11 digits
if (!ctype_digit($contactNo) || strlen($contactNo) != 11) {
    returnToStaff('error', 'Contact number must contain exactly 11 digits.');
}

try {
    require_once __DIR__ . '/../config/database.php';

    if (!isset($conn) || !($conn instanceof mysqli)) { //check if $conn is active
        returnToStaff('error', 'The database connection is unavailable.');
    }

    //check if duplicate full name exists in db. gets a copy of the staffID to compare with the current staffID. If it exists, return to staff.php with error message
    $excludedID = 0;
    if ($action == 'edit') {
        $excludedID = (int) $staffID;
    }
    $sql = 'SELECT staffID FROM staff
        WHERE TRIM(firstName) = ? AND TRIM(lastName) = ? AND staffID != ?
        LIMIT 1';
    $statement = $conn->prepare($sql);
    $statement->bind_param('ssi', $firstName, $lastName, $excludedID);
    $statement->execute();
    $statement->store_result();
    $duplicateName = $statement->num_rows > 0;
    $statement->close();

    if ($duplicateName) {
        $conn->close();
        returnToStaff('error', 'A staff member with the same first and last name already exists.');
    }

    if ($action == 'create') { //insert new staff into database
        $sql = 'INSERT INTO staff (staffType, lastName, firstName, address, contactNo, employmentStatus, dateJoined, dateSeparated) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $statement = $conn->prepare($sql);
        $statement->bind_param('ssssssss', $staffType, $lastName, $firstName, $address, $contactNo, $employmentStatus, $dateJoined, $dateSeparated);
        $statement->execute();
        $statement->close();
        $conn->close();
        returnToStaff('success', 'Staff added successfully.');
    } else if ($action == 'edit') { //update existing staff in database
        $statement = $conn->prepare('SELECT staffID FROM staff WHERE staffID = ?');
        $statement->bind_param('i', $staffID);
        $statement->execute();
        $statement->store_result();
        $staffExists = $statement->num_rows > 0;
        $statement->close();
        if (!$staffExists) {
            $conn->close();
            returnToStaff('error', 'The selected staff member no longer exists.');
        }
        $sql = 'UPDATE staff SET staffType = ?, lastName = ?, firstName = ?, address = ?, contactNo = ?, employmentStatus = ?, dateJoined = ?, dateSeparated = ? WHERE staffID = ?';
        $statement = $conn->prepare($sql);
        $statement->bind_param('ssssssssi', $staffType, $lastName, $firstName, $address, $contactNo, $employmentStatus, $dateJoined, $dateSeparated, $staffID);
        $statement->execute();
        $statement->close();
        $conn->close();
        returnToStaff('success', 'Staff updated successfully.');
    }
} catch (mysqli_sql_exception $error) { //js in case bai
    error_log('Staff save failed: ' . $error->getMessage());
    returnToStaff('error', 'Unable to save the staff. Please try again.');
}
