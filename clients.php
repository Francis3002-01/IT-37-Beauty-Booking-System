<?php
/**
 * MAIN CLIENTS DASHBOARD VIEW
 * ------------------------------------------------------------------
 * Purpose: Serves as the main page for managing salon clients.
 * Architecture: Relies on PHP includes for layout components and
 * JavaScript for dynamic rendering matching the wireframe layout.
 */

$pageTitle = "Clients - beautyReserve";

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
    <!-- Module Specific CSS for Clients -->
    <link href="css/clients.css" rel="stylesheet">
</head>
<body>

<!-- Main Content Area -->
<div class="main-content">
    <div class="dashboard-card">
        <h1 class="fw-bold mb-4" style="font-size: 1.75rem;">Clients</h1>

        <!-- Top Search & Add Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <input type="text" id="searchInput" class="search-pill" placeholder="Search Clients..." oninput="filterClients()">
            <button class="btn btn-create" data-bs-toggle="modal" data-bs-target="#clientModal" onclick="openCreateModal()">Add New Client</button>
        </div>

        <!-- Filter Pills -->
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <button class="filter-pill active" onclick="setFilter('All', this)">All</button>
            <button class="filter-pill" onclick="setFilter('Recent', this)">Recently Added</button>
            <button class="filter-pill" onclick="setFilter('AZ', this)">Name (A-Z)</button>
        </div>

        <!-- Clients List (Stacked Horizontal Rows as depicted in wireframe) -->
        <div class="d-flex flex-column gap-3" id="clientsList">
            <!-- Dynamically populated by javascript/clients.js -->
        </div>
    </div>
</div>

<!-- Modals Include -->
<?php include 'includes/modals/clients_modal.php'; ?>

<!-- Bootstrap 5 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Clients Module JS -->
<script src="javascript/clients.js"></script>

<?php include 'includes/footer.php'; ?>
</body>
</html>