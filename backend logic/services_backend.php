<?php
/* BACKEND LOGIC FOR SERVICES.PHP
 * ------------------------------------------------------------------
 * Add and edit are functional. 
 * Deletion is not implemented kay need pa iconnect sa appointment LOL.
 * Deletion isn't allowed for services nga gigamit na btw.
 * [table] service fields: serviceID(pk, constraint 1xxx but like should we contain it in 1k-2k what r ur thoughts), serviceName, serviceCategory, duration, price, description.
 */

//function to handle outcomes (errors and success messages) and return to services.php
function returnToServices(string $status, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) { // if session is not active, start one
        session_start();
    } 
    $_SESSION['service_status'] = $status; //store response status in flash session variable
    $_SESSION['service_message'] = $message; //store response message in flash session variable
    header('Location: ../services.php', true, 303); //hardcoding the redirection. 303 cz its safer doing get kesas post nga mucommit ug duplicates tabang
    exit;
}

//retrieving and trimming string POST parameters
function getFormValue(string $fieldName): string
{
    if (!isset($_POST[$fieldName])) {
        return '';
    }

    if (!is_string($_POST[$fieldName])) {
        returnToServices('error', 'Invalid form field type: ' . $fieldName);
    }

    return trim($_POST[$fieldName]);
}

//ensure request method is POST, else return to services.php with error message
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    returnToServices('error', 'Please submit the service form.');
}

//guarantee that actions can only be create, edit, or delete. If not, return to services.php with error message
$action = getFormValue('action');
if ($action != 'create' && $action != 'edit' && $action != 'delete') {
    returnToServices('error', 'Invalid service action.');
}

//passed pre-validation, get serviceID for edit and delete
$serviceID = getFormValue('serviceID');

//for editing and deleting, ensure serviceID is a valid positive integer within the range of a 32-bit signed integer. If not, return to services.php with error message
if ($action == 'edit' || $action == 'delete') {
    if (!ctype_digit($serviceID) || $serviceID < 1 || $serviceID > 2147483647) {
        returnToServices('error', 'Invalid service ID.');
    }
}

//implement deletion logic soon okay
if ($action == 'delete') {
    returnToServices('error', 'Deletion is unavailable hahah lets do it.');
}

//store form values in variables for validation to prep for insertion, edits, and deletion.
$serviceName = getFormValue('serviceName');
$category = getFormValue('category');
if ($category == '') {
    $category = getFormValue('serviceCategory');
}
$duration = getFormValue('duration');
$price = getFormValue('price');
$description = getFormValue('description');

//serviceName cannot be empty. mb_strlen kay mucount ug char unlike strlen nga by bytes
if ($serviceName == '' || mb_strlen($serviceName) > 50) {
    returnToServices('error', 'Service name must contain 1 to 50 characters.');
}

//shld we js array this
if ($category != 'Hair' && $category != 'Face'
    && $category != 'Nails' && $category != 'Massage') {
    returnToServices('error', 'Select Hair, Face, Nails, or Massage.');
}

if (!ctype_digit($duration) || $duration < 1 || $duration > 1440) { //1440 mins = 24 hours
    returnToServices('error', 'Duration must be a positive whole number of minutes.');
}

//check if valid price
if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $price) || (float) $price <= 0) {
    returnToServices('error', 'Enter a price between 0.01 and 99999999.99 (up to 2 decimal places).');
}

//check if desc empty
if ($description == '' || mb_strlen($description) > 255) {
    returnToServices('error', 'Description is required and must contain at most 255 characters.');
}

try {
    require_once __DIR__ . '/../config/database.php';

    if (!isset($conn) || !($conn instanceof mysqli)) { //check if $conn is active
        returnToServices('error', 'The database connection is unavailable.');
    }

    if ($action == 'create') { //insert new service into database
        $sql = 'INSERT INTO service
            (serviceName, serviceCategory, duration, price, description)
            VALUES (?, ?, ?, ?, ?)';
        $statement = $conn->prepare($sql);
        $statement->bind_param('ssiss', $serviceName, $category, $duration, $price, $description);
        $statement->execute();
        $statement->close();
        $conn->close();

        returnToServices('success', 'Service added successfully.');
    } else if ($action == 'edit') { //update existing service in database
        $sql = 'SELECT serviceID FROM service WHERE serviceID = ?';
        $statement = $conn->prepare($sql);
        $statement->bind_param('i', $serviceID);
        $statement->execute();
        $statement->store_result();
        $serviceExists = $statement->num_rows > 0;
        $statement->close();

        if (!$serviceExists) {
            $conn->close();
            returnToServices('error', 'The selected service no longer exists.');
        }

        $sql = 'UPDATE service
            SET serviceName = ?, serviceCategory = ?, duration = ?,
                price = ?, description = ?
            WHERE serviceID = ?';
        $statement = $conn->prepare($sql);
        $statement->bind_param('ssissi', $serviceName, $category, $duration, $price, $description, $serviceID);
        $statement->execute();
        $statement->close();
        $conn->close();

        returnToServices('success', 'Service updated successfully.');
    }
} catch (mysqli_sql_exception $error) { //js in case bai
    error_log('Service save failed: ' . $error->getMessage());
    returnToServices('error', 'Unable to save the service. Please try again.');
} //gotta add deletion logic soon okay
