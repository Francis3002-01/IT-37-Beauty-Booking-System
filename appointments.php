<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once 'config/database.php';
require_once 'includes/booking.php';
date_default_timezone_set('Asia/Manila');
$user = bookingLogin($conn);
$admin = strtolower($user['role']) == 'admin';
//get the client, service and active beautician options for the form
$clients = bookingQuery(
    $conn,
    'SELECT clientID,firstName,lastName FROM client ORDER BY firstName,lastName'
);
$services = bookingQuery(
    $conn,
    'SELECT serviceID,serviceName,duration,price FROM service ORDER BY serviceName'
);
$beauticians = bookingQuery(
    $conn,
    'SELECT beauticianID,firstName,lastName,beauticianType,employmentStatus FROM beauticians WHERE employmentStatus=\'Active\' ORDER BY firstName,lastName'
);
//restore form values after an error or availability check
$form = $_SESSION['booking_form'] ?? [];
unset($_SESSION['booking_form']);
$editID = bookingValue($_GET, 'edit');
if (bookingID($editID) && $admin) {
    $rows = bookingQuery(
        $conn,
        'SELECT a.*, (SELECT MIN(clientID) FROM apt_client WHERE appointmentID=a.appointmentID) AS clientID, (SELECT MIN(beauticianID) FROM apt_beautician WHERE appointmentID=a.appointmentID) AS beauticianID FROM appointment a WHERE a.appointmentID=?',
        'i',
        [$editID]
    );
    if ($rows) {
        $form = $rows[0];
        $form['action'] = 'edit';
        $form['startTime'] = substr($form['startTime'], 0, 5);
        $form['endTime'] = substr($form['endTime'], 0, 5);
    }
}
if (isset($_GET['check'])) {
    foreach (
        [
            'appointmentID',
            'action',
            'clientID',
            'serviceID',
            'beauticianID',
            'appointmentDate',
            'startTime',
            'endTime',
            'appointmentType',
            'venue',
            'notes',
            'paymentMethod',
            'amountPaid'
        ]
        as $key
    ) {
        $form[$key] = bookingValue($_GET, $key);
    }
}
$checked =
    bookingDate(bookingValue($form, 'appointmentDate')) &&
    bookingTime(bookingValue($form, 'startTime')) &&
    bookingTime(bookingValue($form, 'endTime')) &&
    $form['startTime'] < $form['endTime'];
$availability = [];
if ($checked) {
    foreach ($beauticians as $person) {
        $availability[$person['beauticianID']] = beauticianAvailability(
            $conn,
            $person,
            $form['appointmentDate'],
            $form['startTime'],
            $form['endTime'],
            (int) ($form['appointmentID'] ?? 0)
        );
    }
}
$message = $_SESSION['booking_message'] ?? '';
$status = $_SESSION['booking_status'] ?? 'error';
unset($_SESSION['booking_message'], $_SESSION['booking_status']);
//get booking details and payment totals without duplicating appointment costs
$appointments = bookingQuery(
    $conn,
    "SELECT a.*,s.serviceName,u.userName,
 (SELECT GROUP_CONCAT(CONCAT(c.firstName,' ',c.lastName) SEPARATOR ', ') FROM apt_client ac JOIN client c ON c.clientID=ac.clientID WHERE ac.appointmentID=a.appointmentID) AS clientNames,
 (SELECT GROUP_CONCAT(CONCAT(b.firstName,' ',b.lastName) SEPARATOR ', ') FROM apt_beautician ab JOIN beauticians b ON b.beauticianID=ab.beauticianID WHERE ab.appointmentID=a.appointmentID) AS beauticianNames,
 (SELECT COALESCE(SUM(p.amount),0) FROM payment p WHERE p.appointmentID=a.appointmentID) AS paid
 FROM appointment a JOIN service s ON s.serviceID=a.serviceID JOIN `user` u ON u.userID=a.userID ORDER BY a.appointmentDate DESC,a.startTime DESC"
);
$openForm = boolval($form) || isset($_GET['check']);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Appointments - beautyReserve</title>
        <link rel="icon" type="image/svg+xml" href="favicon.svg" />
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
        />
        <link href="css/appointments.css?v=<?php echo filemtime(
            __DIR__ . '/css/appointments.css'
        ); ?>" rel="stylesheet" />
    </head>
    <body>
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="dashboard-card">
                <h1 class="fw-bold mb-4 fs-3">Appointments</h1>
                <?php if ($message != ''): ?>
                <div
                    id="appointmentFeedback"
                    data-status="<?php echo bookingText(
                        $status
                    ); ?>"
                    class="alert alert-<?php echo $status == 'success'
                        ? 'success'
                        : 'danger'; ?>"
                    role="alert"
                >
                    <?php echo bookingText($message); ?>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <input
                        id="searchInput"
                        class="search-pill"
                        placeholder="Search Appointments..."
                        aria-label="Search appointments"
                        oninput="filterAppointments()"
                    /><a class="btn btn-create" href="appointments.php?new=1"
                        >Create New Appointment</a
                    >
                </div>
                <div class="d-flex align-items-center flex-wrap gap-3 mb-4">
                    <select
                        id="statusFilter"
                        class="form-select filter-pill filter-dropdown w-auto"
                        aria-label="Status"
                        onchange="filterAppointments()"
                    >
                        <option value="All">Status</option>
                        <?php foreach (
                            ['Pending', 'Cancelled', 'Completed']
                            as $value
                        ): ?>
                        <option><?php echo $value; ?></option>
                        <?php endforeach; ?></select
                    ><input
                        id="dateFilter"
                        type="date"
                        class="form-control w-auto"
                        aria-label="Filter by appointment date"
                        onchange="filterAppointments()"
                    /><button class="filter-pill" onclick="clearAppointmentFilters()">
                        Clear Filters
                    </button>
                </div>
                <div class="row g-3" id="appointmentsGrid">
                    <?php
                    $appointmentModals = '';
                    foreach ($appointments as $appointment):

                        $id = (int) $appointment['appointmentID'];
                        $balance = max(0, round($appointment['totalCost'] - $appointment['paid'], 2));
                        $paymentStatus =
                            $balance == 0 ? 'Paid' : ($appointment['paid'] > 0 ? 'Partially Paid' : 'Unpaid');
                        $active = in_array($appointment['appointmentStatus'], ['Pending', 'Confirmed'], true);
                        $payers = bookingQuery(
                            $conn,
                            'SELECT c.clientID,c.firstName,c.lastName FROM apt_client ac JOIN client c ON c.clientID=ac.clientID WHERE ac.appointmentID=?',
                            'i',
                            [$id]
                        );
                        $payments = bookingQuery(
                            $conn,
                            'SELECT p.*,c.firstName,c.lastName FROM payment p JOIN client c ON c.clientID=p.clientID WHERE p.appointmentID=? ORDER BY p.paymentDate',
                            'i',
                            [$id]
                        );
                        ?>
                    <div
                        class="col-md-6 col-xl-4 appointment-record"
                        data-status="<?php echo bookingText(
                            $appointment['appointmentStatus']
                        ); ?>"
                        data-date="<?php echo $appointment['appointmentDate']; ?>"
                    >
                        <div class="appointment-item-card h-100 mb-0">
                            <h2 class="fs-5 fw-bold mb-2">Appointment #<?php echo $id; ?></h2>
                            <div class="d-flex gap-2 flex-wrap mb-3">
                                <span class="badge fw-bold appointment-status-<?php echo strtolower(bookingText($appointment['appointmentStatus'])); ?>"><?php echo bookingText(
                                      $appointment['appointmentStatus']
                                  ); ?></span
                                ><span class="badge fw-bold <?php echo $paymentStatus == 'Paid'
                                    ? 'bg-success'
                                    : 'bg-secondary'; ?>"><?php echo $paymentStatus; ?></span>
                            </div>
                            <p class="mb-1 small text-secondary">
                                <span class="fw-medium">Client:</span> <?php echo bookingText(
                                      $appointment['clientNames']
                                  ); ?>
                            </p>
                            <p class="mb-1 small text-secondary">
                                <span class="fw-medium">Service:</span> <?php echo bookingText(
                                      $appointment['serviceName']
                                  ); ?>
                            </p>
                            <p class="mb-1 small text-secondary">
                                <span class="fw-medium">Assigned Beautician:</span> <?php echo bookingText(
                                      $appointment['beauticianNames']
                                  ); ?>
                            </p>
                            <p class="mb-1 small text-secondary">
                                <span class="fw-medium">Date and Time:</span> <?php echo $appointment[
                                      'appointmentDate'
                                  ] .
                                      ' · ' .
                                      substr($appointment['startTime'], 0, 5) .
                                      '–' .
                                      substr($appointment['endTime'], 0, 5); ?>
                            </p>
                            <p class="fw-semibold text-secondary text-start mt-1 mb-2">
                                PHP <?php echo number_format(
                                      $appointment['totalCost'],
                                      2
                                  ); ?>
                            </p>
                            <div class="border-top pt-3 d-flex flex-wrap gap-1">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-dark rounded-pill"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewAppointment<?php echo $id; ?>"
                                >
                                    VIEW
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-dark rounded-pill"
                                    data-bs-toggle="modal"
                                    data-bs-target="#paymentAppointment<?php echo $id; ?>"
                                >
                                    PAYMENT DETAILS
                                </button>
                            </div>
                            <?php if ($active): ?>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                <?php if (
                                       $admin
                                   ): ?><a
                                    class="btn btn-sm btn-outline-secondary rounded-pill"
                                    href="appointments.php?edit=<?php echo $id; ?>"
                                    >EDIT</a
                                ><?php endif; ?>
                                <form
                                    method="POST"
                                    action="backend%20logic/appointment_backend.php"
                                    class="d-flex flex-wrap gap-1"
                                    onsubmit="return confirm('Change this appointment status?');"
                                >
                                    <?php bookingTokenField(); ?><input
                                        type="hidden"
                                        name="action"
                                        value="status"
                                    /><input
                                        type="hidden"
                                        name="appointmentID"
                                        value="<?php echo $id; ?>"
                                    /><button
                                        name="appointmentStatus"
                                        value="Completed"
                                        class="btn btn-sm <?php echo $paymentStatus ==
                                        'Paid'
                                            ? 'btn-outline-success'
                                            : 'btn-outline-secondary'; ?> rounded-pill"
                                        <?php if ($paymentStatus != 'Paid') {
                                             echo 'disabled';
                                         } ?>
                                    >
                                        MARK COMPLETED</button
                                    ><button
                                        name="appointmentStatus"
                                        value="Cancelled"
                                        class="btn btn-sm btn-outline-danger rounded-pill"
                                        <?php if (
                                            $paymentStatus == 'Paid'
                                        ) {
                                            echo 'disabled';
                                        } ?>
                                    >
                                        CANCEL
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                    ob_start();
                    include 'includes/modals/appointment_record_modals.php';
                    $appointmentModals .= ob_get_clean();

                    endforeach;
                    ?>
                </div>
                <p id="noAppointmentsMessage" class="text-center text-muted py-4" <?php if ($appointments) {
                    echo 'hidden';
                } ?>>
                    No appointments found.
                </p>
            </div>
        </div>
        <?php
        echo $appointmentModals;
        include 'includes/modals/appointment_modal.php';
        ?>
        <script src="javascript/appointment.js?v=<?php echo filemtime(
            __DIR__ . '/javascript/appointment.js'
        ); ?>"></script>
        <?php include 'includes/footer.php'; ?>
    </body>
</html>
