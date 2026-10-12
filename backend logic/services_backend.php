<?php
/* BACKEND LOGIC FOR SERVICES.PHP
 * ------------------------------------------------------------------
 * Add and edit are functional. 
 * Deletion possible only for services not yet in any appointments
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

//check the actual image contents, then save a unique filename instead of the original name
function uploadServiceImage()
{
    if (!isset($_FILES['serviceImage'])) return null;
    $file = $_FILES['serviceImage'];
    if (!isset($file['error']) || !is_int($file['error'])) {
        throw new RuntimeException('Invalid service image upload.');
    }
    if ($file['error'] == UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] != UPLOAD_ERR_OK) {
        throw new RuntimeException('Service image upload failed. Choose an image up to 2 MB.');
    }
    if (!isset($file['tmp_name']) || !is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid service image upload.');
    }
    if (filesize($file['tmp_name']) > 2 * 1024 * 1024) {
        throw new RuntimeException('Service image must be no larger than 2 MB.');
    }
    $image = @getimagesize($file['tmp_name']);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!$image || $image['mime'] != $mime) {
        throw new RuntimeException('Choose a valid JPG, PNG or WebP image.');
    }
    if ($mime == 'image/jpeg') $extension = 'jpg';
    else if ($mime == 'image/png') $extension = 'png';
    else if ($mime == 'image/webp') $extension = 'webp';
    else throw new RuntimeException('Choose a JPG, PNG or WebP image.');

    $folder = __DIR__ . '/../uploads/services';
    if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
        throw new RuntimeException('Unable to create the service image folder.');
    }
    $path = 'uploads/services/' . bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $path)) {
        throw new RuntimeException('Unable to save the service image.');
    }
    return $path;
}

//only remove files created by this upload handler
function removeServiceImage($path)
{
    if (is_string($path) && preg_match('/^uploads\/services\/[a-f0-9]{32}\.(jpg|png|webp)$/', $path)) {
        $file = __DIR__ . '/../' . $path;
        if (is_file($file) && !unlink($file)) error_log('Unable to remove service image: ' . $path);
    }
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

//check if service is already in an appointment
if ($action == 'delete') {
    try {
        require_once __DIR__ . '/../config/database.php';
        $statement = $conn->prepare('SELECT appointmentID FROM appointment WHERE serviceID = ? LIMIT 1');
        $statement->bind_param('i', $serviceID);
        $statement->execute();
        $statement->store_result();
        $used = $statement->num_rows > 0;
        $statement->close();
        if ($used) {
            $conn->close();
            returnToServices('error', 'This service cannot be deleted because it is used in an appointment.');
        }
        $statement = $conn->prepare('SELECT * FROM service WHERE serviceID = ?');
        $statement->bind_param('i', $serviceID);
        $statement->execute();
        $record = $statement->get_result()->fetch_assoc();
        $statement->close();
        $statement = $conn->prepare('DELETE FROM `service` WHERE serviceID = ?');
        $statement->bind_param('i', $serviceID);
        $statement->execute();
        $deleted = $statement->affected_rows;
        $statement->close();
        $conn->close();
        if ($deleted == 0) {
            returnToServices('error', 'The selected record no longer exists.');
        }
        removeServiceImage($record['serviceImage'] ?? null);
        returnToServices('success', 'Service deleted successfully.');
    } catch (mysqli_sql_exception $error) {
        if ($error->getCode() == 1451) {
            returnToServices('error', 'This record cannot be deleted because it is referenced by another record.');
        }
        error_log('Service deletion failed: ' . $error->getMessage());
        returnToServices('error', 'Unable to delete the record. Please try again.');
    }
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

$serviceImage = null;
try {
    require_once __DIR__ . '/../config/database.php';

    if (!isset($conn) || !($conn instanceof mysqli)) { //check if $conn is active
        returnToServices('error', 'The database connection is unavailable.');
    }

    if ($action == 'create') { //insert new service into database
        $sql = 'INSERT INTO service
            (serviceName, serviceCategory, duration, price, description, serviceImage)
            VALUES (?, ?, ?, ?, ?, ?)';
        $statement = $conn->prepare($sql);
        $serviceImage = uploadServiceImage();
        $statement->bind_param('ssisss', $serviceName, $category, $duration, $price, $description, $serviceImage);
        $statement->execute();
        $statement->close();
        $conn->close();

        returnToServices('success', 'Service added successfully.');
    } else if ($action == 'edit') { //update existing service in database
        $sql = 'SELECT serviceID, serviceImage FROM service WHERE serviceID = ?';
        $statement = $conn->prepare($sql);
        $statement->bind_param('i', $serviceID);
        $statement->execute();
        $record = $statement->get_result()->fetch_assoc();
        $serviceExists = $record !== null;
        $statement->close();

        if (!$serviceExists) {
            $conn->close();
            returnToServices('error', 'The selected service no longer exists.');
        }

        $sql = 'UPDATE service
            SET serviceName = ?, serviceCategory = ?, duration = ?,
                price = ?, description = ?, serviceImage = COALESCE(?, serviceImage)
            WHERE serviceID = ?';
        $statement = $conn->prepare($sql);
        $serviceImage = uploadServiceImage();
        $statement->bind_param('ssisssi', $serviceName, $category, $duration, $price, $description, $serviceImage, $serviceID);
        $statement->execute();
        $statement->close();
        $conn->close();

        if ($serviceImage !== null) removeServiceImage($record['serviceImage']);
        returnToServices('success', 'Service updated successfully.');
    }
} catch (mysqli_sql_exception $error) { //js in case bai
    removeServiceImage($serviceImage);
    error_log('Service save failed: ' . $error->getMessage());
    returnToServices('error', 'Unable to save the service. Please try again.');
} catch (RuntimeException $error) {
    removeServiceImage($serviceImage);
    returnToServices('error', $error->getMessage());
}
