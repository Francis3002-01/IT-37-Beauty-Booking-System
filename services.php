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
    <link href="css/beauticians.css" rel="stylesheet">
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

<!-- Add / Edit Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="serviceForm">
                    <input type="hidden" id="editServiceId">
                    
                    <div class="mb-3">
                        <label for="serviceName" class="form-label field-label">Service Name</label>
                        <input type="text" class="form-control rounded-3" id="serviceName" required placeholder="e.g. Deep Cleansing Facial">
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="category" class="form-label field-label">Category</label>
                            <select class="form-select rounded-3" id="category" required>
                                <option value="Hair">Hair</option>
                                <option value="Face">Face</option>
                                <option value="Nails">Nails</option>
                                <option value="Massage">Massage</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="duration" class="form-label field-label">Duration (Mins)</label>
                            <input type="number" class="form-control rounded-3" id="duration" required placeholder="60">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label field-label">Price (PHP)</label>
                        <input type="number" step="0.01" class="form-control rounded-3" id="price" required placeholder="2000.00">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label field-label">Description</label>
                        <textarea class="form-control rounded-3" id="description" rows="3" placeholder="Brief details about this service..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4" id="saveServiceBtn">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Service Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="viewModalTitle">Service Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Dynamically filled by JS -->
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Javascript Logic -->
<script src="javascript/services.js"></script>

<?php include 'includes/footer.php'; ?>
</body>
</html>