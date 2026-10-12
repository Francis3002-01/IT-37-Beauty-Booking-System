<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/booking.php';
bookingLogin($conn, true);
$beauticianID = bookingValue($_POST, 'beauticianID');
$page = 'availability.php?beauticianID=' . urlencode($beauticianID);
$transaction = false;
try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !bookingID($beauticianID)) {
        throw new RuntimeException('Select a valid beautician.');
    }
    bookingCheckToken();
    $action = bookingValue($_POST, 'action');
    $id = bookingValue($_POST, 'availabilityID');
    if (!in_array($action, ['create', 'edit', 'delete'], true)) {
        throw new RuntimeException('Invalid schedule action.');
    }
    if ($action != 'create' && !bookingID($id)) {
        throw new RuntimeException('Invalid schedule ID.');
    }
    $conn->begin_transaction();
    $transaction = true;
    //All bookings and schedule changes lock the same beautician to prevent races.
    $people = bookingQuery(
        $conn,
        'SELECT * FROM beauticians WHERE beauticianID=? FOR UPDATE',
        'i',
        [$beauticianID]
    );
    if (!$people) {
        throw new RuntimeException('The selected beautician no longer exists.');
    }
    if ($people[0]['employmentStatus'] != 'Active') {
        throw new RuntimeException('Schedules for On Leave or Inactive beauticians are read-only.');
    }
    $old = null;
    if ($action != 'create') {
        $rows = bookingQuery(
            $conn,
            'SELECT * FROM availability WHERE availabilityID=? AND beauticianID=? FOR UPDATE',
            'ii',
            [$id, $beauticianID]
        );
        $old = $rows[0] ?? null;
        if (!$old) {
            throw new RuntimeException('The schedule no longer exists.');
        }
        bookingExecute($conn, 'DELETE FROM availability WHERE availabilityID=?', 'i', [$id]);
    }
    if ($action != 'delete') {
        $date = bookingValue($_POST, 'availableDate');
        $start = bookingValue($_POST, 'startTime');
        $end = bookingValue($_POST, 'endTime');
        if (!bookingDate($date) || !bookingTime($start) || !bookingTime($end) || $start >= $end) {
            throw new RuntimeException(
                'Enter a valid date and working hours that end later on the same day.'
            );
        }
        $overlap = bookingQuery(
            $conn,
            'SELECT availabilityID FROM availability WHERE beauticianID=? AND availableDate=? AND startTime<? AND endTime>?',
            'isss',
            [$beauticianID, $date, $end, $start]
        );
        if ($overlap) {
            throw new RuntimeException('These working hours overlap an existing schedule.');
        }
        if ($action == 'edit') {
            bookingExecute(
                $conn,
                'INSERT INTO availability (availabilityID,beauticianID,availableDate,startTime,endTime) VALUES (?,?,?,?,?)',
                'iisss',
                [$id, $beauticianID, $date, $start, $end]
            );
        } else {
            bookingExecute(
                $conn,
                'INSERT INTO availability (beauticianID,availableDate,startTime,endTime) VALUES (?,?,?,?)',
                'isss',
                [$beauticianID, $date, $start, $end]
            );
        }
    }
    if ($old) {
        $bookings = bookingQuery(
            $conn,
            "SELECT a.* FROM appointment a JOIN apt_beautician b ON b.appointmentID=a.appointmentID WHERE b.beauticianID=? AND a.appointmentDate=? AND a.appointmentStatus<>'Cancelled'",
            'is',
            [$beauticianID, $old['availableDate']]
        );
        foreach ($bookings as $booking) {
            $coverage = bookingQuery(
                $conn,
                'SELECT availabilityID FROM availability WHERE beauticianID=? AND availableDate=? AND startTime<=? AND endTime>=?',
                'isss',
                [
                    $beauticianID,
                    $booking['appointmentDate'],
                    $booking['startTime'],
                    $booking['endTime']
                ]
            );
            if (!$coverage) {
                throw new RuntimeException(
                    'This change would leave an existing appointment outside working hours.'
                );
            }
        }
    }
    $conn->commit();
    $transaction = false;
    bookingFlash(
        $page,
        'success',
        'Schedule ' . ($action == 'delete' ? 'deleted' : 'saved') . ' successfully.'
    );
} catch (Throwable $error) {
    if ($transaction) {
        $conn->rollback();
    }
    if ($error instanceof mysqli_sql_exception) {
        error_log($error->getMessage());
        $message = 'Unable to save the schedule. Please try again.';
    } else {
        $message = $error->getMessage();
    }
    bookingFlash($page, 'error', $message);
}
