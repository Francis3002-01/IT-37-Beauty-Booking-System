<?php

/**
 * MAIN STAFF DASHBOARD VIEW
 * ------------------------------------------------------------------
 * Purpose: Serves as the main page for managing salon staff members.
 * Architecture: 
 * - Visual shell relying on PHP includes for navigation, sidebar, and modals.
 * - Database Ready: Injects $dbStaffData into JavaScript when connected to MySQL.
 */

$pageTitle = "Staff - beautyReserve";
include 'includes/sidebar.php';

// Database Ready Bridge (Set $dbStaffData in controller when MySQL is connected)
$dbStaffData = $dbStaffData ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'beautyReserve.'; ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Stylesheets -->
    <link href="css/beauticians.css" rel="stylesheet">
    <link href="css/staff.css" rel="stylesheet">
</head>
<body>

<!-- Main Content Area -->
<div class="main-content">
    <div class="dashboard-card">
        <h1 class="fw-bold mb-4" style="font-size: 1.75rem;">Staff</h1>

        <!-- Top Search & Create Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <input type="text" id="searchInput" class="search-pill" placeholder="Search Staff..." oninput="filterStaff()">
            <button class="btn btn-create" onclick="openCreateModal()">Add New Staff</button>
        </div>

        <!-- Filter Pills -->
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <button class="filter-pill active" onclick="setFilter('All', this)">All</button>
            <button class="filter-pill" onclick="setFilter('Makeup Artist', this)">Makeup Artist</button>
            <button class="filter-pill" onclick="setFilter('Hair stylist', this)">Hair Stylist</button>
        </div>

        <!-- Staff Cards Grid -->
        <div class="row" id="staffGrid">
            <!-- Dynamically populated by javascript/staff.js -->
        </div>
    </div>
</div>

<!-- Modals Subfolder Include -->
 <?php include 'includes/modals/staff_modal.php'; ?>

<!-- Bootstrap 5 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Bridge server database records into JS when available -->
<script>
    window.SERVER_STAFF_DATA = <?php echo json_encode($dbStaffData); ?>;
</script>

<!-- Javascript Logic -->
<script src="javascript/staff.js"></script>

<?php include 'includes/footer.php'; ?>
</body>
</html>