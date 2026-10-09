<?php

/**
 * MAIN REPORTS DASHBOARD VIEW
 * ------------------------------------------------------------------
 * Purpose: Serves as the main page for managing salon appointment statistics and reports.
 * Architecture: 
 * - Acts as the visual shell. Relies on PHP includes for sidebar/footer
 *   and JavaScript for dynamic card rendering.
 * - Database Ready: Injects $dbReportsData into JavaScript when connected.
 */

$pageTitle = "Reports - beautyReserve";
include 'includes/sidebar.php';

// Database Ready Bridge (Set $dbReportsData in controller when MySQL is connected)
$dbReportsData = $dbReportsData ?? null;
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
    <link href="css/reports.css" rel="stylesheet">
</head>
<body>

<!-- Main Content Area -->
<div class="main-content">
    <div class="dashboard-card">
        <!-- Header & Subtitle -->
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">Reports</h1>
        <p class="text-muted mb-4 fs-6">Overview of bookings, revenue performance, and staff metrics.</p>

        <!-- SECTION 1: APPOINTMENT STATISTICS CARD -->
        <div class="report-section-card p-4 mb-4">
            <h2 class="fw-bold fs-4 mb-3">Appointment Statistics</h2>
            
            <!-- Controls Bar: Timeframe Pills & Date Range -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div class="d-flex gap-2">
                    <button class="filter-pill" onclick="setPeriod('Daily', this)">Daily</button>
                    <button class="filter-pill" onclick="setPeriod('Weekly', this)">Weekly</button>
                    <button class="filter-pill active" onclick="setPeriod('Monthly', this)">Monthly</button>
                </div>

                <!-- Date Range Display Pill -->
                <div class="date-range-pill d-flex align-items-center gap-2">
                    <i class="fa-regular fa-calendar text-muted"></i>
                    <span id="dateRangeDisplay">OCT 01, 2026 - OCT 31, 2026</span>
                </div>
            </div>

            <!-- 6 STATS GRID (2 Rows x 3 Columns) -->
            <div class="row g-3">
                <!-- Total Bookings -->
                <div class="col-md-4">
                    <div class="stat-box text-center p-4">
                        <div class="stat-number fw-bold" id="statTotalBookings">0</div>
                        <div class="stat-label text-muted fw-semibold">Total Bookings</div>
                    </div>
                </div>
                <!-- Completed -->
                <div class="col-md-4">
                    <div class="stat-box text-center p-4">
                        <div class="stat-number fw-bold" id="statCompleted">0</div>
                        <div class="stat-label text-muted fw-semibold">Completed</div>
                    </div>
                </div>
                <!-- Cancelled -->
                <div class="col-md-4">
                    <div class="stat-box text-center p-4">
                        <div class="stat-number fw-bold" id="statCancelled">0</div>
                        <div class="stat-label text-muted fw-semibold">Cancelled</div>
                    </div>
                </div>
                <!-- Booking Value -->
                <div class="col-md-4">
                    <div class="stat-box text-center p-4">
                        <div class="stat-number fw-bold" id="statBookingValue">PHP 0.00</div>
                        <div class="stat-label text-muted fw-semibold">Booking Value</div>
                    </div>
                </div>
                <!-- Paid -->
                <div class="col-md-4">
                    <div class="stat-box text-center p-4">
                        <div class="stat-number fw-bold" id="statPaid">PHP 0.00</div>
                        <div class="stat-label text-muted fw-semibold">Paid</div>
                    </div>
                </div>
                <!-- Unpaid -->
                <div class="col-md-4">
                    <div class="stat-box text-center p-4">
                        <div class="stat-number fw-bold" id="statUnpaid">PHP 0.00</div>
                        <div class="stat-label text-muted fw-semibold">Unpaid</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: PER BEAUTICIAN & PER CLIENT GRID -->
        <div class="row g-4">
            <!-- Per Beautician List -->
            <div class="col-md-6">
                <div class="report-section-card p-4 h-100">
                    <h3 class="fw-bold fs-5 mb-3">Per Beautician</h3>
                    <div class="d-flex flex-column gap-3" id="perBeauticianList">
                        <!-- Dynamically populated by javascript/reports.js -->
                    </div>
                </div>
            </div>

            <!-- Per Client List -->
            <div class="col-md-6">
                <div class="report-section-card p-4 h-100">
                    <h3 class="fw-bold fs-5 mb-3">Per Client</h3>
                    <div class="d-flex flex-column gap-3" id="perClientList">
                        <!-- Dynamically populated by javascript/reports.js -->
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Bootstrap 5 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Bridge server database records into JS when available -->
<script>
    window.SERVER_REPORTS_DATA = <?php echo json_encode($dbReportsData); ?>;
</script>

<!-- Javascript Logic -->
<script src="javascript/reports.js"></script>

<?php include 'includes/footer.php'; ?>
</body>
</html>