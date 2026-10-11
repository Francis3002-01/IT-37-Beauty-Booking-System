<?php
session_start();
require_once __DIR__ . '/../config/database.php'; // MySQLi connection: $conn

function redirectWithError(string $error): void {
    header('Location: ../appointments.php?error=' . rawurlencode($error));
    exit;
}
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/** Check active status, scheduled availability, and appointment overlap. */
function checkAvailability(
    mysqli $conn,
    int $beauticianID,
    string $date,
    string $start,
    string $end,
    int $excludeID = 0
): array {

    if (
        $beauticianID <= 0 ||
        $date === '' ||
        $start === '' ||
        $end === ''
    ) {
        return [
            'available' => false,
            'message' => 'Choose a beautician, date, start time, and end time.'
        ];
    }

    // Validate date and time.
    $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

    if (
        !$dateObject ||
        $dateObject->format('Y-m-d') !== $date
    ) {
        return [
            'available' => false,
            'message' => 'Please select a valid appointment date.'
        ];
    }

    if (
        !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $start) ||
        !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $end)
    ) {
        return [
            'available' => false,
            'message' => 'Please select valid start and end times.'
        ];
    }

    $startTime = strtotime("$date $start");
    $endTime = strtotime("$date $end");

    if ($endTime <= $startTime) {
        return [
            'available' => false,
            'message' => 'End time must be later than start time.'
        ];
    }

    // Booking hours: 03:00 AM through 11:59 PM.
    if (
        $start < '03:00:00' ||
        $end > '23:59:59'
    ) {
        return [
            'available' => false,
            'message' => 'Appointments are allowed between 3:00 AM and 11:59 PM.'
        ];
    }

    // Ensure the beautician is active.
    $stmt = $conn->prepare(
        "SELECT beauticianID
         FROM beauticians
         WHERE beauticianID = ?
           AND employmentStatus = 'Active'
         LIMIT 1"
    );

    $stmt->bind_param('i', $beauticianID);
    $stmt->execute();

    $active = (bool) $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$active) {
        return [
            'available' => false,
            'message' => 'This beautician is inactive and cannot be booked.'
        ];
    }

    // Prevent overlapping appointments for the same beautician.
    $stmt = $conn->prepare(
        "SELECT a.appointmentID
         FROM appointment a
         INNER JOIN apt_beautician ab
             ON ab.appointmentID = a.appointmentID
         WHERE ab.beauticianID = ?
           AND a.appointmentDate = ?
           AND a.appointmentStatus <> 'Cancelled'
           AND a.startTime < ?
           AND a.endTime > ?
           AND a.appointmentID <> ?
         LIMIT 1"
    );

    $stmt->bind_param(
        'isssi',
        $beauticianID,
        $date,
        $end,
        $start,
        $excludeID
    );

    $stmt->execute();

    $overlap = (bool) $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if ($overlap) {
        return [
            'available' => false,
            'message' => 'This beautician already has an appointment during that time.'
        ];
    }

    return [
        'available' => true,
        'message' => 'Availability confirmed. No overlapping appointments.'
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError('invalid_request');
}
$action = $_POST['action'] ?? 'create';

if ($action === 'check_availability') {
    try {
        $result = checkAvailability(
            $conn,
            (int)($_POST['beauticianID'] ?? $_POST['resourceId'] ?? 0),
            trim((string)($_POST['appointmentDate'] ?? '')),
            trim((string)($_POST['startTime'] ?? '')),
            trim((string)($_POST['endTime'] ?? '')),
            (int)($_POST['editAppointmentId'] ?? $_POST['appointmentID'] ?? 0)
        );
        jsonResponse($result);
    } catch (Throwable $e) {
        error_log('Availability Check Failed: ' . $e->getMessage());
        jsonResponse(['available' => false, 'message' => 'Could not verify availability. Please try again.'], 500);
    }
}

if ($action !== 'create') {
    redirectWithError('unsupported_action');
}

$clientID = (int)($_POST['clientID'] ?? 0);
$serviceID = (int)($_POST['serviceID'] ?? $_POST['serviceId'] ?? 0);
$beauticianID = (int)($_POST['beauticianID'] ?? $_POST['resourceId'] ?? 0);
$date = trim((string)($_POST['appointmentDate'] ?? ''));
$start = trim((string)($_POST['startTime'] ?? ''));
$end = trim((string)($_POST['endTime'] ?? ''));
$type = trim((string)($_POST['appointmentType'] ?? 'Salon service'));
$venue = trim((string)($_POST['venue'] ?? ''));
$paymentMethod = trim((string)($_POST['paymentMethod'] ?? 'Cash'));
$paymentStatus = trim((string)($_POST['paymentStatus'] ?? 'Unpaid'));
$notes = trim((string)($_POST['notes'] ?? ''));
$userID = (int)($_SESSION['user_id'] ?? 5001);

if (!$clientID || !$serviceID || !$beauticianID || !$date || !$start || !$end) {
    redirectWithError('missing_fields');
}
if (!in_array($type, ['Salon service', 'Home service'], true)) redirectWithError('invalid_appointment_type');
if ($type === 'Home service' && $venue === '') redirectWithError('venue_required');
if (!in_array($paymentMethod, ['Cash', 'Bank Transfer', 'E-Wallet'], true)) redirectWithError('invalid_payment_method');
if (!in_array($paymentStatus, ['Paid', 'Unpaid'], true)) redirectWithError('invalid_payment_status');

$transactionStarted = false;
try {
    $stmt = $conn->prepare('SELECT clientID FROM client WHERE clientID = ? LIMIT 1');
    $stmt->bind_param('i', $clientID);
    $stmt->execute();
    $clientExists = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare('SELECT price FROM service WHERE serviceID = ? LIMIT 1');
    $stmt->bind_param('i', $serviceID);
    $stmt->execute();
    $service = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare('SELECT userID FROM `user` WHERE userID = ? LIMIT 1');
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $userExists = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$clientExists || !$service || !$userExists) redirectWithError('invalid_client_service_or_user');
    $price = (float)$service['price'];

    // Re-check on the server; browser-side validation alone is not sufficient.
    $availability = checkAvailability($conn, $beauticianID, $date, $start, $end);
    if (!$availability['available']) redirectWithError('unavailable');

    $conn->begin_transaction();
    $transactionStarted = true;

    $status = 'Confirmed';
    $stmt = $conn->prepare(
        "INSERT INTO appointment
         (userID, serviceID, appointmentDate, startTime, endTime, appointmentType, venue,
          appointmentStatus, priceAtBooking, totalCost, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('iissssssdds', $userID, $serviceID, $date, $start, $end, $type, $venue, $status, $price, $price, $notes);
    $stmt->execute();
    $appointmentID = (int)$conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare('INSERT INTO apt_client (appointmentID, clientID) VALUES (?, ?)');
    $stmt->bind_param('ii', $appointmentID, $clientID);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare('INSERT INTO apt_beautician (appointmentID, beauticianID) VALUES (?, ?)');
    $stmt->bind_param('ii', $appointmentID, $beauticianID);
    $stmt->execute();
    $stmt->close();

    // payment has no paymentStatus column; record a payment row only if marked Paid.
    if ($paymentStatus === 'Paid') {
        $stmt = $conn->prepare('INSERT INTO payment (appointmentID, clientID, amount, paymentMethod) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iids', $appointmentID, $clientID, $price, $paymentMethod);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    header('Location: ../appointments.php?success=1');
    exit;
} catch (Throwable $e) {
    if ($transactionStarted) {
        try { $conn->rollback(); } catch (Throwable $ignored) {}
    }
    error_log('Appointment Creation Failed: ' . $e->getMessage());
    redirectWithError('db_failed');
}
?>
