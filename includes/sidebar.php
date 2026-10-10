<?php
$currentPage = basename($_SERVER['PHP_SELF']);

/**
 * NAVIGATION SIDEBAR TEMPLATE
 * ------------------------------------------------------------------
 * Purpose: Renders the primary navigation menu for the dashboard.
 * Why include this? 
 * 1. Keeps navigation uniform across all admin and user pages.
 * 2. Adding a new module or link in the future only requires modifying this single file.
 */
?>
<div class="sidebar">
    <div>
        <div class="brand-title">beautyReserve.</div>
        
        <div class="nav-section-title">My Panel</div>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($currentPage == 'appointments.php') ? 'active' : 'sub-nav'; ?>" href="appointments.php">Appointments</a>
            <a class="nav-link <?php echo ($currentPage == 'beauticians.php') ? 'active' : 'sub-nav'; ?>" href="beauticians.php">Beauticians</a>
            <a class="nav-link <?php echo ($currentPage == 'services.php') ? 'active' : 'sub-nav'; ?>" href="services.php">Services</a>
            <a class="nav-link <?php echo ($currentPage == 'clients.php') ? 'active' : 'sub-nav'; ?>" href="clients.php">Clients</a>
            <a class="nav-link <?php echo ($currentPage == 'reports.php') ? 'active' : 'sub-nav'; ?>" href="reports.php">Reports</a>
            <a class="nav-link <?php echo ($currentPage == 'staff.php') ? 'active' : 'sub-nav'; ?>" href="staff.php">Staff</a>
        </nav>

        <div class="nav-section-title mt-4">My Account</div>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($currentPage == 'profile-settings.php') ? 'active' : ''; ?>" href="profile-settings.php">Profile Settings</a>
        </nav>
    </div>

    <!-- User Profile & Black Sign Out Button -->
    <div class="user-profile border-top pt-3 mt-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="avatar-circle"></div>
                <div>
                    <h6 class="mb-0 fw-bold"><?php echo $_SESSION['user_name'] ?? 'John Francis'; ?></h6>
                    <small class="text-muted"><?php echo $_SESSION['user_role'] ?? 'Administrator'; ?></small>
                </div>
            </div>
            <!-- Black Sign Out Button -->
            <a href="includes/logout.php" class="btn btn-sm btn-dark" title="Sign Out">
                <i class="bi bi-box-arrow-right"></i> Sign Out
            </a>
        </div>
    </div>
</div>