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
            <a class="nav-link <?php echo ($currentPage == 'reports.php') ? 'active' : 'sub-nav'; ?>" href="#">Reports</a>
            <a class="nav-link <?php echo ($currentPage == 'staff.php') ? 'active' : 'sub-nav'; ?>" href="#">Staff</a>
        </nav>

        <div class="nav-section-title mt-4">My Account</div>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($currentPage == 'profile.php') ? 'active' : ''; ?>" href="#">Profile Settings</a>
        </nav>
    </div>

    <!-- User Profile Box -->
    <div class="user-profile-box">
        <div class="user-avatar"></div>
        <div>
            <div class="fw-bold fs-6"><?php echo $_SESSION['user_name'] ?? 'John Francis'; ?></div>
            <div class="text-muted" style="font-size: 0.75rem;"><?php echo $_SESSION['user_role'] ?? 'Administrator'; ?></div>
        </div>
    </div>
</div>