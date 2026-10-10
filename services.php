<?php

/**
 * MAIN SERVICES DASHBOARD VIEW
 * ------------------------------------------------------------------
 * Purpose: Serves as the main page for managing salon services.
 * Architecture: 
 * - Acts as the visual shell. It relies on PHP includes for layout components 
 *   (Header, Sidebar, Modals, Footer) and JavaScript for dynamic content rendering.
 */

$pageTitle = "Services - beautyReserve";
// include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'beautyReserve.'; ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Stylesheets -->
    <link href="css/services.css" rel="stylesheet">
</head>
<body>

<!-- Main Content Area -->
<div class="main-content">
    <div class="dashboard-card">
        <h1 class="fw-bold mb-4" style="font-size: 1.75rem;">Services</h1>

        <!-- Top Search & Create Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <input type="text" id="searchInput" class="search-pill" placeholder="Search Services..." oninput="filterServices()">
           <button class="btn btn-create" onclick="openCreateModal()">Add New Service</button>
        </div>

        <!-- Filter Pills -->
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <button class="filter-pill active" onclick="setFilter('All', this)">All</button>
            <button class="filter-pill" onclick="setFilter('Hair', this)">Hair</button>
            <button class="filter-pill" onclick="setFilter('Face', this)">Face</button>
            <button class="filter-pill" onclick="setFilter('Nails', this)">Nails</button>
            <button class="filter-pill" onclick="setFilter('Massage', this)">Massage</button>
        </div>

        <!-- Services Cards Grid -->
        <div class="row" id="servicesGrid">
            <!-- Dynamically populated by javascript/services.js -->
        </div>
    </div>
</div>

<!-- Modals Include -->
<?php include 'includes/modals/services_modal.php'; ?>

<!-- Bootstrap 5 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Javascript Logic -->
<script src="javascript/services.js"></script>

<?php include 'includes/footer.php'; ?>
</body>
</html>