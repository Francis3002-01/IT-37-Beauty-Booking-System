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

//config

require_once __DIR__ . '/config/database.php';

$result = $conn->query(
    'SELECT serviceID, serviceName,
            serviceCategory AS category,
            duration, price, description
     FROM service
     ORDER BY serviceID'
);

$dbServices = [];
while ($service = $result->fetch_assoc()) {
    $dbServices[] = $service;
}
$result->free();
$conn->close();

function serviceText($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

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
    <link href="css/services.css?v=<?php echo filemtime(__DIR__ . '/css/services.css'); ?>" rel="stylesheet">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="main-content">
    <div class="dashboard-card">
        <h1 class="fw-bold mb-4" style="font-size: 1.75rem;">Services</h1>
        <?php if (isset($_GET['message']) && is_string($_GET['message'])): ?>
            <div id="serviceFeedback" class="alert alert-<?php echo (isset($_GET['status']) && $_GET['status'] === 'success') ? 'success' : 'danger'; ?>" role="alert">
                <?php echo serviceText($_GET['message']); ?>
            </div>
        <?php endif; ?>

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
            <?php foreach ($dbServices as $service): ?>
                <div class="col-md-6 col-lg-6 mb-4 service-record"
                     id="service-<?php echo (int) $service['serviceID']; ?>"
                     data-name="<?php echo serviceText($service['serviceName']); ?>"
                     data-category="<?php echo serviceText($service['category']); ?>"
                     data-duration="<?php echo (int) $service['duration']; ?>"
                     data-price="<?php echo serviceText($service['price']); ?>"
                     data-description="<?php echo serviceText($service['description']); ?>">
                    <div class="service-card">
                        <div class="service-image-placeholder"></div>
                        <h4 class="service-title"><?php echo serviceText($service['serviceName']); ?></h4>
                        <div class="d-flex justify-content-between service-meta">
                            <span><?php echo serviceText($service['category']); ?></span>
                            <span><?php echo (int) $service['duration']; ?> min.</span>
                        </div>
                        <p class="service-description"><?php echo serviceText($service['description']); ?></p>
                        <div class="service-price-row">
                            <div class="service-price">PHP <?php echo number_format((float) $service['price'], 2); ?></div>
                        </div>
                        <div class="service-card-footer">
                            <button type="button" class="btn btn-sm btn-outline-dark px-3 rounded-pill" onclick="viewService(<?php echo (int) $service['serviceID']; ?>)">VIEW</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3 rounded-pill" onclick="editService(<?php echo (int) $service['serviceID']; ?>)">EDIT</button>
                            <form method="POST" action="backend%20logic/services_backend.php" onsubmit="return confirmServiceDelete()" class="d-inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="serviceID" value="<?php echo (int) $service['serviceID']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger px-3 rounded-pill">DELETE</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div id="noServicesMessage" class="col-12 text-center text-muted py-5" <?php if (count($dbServices) > 0) echo 'hidden'; ?>>No services found.</div>
        </div>
    </div>
</div>

<!-- Modals Include -->
<?php include 'includes/modals/services_modal.php'; ?>

<!-- Javascript Logic -->
<script src="javascript/services.js?v=<?php echo filemtime(__DIR__ . '/javascript/services.js'); ?>"></script>

<?php include 'includes/footer.php'; ?>
