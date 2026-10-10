<?php
/* BACKEND LOGIC FOR CLIENTS.PHP
 * ------------------------------------------------------------------
 * Add and edit are functional. 
 * Deletion is not implemented kay need pa iconnect sa appointment LOL.
 * Deletion isn't allowed for services nga gigamit na btw.
 * [table] client fields: clientID(pk, constraint 2xxx but like should we contain it in 1k-2k what r ur thoughts), lastName, firstName, address, contactNo.
 */

//function to handle outcomes (errors and success messages) and return to clients.php
function returnToClients($status, $message)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['client_status'] = $status;
    $_SESSION['client_message'] = $message;
    header('Location: ../clients.php', true, 303);
    exit;
}

//retrieving and trimming string POST parameters
function getClientFormValue($fieldName)
{
    if (!isset($_POST[$fieldName])) {
        return '';
    }
    if (!is_string($_POST[$fieldName])) {
        returnToClients('error', 'Invalid form field: ' . $fieldName);
    }
    return trim($_POST[$fieldName]);
}

//ensure request method is POST, else return to clients.php with error message
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    returnToClients('error', 'Please submit the client form.');
}

//guarantee that actions can only be create, edit, or delete. If not, return to clients.php with error message
$action = getClientFormValue('action');
if ($action != 'create' && $action != 'edit' && $action != 'delete') {
    returnToClients('error', 'Invalid client action.');
}

//passed pre-validation, get clientID for edit and delete
$clientID = getClientFormValue('clientID');

//for editing and deleting, ensure clientID is a valid positive integer within the range of a 32-bit signed integer. If not, return to clients.php with error message
if ($action == 'edit' || $action == 'delete') {
    if (!ctype_digit($clientID) || $clientID < 1 || $clientID > 2147483647) {
        returnToClients('error', 'Invalid client ID.');
    }
}

//implement deletion logic soon okay
if ($action == 'delete') {
    returnToClients('error', 'Deletion is unavailable hahah lets do it.');
}

//store form values in variables for validation to prep for insertion, edits, and deletion.
$firstName = getClientFormValue('firstName');
$lastName = getClientFormValue('lastName');
$address = getClientFormValue('address');
$contactNo = getClientFormValue('contactNo');

//name cannot be empty
if ($firstName == '' || mb_strlen($firstName) > 50) {
    returnToClients('error', 'First name must contain 1 to 50 characters.');
}
if ($lastName == '' || mb_strlen($lastName) > 50) {
    returnToClients('error', 'Last name must contain 1 to 50 characters.');
}

//allow accented letters, spaces, apostrophes, periods and hyphens lngc
if (!preg_match('/\p{L}/u', $firstName)
    || !preg_match("/^[\p{L}\p{M} .’'\-]+$/u", $firstName)) {
    returnToClients('error', 'First name must contain letters. It may use spaces, apostrophes, periods or hyphens.');
}
if (!preg_match('/\p{L}/u', $lastName)
    || !preg_match("/^[\p{L}\p{M} .’'\-]+$/u", $lastName)) {
    returnToClients('error', 'Last name must contain letters. It may use spaces, apostrophes, periods or hyphens.');
}

//address cannot be empty and allow symbols
if ($address == '' || mb_strlen($address) > 255) {
    returnToClients('error', 'Address is required and must contain at most 255 characters.');
}
if (!preg_match('/\p{L}/u', $address)
    || !preg_match("/^[\p{L}\p{M}\p{N} .,\r\n’'\-\/#():]+$/u", $address)) {
    returnToClients('error', 'Address must contain letters and use ordinary address text, numbers and punctuation.');
}

//contactno cannot be empty and must be exactly 11 digits
if (!ctype_digit($contactNo) || strlen($contactNo) != 11) {
    returnToClients('error', 'Contact number must contain exactly 11 digits.');
}

try {
    require_once __DIR__ . '/../config/database.php';

    if (!isset($conn) || !($conn instanceof mysqli)) { //check if $conn is active
        returnToClients('error', 'The database connection is unavailable.');
    }

    //check if duplicate full name exists in db. gets a copy of the clientID to compare with the current clientID. If it exists, return to clients.php with error message
    $excludedID = 0; 
    if ($action == 'edit') {
        $excludedID = (int) $clientID;
    }
    $sql = 'SELECT clientID FROM client
        WHERE TRIM(firstName) = ? AND TRIM(lastName) = ? AND clientID != ?
        LIMIT 1';
    $statement = $conn->prepare($sql);
    $statement->bind_param('ssi', $firstName, $lastName, $excludedID);
    $statement->execute();
    $statement->store_result();
    $duplicateName = $statement->num_rows > 0;
    $statement->close();
    
    if ($duplicateName) {
        $conn->close();
        returnToClients('error', 'A client with the same first and last name already exists.');
    }

    if ($action == 'create') { //insert new client into database
        $sql = 'INSERT INTO client (lastName, firstName, address, contactNo) VALUES (?, ?, ?, ?)';
        $statement = $conn->prepare($sql);
        $statement->bind_param('ssss', $lastName, $firstName, $address, $contactNo);
        $statement->execute();
        $statement->close();
        $conn->close();
        returnToClients('success', 'Client added successfully.');
    } else if ($action == 'edit') { //update existing client in database
        $statement = $conn->prepare('SELECT clientID FROM client WHERE clientID = ?');
        $statement->bind_param('i', $clientID);
        $statement->execute();
        $statement->store_result();
        $clientExists = $statement->num_rows > 0;
        $statement->close();
        if (!$clientExists) {
            $conn->close();
            returnToClients('error', 'The selected client no longer exists.');
        }
        $sql = 'UPDATE client SET lastName = ?, firstName = ?, address = ?, contactNo = ? WHERE clientID = ?';
        $statement = $conn->prepare($sql);
        $statement->bind_param('ssssi', $lastName, $firstName, $address, $contactNo, $clientID);
        $statement->execute();
        $statement->close();
        $conn->close();
        returnToClients('success', 'Client updated successfully.');
    }
} catch (mysqli_sql_exception $error) { //js in case bai
    error_log('Client save failed: ' . $error->getMessage());
    returnToClients('error', 'Unable to save the client. Please try again.');
} //gotta add deletion logic soon okay
