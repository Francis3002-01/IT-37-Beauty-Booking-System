<?php
require_once __DIR__ . '/config/database.php';

// Fetch clients for the appointment form.
$clientsResult = $conn->query(
    "SELECT clientID, lastName, firstName, address, contactNo
     FROM client
     ORDER BY lastName, firstName"
);
$dbClients = $clientsResult->fetch_all(MYSQLI_ASSOC);

// Fetch services.
$servicesResult = $conn->query(
    "SELECT serviceID, serviceName, serviceCategory, price
     FROM service
     ORDER BY serviceName"
);
$dbServices = $servicesResult->fetch_all(MYSQLI_ASSOC);

// Fetch active beauticians.
$beauticiansResult = $conn->query(
    "SELECT beauticianID, firstName, lastName, beauticianType
     FROM beauticians
     WHERE employmentStatus = 'Active'
     ORDER BY lastName, firstName"
);
$dbBeauticians = $beauticiansResult->fetch_all(MYSQLI_ASSOC);

// Fetch saved appointments and their related records.
$appointmentsSql = "
    SELECT
        a.appointmentID,
        ac.clientID,
        c.firstName AS clientFirstName,
        c.lastName AS clientLastName,
        c.address AS clientAddress,
        c.contactNo AS clientContactNo,
        a.serviceID,
        s.serviceName,
        s.serviceCategory,
        s.price AS servicePrice,
        ab.beauticianID AS resourceID,
        b.firstName AS beauticianFirstName,
        b.lastName AS beauticianLastName,
        b.beauticianType,
        a.appointmentDate,
        TIME_FORMAT(a.startTime, '%H:%i') AS startTime,
        TIME_FORMAT(a.endTime, '%H:%i') AS endTime,
        a.appointmentType,
        a.venue,
        a.appointmentStatus,
        a.totalCost,
        a.notes AS appointmentNotes,
        CASE
            WHEN EXISTS (
                SELECT 1
                FROM payment p
                WHERE p.appointmentID = a.appointmentID
                  AND p.clientID = ac.clientID
            ) THEN 'Paid'
            ELSE 'Unpaid'
        END AS paymentStatus
    FROM appointment a
    INNER JOIN apt_client ac
        ON ac.appointmentID = a.appointmentID
    INNER JOIN client c
        ON c.clientID = ac.clientID
    INNER JOIN service s
        ON s.serviceID = a.serviceID
    INNER JOIN apt_beautician ab
        ON ab.appointmentID = a.appointmentID
    INNER JOIN beauticians b
        ON b.beauticianID = ab.beauticianID
    ORDER BY
        a.appointmentDate DESC,
        a.startTime DESC,
        a.appointmentID DESC
";

$appointmentsResult = $conn->query($appointmentsSql);
$dbAppointments = $appointmentsResult->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Appointments - beautyReserve";
include 'includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link href="css/appointments.css" rel="stylesheet">
</head>

<body>

    <div class="main-content">
        <div class="dashboard-card">

            <h1 class="fw-bold mb-4" style="font-size: 1.75rem;">
                Appointments
            </h1>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    Appointment saved successfully.
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    Appointment could not be saved.
                    Please check the required fields, availability,
                    and PHP error log.
                </div>
            <?php endif; ?>

            <!-- Search and Create Button -->
            <div class="d-flex justify-content-between align-items-center
                    mb-4 flex-wrap gap-3">

                <input
                    type="text"
                    id="searchInput"
                    class="search-pill"
                    placeholder="Search appointments..."
                    oninput="filterAppointments()">

                <button
                    type="button"
                    class="btn btn-create"
                    data-bs-toggle="modal"
                    data-bs-target="#appointmentModal"
                    onclick="openCreateModal()">
                    Create New Appointment
                </button>

            </div>

            <!-- Filter Pills -->
            <div class="d-flex gap-2 mb-4 flex-wrap">
                <button class="filter-pill active" onclick="setFilter('All', this)">All</button>
                <button class="filter-pill" onclick="setFilter('Pending', this)">Pending</button>
                <button class="filter-pill" onclick="setFilter('Confirmed', this)">Confirmed</button>
                <button class="filter-pill" onclick="setFilter('Completed', this)">Completed</button>
                <button class="filter-pill" onclick="setFilter('Cancelled', this)">Cancelled</button>
            </div>

            <!-- Database Appointments Display -->
            <div class="row" id="appointmentsGrid"></div>

        </div>
    </div>

    <!-- Appointment Modals -->
    <?php include 'includes/modals/appointment_modal.php'; ?>

    <!-- Send Database Records to JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.querySelector('.alert-success');

            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.transition = 'opacity 0.5s ease';
                    successMessage.style.opacity = '0';

                    setTimeout(function() {
                        successMessage.remove();
                    }, 500);
                }, 3000);
            }
        });
        window.dbClients = <?= json_encode(
                                $dbClients,
                                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
                            ) ?>;

        window.dbServices = <?= json_encode(
                                $dbServices,
                                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
                            ) ?>;

        window.dbBeauticians = <?= json_encode(
                                    $dbBeauticians,
                                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
                                ) ?>;

        window.dbAppointments = <?= json_encode(
                                    $dbAppointments,
                                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
                                ) ?>;
    </script>

    <script src="javascript/appointment.js"></script>

    <?php include 'includes/footer.php'; ?>

</body>

</html>