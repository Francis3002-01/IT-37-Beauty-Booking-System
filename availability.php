<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once 'config/database.php';
require_once 'includes/booking.php';
$user = bookingLogin($conn);
$admin = strtolower($user['role']) == 'admin';
$beauticianID = bookingValue($_GET, 'beauticianID');
$people = bookingID($beauticianID)
    ? bookingQuery(
        $conn,
        'SELECT beauticianID,firstName,lastName,employmentStatus,dateJoined,dateSeparated FROM beauticians WHERE beauticianID=?',
        'i',
        [$beauticianID]
    )
    : [];
$person = $people[0] ?? null;
if (!$person) {
    header('Location: beauticians.php');
    exit();
}
//only admin can change working hours for active beauticians
$canManage = $admin && $person['employmentStatus'] == 'Active';
$schedules = bookingID($beauticianID)
    ? bookingQuery(
        $conn,
        'SELECT * FROM availability WHERE beauticianID=? ORDER BY availableDate,startTime',
        'i',
        [$beauticianID]
    )
    : [];
$editID = bookingValue($_GET, 'edit');
$edit = [];
foreach ($schedules as $schedule) {
    if ($schedule['availabilityID'] == $editID) {
        $edit = $schedule;
    }
}
$message = $_SESSION['booking_message'] ?? '';
$status = $_SESSION['booking_status'] ?? 'error';
unset($_SESSION['booking_message'], $_SESSION['booking_status']);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Availability - beautyReserve</title>
        <link rel="icon" type="image/svg+xml" href="favicon.svg" />
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
        />
        <link href="css/beauticians.css?v=<?php echo filemtime(
            __DIR__ . '/css/beauticians.css'
        ); ?>" rel="stylesheet" />
    </head>
    <body>
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="dashboard-card">
                <a href="beauticians.php" class="filter-pill d-inline-block text-decoration-none"
                    >&larr; Back to Beauticians</a
                >
                <h1 class="fw-bold fs-3 mt-3">Availability Schedule</h1>
                <p class="text-muted">
                    Appointments can only be booked within the beautician’s working hours.
                </p>
                <?php if ($message != ''): ?>
                <div class="alert alert-<?php echo $status == 'success'
                    ? 'success'
                    : 'danger'; ?>"><?php echo bookingText($message); ?></div>
                <?php endif; ?>
                <div class="mb-4">
                    <h2 class="fs-5 fw-bold">
                        <?php echo bookingText(
                             $person['firstName'] . ' ' . $person['lastName']
                         ); ?>
                        <span class="badge bg-light text-dark">#<?php echo $person['beauticianID']; ?></span>
                    </h2>
                    <?php if ($person['employmentStatus'] == 'Inactive'): ?>
                    <p class="text-danger mb-2">Inactive</p>
                    <?php elseif ($person['employmentStatus'] == 'On Leave'): ?>
                    <p class="text-warning-emphasis mb-2">On Leave</p>
                    <?php endif; ?>
                    <?php if ($person['employmentStatus'] != 'Active'): ?>
                    <div class="d-flex flex-wrap gap-4 text-muted small">
                        <span><strong>Date Joined:</strong> <?php echo bookingText($person['dateJoined']); ?></span>
                        <span><strong>Date Separated:</strong> <?php echo bookingText(
                               $person['dateSeparated'] ?? '-'
                           ); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($canManage): ?>
                <form
                    method="POST"
                    action="backend%20logic/availability_backend.php"
                    class="border rounded-4 p-3 mb-4"
                >
                    <?php bookingTokenField(); ?>
                    <h2 class="fs-5"><?php echo $edit ? 'Edit working hours' : 'Add working hours'; ?></h2>
                    <input type="hidden" name="action" value="<?php echo $edit
                        ? 'edit'
                        : 'create'; ?>" /><input
                        type="hidden"
                        name="beauticianID"
                        value="<?php echo bookingText(
                            $beauticianID
                        ); ?>"
                    /><input type="hidden" name="availabilityID" value="<?php echo $edit['availabilityID'] ??
                        ''; ?>" />
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="scheduleDate">Date</label
                            ><input
                                id="scheduleDate"
                                class="form-control"
                                type="date"
                                name="availableDate"
                                value="<?php echo $edit[
                                    'availableDate'
                                ] ??
                                    ''; ?>"
                                required
                            />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="scheduleStart">Start time</label
                            ><input
                                id="scheduleStart"
                                class="form-control"
                                type="time"
                                name="startTime"
                                value="<?php echo substr(
                                    $edit['startTime'] ?? '09:00:00',
                                    0,
                                    5
                                ); ?>"
                                required
                            />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="scheduleEnd">End time</label
                            ><input
                                id="scheduleEnd"
                                class="form-control"
                                type="time"
                                name="endTime"
                                value="<?php echo substr(
                                    $edit['endTime'] ?? '17:00:00',
                                    0,
                                    5
                                ); ?>"
                                required
                            />
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-create">Save Schedule</button><?php if (
                            $edit
                        ): ?><a
                            class="btn btn-light"
                            href="availability.php?beauticianID=<?php echo bookingText(
                                $beauticianID
                            ); ?>"
                            >Cancel Edit</a
                        ><?php endif; ?>
                    </div>
                </form>
                <?php endif; ?>
                <?php if (
                    $person['employmentStatus'] != 'Active'
                ): ?>
                <h3 class="fs-6 fw-semibold">Previous Working Hours</h3>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Working hours</th>
                                <?php if (
                                    $canManage
                                ): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?php echo bookingText(
                                    $schedule['availableDate']
                                ); ?></td>
                                <td><?php echo substr($schedule['startTime'], 0, 5) .
                                    ' – ' .
                                    substr($schedule['endTime'], 0, 5); ?></td>
                                <?php if (
                                    $canManage
                                ): ?>
                                <td>
                                    <a
                                        class="btn btn-sm btn-outline-secondary rounded-pill"
                                        href="availability.php?beauticianID=<?php echo $beauticianID; ?>&edit=<?php echo $schedule[
                                            'availabilityID'
                                        ]; ?>"
                                        >EDIT</a
                                    >
                                    <form
                                        class="d-inline"
                                        method="POST"
                                        action="backend%20logic/availability_backend.php"
                                        onsubmit="return confirm('Delete these working hours?');"
                                    >
                                        <?php bookingTokenField(); ?><input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        /><input
                                            type="hidden"
                                            name="beauticianID"
                                            value="<?php echo $beauticianID; ?>"
                                        /><input
                                            type="hidden"
                                            name="availabilityID"
                                            value="<?php echo $schedule[
                                                'availabilityID'
                                            ]; ?>"
                                        /><button
                                            class="btn btn-sm btn-outline-danger rounded-pill"
                                        >
                                            DELETE
                                        </button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (!$schedules): ?>
                            <tr>
                                <td colspan="3" class="text-muted">
                                    No working schedules set.<?php if (
                                        $canManage
                                    ) {
                                        echo ' Add working hours above.';
                                    } ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </body>
</html>
