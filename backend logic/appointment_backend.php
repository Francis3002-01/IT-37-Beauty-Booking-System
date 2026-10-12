<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/booking.php';
date_default_timezone_set('Asia/Manila');
$user = bookingLogin($conn);
$admin = strtolower($user['role']) == 'admin';
//track the transaction so failed bookings can be rolled back
$transaction = false;
try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new RuntimeException('Submit the appointment form.');
    }
    bookingCheckToken();
    $action = bookingValue($_POST, 'action');
    $id = bookingValue($_POST, 'appointmentID');
    if (!in_array($action, ['create', 'edit', 'status', 'payment'], true)) {
        throw new RuntimeException('Appointments cannot be deleted.');
    }
    if ($action != 'create' && !bookingID($id)) {
        throw new RuntimeException('Invalid appointment ID.');
    }
    if ($action == 'edit' && !$admin) {
        throw new RuntimeException('Only Admin can change booking details.');
    }
    $conn->begin_transaction();
    $transaction = true;
    $old = null;
    if ($action != 'create') {
        $rows = bookingQuery(
            $conn,
            'SELECT * FROM appointment WHERE appointmentID=? FOR UPDATE',
            'i',
            [$id]
        );
        $old = $rows[0] ?? null;
        if (!$old) {
            throw new RuntimeException('The appointment no longer exists.');
        }
        $recordedPaid = bookingQuery(
            $conn,
            'SELECT COALESCE(SUM(amount),0) AS paid FROM payment WHERE appointmentID=?',
            'i',
            [$id]
        )[0]['paid'];
        $fullyPaid = round((float) $recordedPaid, 2) >= round((float) $old['totalCost'], 2);
    }
    //check payment rules before changing the appointment status
    if ($action == 'status') {
        $status = bookingValue($_POST, 'appointmentStatus');
        if ($status == 'Cancelled' && $fullyPaid) {
            throw new RuntimeException('Paid appointments cannot be cancelled.');
        }
        if ($status == 'Completed' && !$fullyPaid) {
            throw new RuntimeException(
                'Full payment is required before marking an appointment as completed.'
            );
        }
        if (!in_array($old['appointmentStatus'], ['Pending', 'Confirmed'], true)) {
            throw new RuntimeException(
                'Completed and cancelled appointments cannot change status.'
            );
        }
        if (!in_array($status, ['Confirmed', 'Completed', 'Cancelled'], true)) {
            throw new RuntimeException('Select a valid appointment status.');
        }
        bookingExecute(
            $conn,
            'UPDATE appointment SET appointmentStatus=? WHERE appointmentID=?',
            'si',
            [$status, $id]
        );
        //get the appointment client and prevent overpayments
    } elseif ($action == 'payment') {
        if ($old['appointmentStatus'] == 'Cancelled') {
            throw new RuntimeException('Payments cannot be added to cancelled appointments.');
        }
        $amount = bookingValue($_POST, 'amount');
        $method = bookingValue($_POST, 'paymentMethod');
        $bookingClients = bookingQuery(
            $conn,
            'SELECT clientID FROM apt_client WHERE appointmentID=?',
            'i',
            [$id]
        );
        if (count($bookingClients) != 1) {
            throw new RuntimeException('Payment requires an appointment with one assigned client.');
        }
        $client = $bookingClients[0]['clientID'];
        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $amount) || (float) $amount <= 0) {
            throw new RuntimeException(
                'Amount paid must be greater than 0 and equal to or less than the price or balance. Use up to two decimal places.'
            );
        }
        if (!in_array($method, ['Cash', 'Card', 'Online', 'Bank Transfer', 'E-Wallet'], true)) {
            throw new RuntimeException('Select a valid payment method.');
        }
        $paid = bookingQuery(
            $conn,
            'SELECT COALESCE(SUM(amount),0) AS paid FROM payment WHERE appointmentID=?',
            'i',
            [$id]
        )[0]['paid'];
        if (round((float) $amount + (float) $paid, 2) > round((float) $old['totalCost'], 2)) {
            throw new RuntimeException(
                'Amount paid must be greater than 0 and equal to or less than the price or balance.'
            );
        }
        bookingExecute(
            $conn,
            'INSERT INTO payment (appointmentID,clientID,amount,paymentMethod) VALUES (?,?,?,?)',
            'iiss',
            [$id, $client, $amount, $method]
        );
    } else {
        if ($old && !in_array($old['appointmentStatus'], ['Pending', 'Confirmed'], true)) {
            throw new RuntimeException('Completed and cancelled bookings cannot be edited.');
        }
        $client = bookingValue($_POST, 'clientID');
        $service = bookingValue($_POST, 'serviceID');
        $beautician = bookingValue($_POST, 'beauticianID');
        if ($old) {
            $savedClients = bookingQuery(
                $conn,
                'SELECT clientID FROM apt_client WHERE appointmentID=?',
                'i',
                [$id]
            );
            if (count($savedClients) != 1 || $savedClients[0]['clientID'] != $client) {
                throw new RuntimeException(
                    'The client cannot change during editing. Cancel and create a new booking instead.'
                );
            }
        }
        $date = bookingValue($_POST, 'appointmentDate');
        $start = bookingValue($_POST, 'startTime');
        $end = bookingValue($_POST, 'endTime');
        $type = bookingValue($_POST, 'appointmentType');
        $venue = bookingValue($_POST, 'venue');
        $notes = bookingValue($_POST, 'notes');
        if (!bookingID($client) || !bookingID($service) || !bookingID($beautician)) {
            throw new RuntimeException('Select a client, service and available beautician.');
        }
        if (!bookingDate($date) || !bookingTime($start) || !bookingTime($end) || $start >= $end) {
            throw new RuntimeException(
                'Enter valid same-day times, with end time after start time.'
            );
        }
        if ($date < date('Y-m-d') || ($date == date('Y-m-d') && $start < date('H:i'))) {
            throw new RuntimeException('The appointment must start in the future.');
        }
        if (!in_array($type, ['Salon service', 'Home service'], true)) {
            throw new RuntimeException('Select Salon or Home service.');
        }
        if (
            $type == 'Home service' &&
            ($venue == '' || mb_strlen($venue) > 255 || !preg_match('/\p{L}/u', $venue))
        ) {
            throw new RuntimeException('Enter a valid home-service venue, up to 255 characters.');
        }
        if ($type == 'Salon service') {
            $venue = null;
        }
        if (mb_strlen($notes) > 255) {
            throw new RuntimeException('Notes must contain at most 255 characters.');
        }
        if (!bookingQuery($conn, 'SELECT clientID FROM client WHERE clientID=?', 'i', [$client])) {
            throw new RuntimeException('The client no longer exists.');
        }
        $services = bookingQuery($conn, 'SELECT * FROM service WHERE serviceID=?', 'i', [$service]);
        if (!$services || $services[0]['price'] <= 0) {
            throw new RuntimeException('Select a service with a valid price.');
        }
        $minutes =
            (int) substr($end, 0, 2) * 60 +
            (int) substr($end, 3, 2) -
            (int) substr($start, 0, 2) * 60 -
            (int) substr($start, 3, 2);
        if ($minutes != $services[0]['duration']) {
            throw new RuntimeException(
                'End time must match the selected service duration. Select the service again and check availability.'
            );
        }
        //lock the beautician and recheck availability before saving
        $people = bookingQuery(
            $conn,
            'SELECT beauticianID,employmentStatus FROM beauticians WHERE beauticianID=? FOR UPDATE',
            'i',
            [$beautician]
        );
        if (!$people) {
            throw new RuntimeException('The beautician no longer exists.');
        }
        $available = beauticianAvailability($conn, $people[0], $date, $start, $end, $old ? $id : 0);
        if ($available != 'Available') {
            throw new RuntimeException('Beautician is unavailable: ' . $available . '.');
        }
        //keep the booked price when the service stays the same
        $price =
            $old && $old['serviceID'] == $service ? $old['priceAtBooking'] : $services[0]['price'];
        $total = $price;
        $initialAmount = bookingValue($_POST, 'amountPaid');
        if ($initialAmount == '') {
            $initialAmount = '0';
        }
        $initialMethod = bookingValue($_POST, 'paymentMethod');
        if (
            !$old &&
            (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $initialAmount) ||
                (float) $initialAmount > (float) $total)
        ) {
            throw new RuntimeException(
                'Amount paid must be greater than 0 and equal to or less than the price or balance. Leave it at 0 if no payment is being recorded.'
            );
        }
        if (
            !$old &&
            (float) $initialAmount > 0 &&
            !in_array($initialMethod, ['Cash', 'Card', 'Online', 'Bank Transfer', 'E-Wallet'], true)
        ) {
            throw new RuntimeException('Select a valid mode of payment.');
        }
        if ($old) {
            $clients = bookingQuery(
                $conn,
                'SELECT clientID FROM apt_client WHERE appointmentID=?',
                'i',
                [$id]
            );
            $links = bookingQuery(
                $conn,
                'SELECT beauticianID FROM apt_beautician WHERE appointmentID=?',
                'i',
                [$id]
            );
            if (count($clients) != 1 || count($links) != 1) {
                throw new RuntimeException(
                    'Group bookings cannot be edited by the single-client form.'
                );
            }
            $paid = bookingQuery(
                $conn,
                'SELECT COALESCE(SUM(amount),0) AS paid FROM payment WHERE appointmentID=?',
                'i',
                [$id]
            )[0]['paid'];
            if ($paid > $total) {
                throw new RuntimeException(
                    'The new price cannot be below payments already received.'
                );
            }
            if ($clients[0]['clientID'] != $client && $paid > 0) {
                throw new RuntimeException('The client cannot change after payment.');
            }
            bookingExecute(
                $conn,
                'UPDATE appointment SET serviceID=?,appointmentDate=?,startTime=?,endTime=?,appointmentType=?,venue=?,priceAtBooking=?,totalCost=?,notes=? WHERE appointmentID=?',
                'issssssssi',
                [$service, $date, $start, $end, $type, $venue, $price, $total, $notes, $id]
            );
            bookingExecute($conn, 'DELETE FROM apt_beautician WHERE appointmentID=?', 'i', [$id]);
            if ($clients[0]['clientID'] != $client) {
                bookingExecute($conn, 'DELETE FROM apt_client WHERE appointmentID=?', 'i', [$id]);
                bookingExecute($conn, 'INSERT INTO apt_client VALUES (?,?)', 'ii', [$id, $client]);
            }
        } else {
            bookingExecute(
                $conn,
                "INSERT INTO appointment (userID,serviceID,appointmentDate,startTime,endTime,appointmentType,venue,appointmentStatus,priceAtBooking,totalCost,notes) VALUES (?,?,?,?,?,?,?,'Confirmed',?,?,?)",
                'iissssssss',
                [
                    $user['userID'],
                    $service,
                    $date,
                    $start,
                    $end,
                    $type,
                    $venue,
                    $price,
                    $total,
                    $notes
                ]
            );
            $id = $conn->insert_id;
            bookingExecute($conn, 'INSERT INTO apt_client VALUES (?,?)', 'ii', [$id, $client]);
        }
        bookingExecute($conn, 'INSERT INTO apt_beautician VALUES (?,?)', 'ii', [$id, $beautician]);
        if (!$old && (float) $initialAmount > 0) {
            bookingExecute(
                $conn,
                'INSERT INTO payment (appointmentID,clientID,amount,paymentMethod) VALUES (?,?,?,?)',
                'iiss',
                [$id, $client, $initialAmount, $initialMethod]
            );
        }
    }
    $conn->commit();
    $transaction = false;
    unset($_SESSION['booking_form']);
    bookingFlash(
        'appointments.php',
        'success',
        $action == 'payment' ? 'Payment recorded successfully.' : 'Appointment saved successfully.'
    );
} catch (Throwable $error) {
    if ($transaction) {
        $conn->rollback();
    }
    if (in_array($action ?? '', ['create', 'edit'], true)) {
        $_SESSION['booking_form'] = $_POST;
    }
    $message =
        $error instanceof mysqli_sql_exception
            ? 'Unable to save the appointment. Please try again.'
            : $error->getMessage();
    if ($error instanceof mysqli_sql_exception) {
        error_log($error->getMessage());
    }
    bookingFlash('appointments.php', 'error', $message);
}
