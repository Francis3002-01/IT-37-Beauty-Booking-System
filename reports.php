<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once 'config/database.php';
require_once 'includes/booking.php';
date_default_timezone_set('Asia/Manila');
$reportUser = bookingLogin($conn);
$isAdmin = strtolower($reportUser['role']) == 'admin';
$period = bookingValue($_GET, 'period');
$today = new DateTimeImmutable('today');
if ($period == 'Daily') {
    $start = $today->format('Y-m-d');
    $end = $start;
} elseif ($period == 'Weekly') {
    $start = $today->modify('monday this week')->format('Y-m-d');
    $end = $today->modify('sunday this week')->format('Y-m-d');
} else {
    $period = 'Monthly';
    $start = $today->format('Y-m-01');
    $end = $today->format('Y-m-t');
}
$error = '';
if ($isAdmin && (isset($_GET['from']) || isset($_GET['to']))) {
    $from = bookingValue($_GET, 'from');
    $to = bookingValue($_GET, 'to');
    if (bookingDate($from) && bookingDate($to) && $from <= $to) {
        $start = $from;
        $end = $to;
    } else {
        $error = 'Enter a valid date range. Showing the selected period instead.';
    }
}
//calculate reports from saved bookings and payments
$stats = bookingQuery(
    $conn,
    "SELECT COUNT(*) AS bookings, COALESCE(SUM(appointmentStatus='Completed'),0) AS completed, COALESCE(SUM(appointmentStatus='Cancelled'),0) AS cancelled, COALESCE(SUM(CASE WHEN appointmentStatus<>'Cancelled' THEN totalCost ELSE 0 END),0) AS bookingValue, COALESCE(SUM(CASE WHEN appointmentStatus<>'Cancelled' THEN GREATEST(totalCost-(SELECT COALESCE(SUM(amount),0) FROM payment p WHERE p.appointmentID=a.appointmentID),0) ELSE 0 END),0) AS outstanding FROM appointment a WHERE appointmentDate BETWEEN ? AND ?",
    'ss',
    [$start, $end]
)[0];
$received = bookingQuery(
    $conn,
    'SELECT COALESCE(SUM(amount),0) AS received FROM payment WHERE paymentDate>=? AND paymentDate<DATE_ADD(?,INTERVAL 1 DAY)',
    'ss',
    [$start, $end]
)[0]['received'];
$perBeautician = bookingQuery(
    $conn,
    "SELECT b.beauticianID,b.firstName,b.lastName,COUNT(DISTINCT a.appointmentID) AS bookings,COUNT(DISTINCT CASE WHEN a.appointmentStatus='Completed' THEN a.appointmentID END) AS completed FROM beauticians b JOIN apt_beautician ab ON ab.beauticianID=b.beauticianID JOIN appointment a ON a.appointmentID=ab.appointmentID WHERE a.appointmentDate BETWEEN ? AND ? GROUP BY b.beauticianID,b.firstName,b.lastName ORDER BY bookings DESC,b.firstName",
    'ss',
    [$start, $end]
);
$perClient = bookingQuery(
    $conn,
    'SELECT c.clientID,c.firstName,c.lastName,COUNT(DISTINCT a.appointmentID) AS bookings FROM client c JOIN apt_client ac ON ac.clientID=c.clientID JOIN appointment a ON a.appointmentID=ac.appointmentID WHERE a.appointmentDate BETWEEN ? AND ? GROUP BY c.clientID,c.firstName,c.lastName ORDER BY bookings DESC,c.firstName',
    'ss',
    [$start, $end]
);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Reports - beautyReserve</title>
        <link rel="icon" type="image/svg+xml" href="favicon.svg" />
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
        />
        <link
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
            rel="stylesheet"
        />
        <link href="css/reports.css?v=<?php echo filemtime(
            __DIR__ . '/css/reports.css'
        ); ?>" rel="stylesheet" />
    </head>
    <body>
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="dashboard-card">
                <h1 class="fw-bold fs-3 mb-4">Reports</h1>
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo bookingText(
                    $error
                ); ?></div>
                <?php endif; ?>
                <div class="d-flex gap-2 flex-wrap mb-3">
                    <?php foreach (
                        ['Daily', 'Weekly', 'Monthly']
                        as $choice
                    ): ?><a
                        class="filter-pill text-decoration-none <?php if ($choice == $period) {
                            echo 'active';
                        } ?>"
                        href="reports.php?period=<?php echo $choice; ?>"
                        ><?php echo ['Daily' => 'Today', 'Weekly' => 'This Week', 'Monthly' => 'This Month'][$choice]; ?></a
                    ><?php endforeach; ?>
                </div>
                <?php if ($isAdmin): ?>
                <form method="GET" class="report-range-controls d-flex align-items-end flex-wrap gap-3 mb-1">
                    <input type="hidden" name="period" value="<?php echo $period; ?>" />
                    <div>
                        <label class="form-label fw-semibold" for="fromDate">From</label
                        ><input
                            id="fromDate"
                            type="date"
                            class="form-control"
                            name="from"
                            value="<?php echo $start; ?>"
                            required
                        />
                    </div>
                    <div>
                        <label class="form-label fw-semibold" for="toDate">To</label
                        ><input
                            id="toDate"
                            type="date"
                            class="form-control"
                            name="to"
                            value="<?php echo $end; ?>"
                            required
                        />
                    </div>
                    <button class="btn btn-create">Generate Reports</button>
                </form>
                <p class="text-muted mt-1 mb-4" style="font-size: 0.75rem;">
                    Generate a report for a certain date range.
                </p>
                <?php endif; ?>
                <div class="report-section-card p-4 mb-4">
                    <h2 class="fs-5">Appointment Statistics</h2>
                    <p class="text-muted small">
                        <?php echo $start .
                            ' to ' .
                            $end; ?>. Booking value excludes cancellations; payments received use
                        payment dates.
                    </p>
                    <div class="row g-3">
                        <?php foreach (
                            [
                                ['Total Bookings', $stats['bookings']],
                                ['Completed', $stats['completed']],
                                ['Cancelled', $stats['cancelled']],
                                ['Booking Value', 'PHP ' . number_format($stats['bookingValue'], 2)],
                                ['Payments Received', 'PHP ' . number_format($received, 2)],
                                ['Outstanding Balance', 'PHP ' . number_format($stats['outstanding'], 2)]
                            ]
                            as [$label, $value]
                        ): ?>
                        <div class="col-sm-6 col-lg-4">
                            <div class="stat-box text-center p-3">
                                <div class="stat-number fw-bold"><?php echo bookingText(
                                    $value
                                ); ?></div>
                                <div class="stat-label text-muted"><?php echo $label; ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="report-section-card p-4">
                            <h2 class="fs-5">Per Beautician</h2>
                            <?php
                            foreach (
                                $perBeautician
                                as $person
                            ): ?>
                            <div class="border-bottom py-3">
                                <strong><?php echo bookingText(
                                    $person['firstName'] . ' ' . $person['lastName']
                                ); ?></strong>
                                <div class="text-muted small">
                                    <?php echo $person[
                                        'bookings'
                                    ]; ?> bookings · <?php echo $person['completed']; ?> completed
                                </div>
                            </div>
                            <?php endforeach;
                            if (!$perBeautician): ?>
                            <p class="text-muted">No bookings in this range.</p>
                            <?php endif;
                            ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="report-section-card p-4">
                            <h2 class="fs-5">Per Client</h2>
                            <?php
                            foreach ($perClient as $person): ?>
                            <div class="border-bottom py-3">
                                <strong><?php echo bookingText(
                                    $person['firstName'] . ' ' . $person['lastName']
                                ); ?></strong>
                                <div class="text-muted small"><?php echo $person[
                                    'bookings'
                                ]; ?> bookings</div>
                            </div>
                            <?php endforeach;
                            if (!$perClient): ?>
                            <p class="text-muted">No bookings in this range.</p>
                            <?php endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </body>
</html>
