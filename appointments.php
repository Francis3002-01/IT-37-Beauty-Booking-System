<?php

/**
 * MAIN APPOINTMENTS DASHBOARD VIEW
 * ------------------------------------------------------------------
 * Purpose: Serves as the main page for managing salon appointments.
 * Architecture: 
 * - Acts as the visual shell. It relies on PHP includes for layout components 
 *   (Header, Sidebar, Modals, Footer) and JavaScript for dynamic content rendering.
 */

$pageTitle = "Appointments - beautyReserve";
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content Area -->
<div class="main-content">
    <div class="dashboard-card">
        <h1 class="fw-bold mb-4" style="font-size: 1.75rem;">Appointments</h1>

        <!-- Top Search & Create Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <input type="text" id="searchInput" class="search-pill" placeholder="Search appointments..." oninput="filterAppointments()">
            <button class="btn btn-create" data-bs-toggle="modal" data-bs-target="#appointmentModal" onclick="openCreateModal()">Create New Appointment</button>
        </div>

        <!-- Filter Pills -->
        <div class="d-flex gap-2 mb-4">
            <button class="filter-pill active" onclick="setFilter('All', this)">All</button>
            <button class="filter-pill" onclick="setFilter('Pending', this)">Pending</button>
            <button class="filter-pill" onclick="setFilter('Confirmed', this)">Confirmed</button>
            <button class="filter-pill" onclick="setFilter('Completed', this)">Completed</button>
            <button class="filter-pill" onclick="setFilter('Cancelled', this)">Cancelled</button>
        </div>

        <!-- Appointments Cards Grid -->
        <div class="row" id="appointmentsGrid">
            <!-- Dynamically populated by javascript/appointment.js -->
            <!--Will be updated to dynamic database display later once database is designed-->
        </div>
    </div>
</div>

<!-- Modals Subfolder Include -->
<?php include 'includes/modals/appointment_modal.php'; ?>

<!-- Javascript Logic (Updated to match your singular file name) -->
<script src="javascript/appointment.js"></script>

<?php include 'includes/footer.php'; ?>