<?php
//Common checks for schedule forms and appointment forms.
function bookingText($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
function bookingValue($values, $key)
{
    return isset($values[$key]) && is_string($values[$key]) ? trim($values[$key]) : '';
}
function bookingDate($date)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }
    $parts = explode('-', $date);
    return $parts[0] >= 1000 && checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
}
function bookingTime($time)
{
    return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time);
}
function bookingID($id)
{
    return ctype_digit((string) $id) && $id > 0 && $id <= 2147483647;
}
function bookingQuery($conn, $sql, $types = '', $values = [])
{
    $statement = $conn->prepare($sql);
    if ($types != '') {
        $statement->bind_param($types, ...$values);
    }
    $statement->execute();
    $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();
    return $rows;
}
function bookingExecute($conn, $sql, $types, $values)
{
    $statement = $conn->prepare($sql);
    $statement->bind_param($types, ...$values);
    $statement->execute();
    $statement->close();
}
function bookingLogin($conn, $adminOnly = false)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $id = $_SESSION['userID'] ?? '';
    if (!bookingID($id)) {
        http_response_code(403);
        exit('Please log in to manage bookings.');
    }
    $users = bookingQuery(
        $conn,
        'SELECT u.*, s.employmentStatus FROM `user` u LEFT JOIN staff s ON s.staffID=u.staffID WHERE u.userID=?',
        'i',
        [$id]
    );
    $user = $users[0] ?? null;
    if (
        !$user ||
        $user['accountStatus'] != 'Active' ||
        ($user['staffID'] !== null && $user['employmentStatus'] == 'Inactive') ||
        !in_array(strtolower($user['role']), ['admin', 'staff', 'receptionist'], true) ||
        ($adminOnly && strtolower($user['role']) != 'admin')
    ) {
        http_response_code(403);
        exit('You do not have access to this action.');
    }
    if (!isset($_SESSION['booking_token'])) {
        $_SESSION['booking_token'] = bin2hex(random_bytes(24));
    }
    return $user;
}
//A hidden session token prevents other websites from submitting booking actions.
function bookingTokenField()
{
    echo '<input type="hidden" name="bookingToken" value="' .
        bookingText($_SESSION['booking_token']) .
        '">';
}
function bookingCheckToken()
{
    $token = bookingValue($_POST, 'bookingToken');
    if ($token == '' || !hash_equals($_SESSION['booking_token'] ?? '', $token)) {
        throw new RuntimeException('The form expired. Reload the page and try again.');
    }
}
function bookingFlash($page, $status, $message)
{
    $_SESSION['booking_status'] = $status;
    $_SESSION['booking_message'] = $message;
    header('Location: ../' . $page, true, 303);
    exit();
}
//Working hours remain unchanged by bookings; free time is calculated here.
function beauticianAvailability($conn, $beautician, $date, $start, $end, $exclude = 0)
{
    if ($beautician['employmentStatus'] != 'Active') {
        return $beautician['employmentStatus'];
    }
    $schedules = bookingQuery(
        $conn,
        'SELECT startTime,endTime FROM availability WHERE beauticianID=? AND availableDate=? FOR UPDATE',
        'is',
        [$beautician['beauticianID'], $date]
    );
    if (!$schedules) {
        return 'No schedule set';
    }
    $within = false;
    foreach ($schedules as $schedule) {
        if ($schedule['startTime'] <= $start . ':00' && $schedule['endTime'] >= $end . ':00') {
            $within = true;
        }
    }
    if (!$within) {
        return 'Outside working hours';
    }
    $conflicts = bookingQuery(
        $conn,
        "SELECT a.startTime,a.endTime FROM appointment a JOIN apt_beautician b ON b.appointmentID=a.appointmentID WHERE b.beauticianID=? AND a.appointmentDate=? AND a.appointmentID<>? AND a.appointmentStatus<>'Cancelled' AND a.startTime<? AND a.endTime>? FOR UPDATE",
        'isiss',
        [$beautician['beauticianID'], $date, $exclude, $end, $start]
    );
    if ($conflicts) {
        return 'Booked ' .
            substr($conflicts[0]['startTime'], 0, 5) .
            '–' .
            substr($conflicts[0]['endTime'], 0, 5);
    }
    return 'Available';
}
