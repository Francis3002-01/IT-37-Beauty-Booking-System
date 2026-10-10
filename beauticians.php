<?php

/**
 * MAIN BEAUTICIANS DASHBOARD VIEW
 * ------------------------------------------------------------------
 * Purpose: Serves as the main page for managing salon beauticians.
 * Architecture: 
 * - Acts as the visual shell. It relies on PHP includes for layout components 
 *   (Header, Sidebar, Modals, Footer) and JavaScript for dynamic content rendering.
 */

$pageTitle = "Beauticians - beautyReserve";
//include 'includes/header.php';
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
    <!-- Custom Stylesheet (Updated to match your file name) -->
    <link href="css/beauticians.css" rel="stylesheet">
</head>
<body>

<!-- Main Content Area -->
<div class="main-content">
    <div class="dashboard-card">
        <h1 class="fw-bold mb-4" style="font-size: 1.75rem;">Beauticians</h1>

        <!-- Top Search & Create Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <input type="text" id="searchInput" class="search-pill" placeholder="Search beauticians..." oninput="filterBeauticians()">
            <button type="button" class="btn btn-create" onclick="openCreateModal()">Add New Beautician</button>
        </div>

        <!-- Filter Pills -->
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <button class="filter-pill active" onclick="setFilter('All', this)">All</button>
            <button class="filter-pill" onclick="setFilter('Hair stylist', this)">Hair Stylist</button>
            <button class="filter-pill" onclick="setFilter('Makeup Artist', this)">Makeup Artist</button>
        </div>

        <!-- Beauticians Cards Grid -->
        <div class="row" id="beauticiansGrid">
            <!-- Dynamically populated by javascript/beautician.js -->
        </div>
    </div>
</div>

<!-- Modals Subfolder Include -->
<?php include 'includes/modals/beauticians_modal.php'; ?>

<!-- Javascript Logic -->
<script src="javascript/beauticians.js"></script>

<?php include 'includes/footer.php'; ?>