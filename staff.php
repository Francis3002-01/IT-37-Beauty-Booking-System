<?php
/* TAB VIEW FOR STAFF
 * ------------------------------------------------------------------
 * Main tab for managing salon staff.
 * Acts as the visual shell. It relies on PHP includes for layout components (Header, Sidebar, Modals, Footer) and JavaScript for dynamic content rendering.
*/

$pageTitle = 'Staff - beautyReserve';

//config db
require_once __DIR__ . '/config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(); //start session if not already started
}
$message = $_SESSION['staff_message'] ?? '';
$status = $_SESSION['staff_status'] ?? 'error';
$messageDuration = (int) ($_SESSION['staff_message_duration'] ?? 3000);
unset($_SESSION['staff_message'], $_SESSION['staff_status'], $_SESSION['staff_message_duration']);

//retrieve staff records from database
$sql = "SELECT staff.staffID, staff.staffType, staff.firstName, staff.lastName, staff.address, staff.contactNo, staff.employmentStatus, staff.dateJoined, staff.dateSeparated, `user`.userName, (staff.imageData IS NOT NULL) AS hasImage 
        FROM staff 
        LEFT JOIN `user` ON staff.staffID = `user`.staffID";
$result = $conn->query($sql);
$staffRecords =$result->fetch_all(MYSQLI_ASSOC);

$result->free();
$conn->close();

function staffText($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES); //type errorr guard. htmlspecialchars convert, en_quotes converts '"
}
$canManageStaff = in_array(strtolower($_SESSION['user_role'] ?? ''), ['admin', 'staff'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo staffText($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/staff.css?v=<?php echo filemtime(__DIR__ . '/css/staff.css'); ?>" rel="stylesheet">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <div class="dashboard-card">
    <h1 class="fw-bold mb-4" style="font-size:1.75rem;">Staff</h1>
    
    <!-- Conditional feedback alert (hidden when empty) -->
    <?php if (!empty($message)): ?>
        <div id="staffFeedback" data-duration="<?php echo $messageDuration; ?>" class="alert alert-<?php echo $status === 'success' ? 'success' : 'danger'; ?>" role="alert">
            <?php echo staffText($message); ?>
        </div>
    <?php endif; ?>

    <!-- Search box and Add New Staff button -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <input type="text" id="searchInput" class="search-pill" placeholder="Search Staff..." oninput="filterStaff()">
        
        <?php if ($canManageStaff): ?>
            <button type="button" class="btn btn-create" onclick="openCreateModal()">Add New Staff</button>
        <?php endif; ?>
    </div>

    <!-- Status filter dropdown -->
    <div class="d-flex gap-3 mb-4 flex-wrap">
        <div>
            <select id="employmentFilter" class="form-select filter-pill filter-dropdown" aria-label="Status" onchange="filterStaff()">
                <option value="All">Status</option>
                <option value="Active">Active</option>
                <option value="On Leave">On Leave</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
    </div>
        <div class="row" id="staffGrid">
            <?php foreach ($staffRecords as $staff): ?>
                <?php
                $profileImage = $staff['hasImage']
                    ? 'staff-image.php?id=' . (int) $staff['staffID'] : '';
                ?>
                <div class="col-md-6 col-lg-4 mb-4 staff-record" id="staff-<?php echo (int) $staff['staffID']; ?>"
                     data-username="<?php echo staffText($staff['userName'] ?? ''); ?>"
                     data-first-name="<?php echo staffText($staff['firstName']); ?>"
                     data-last-name="<?php echo staffText($staff['lastName']); ?>"
                     data-type="<?php echo staffText($staff['staffType']); ?>"
                     data-address="<?php echo staffText($staff['address']); ?>"
                     data-image="<?php echo staffText($profileImage); ?>"
                     data-contact="<?php echo staffText($staff['contactNo']); ?>"
                     data-status="<?php echo staffText($staff['employmentStatus']); ?>"
                     data-joined="<?php echo staffText($staff['dateJoined']); ?>"
                     data-separated="<?php echo staffText($staff['dateSeparated']); ?>"
                >
                    <div class="staff-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="staff-avatar-placeholder">
                        <?php if ($profileImage != ''): ?>
                            <img src="<?php echo staffText($profileImage); ?>" class="staff-profile-image rounded-circle object-fit-cover" width="60" height="60" alt="<?php echo staffText($staff['firstName'] . ' ' . $staff['lastName']); ?> profile picture">
                        <?php endif; ?>
                    </div>
                                <div>
                                <h5 class="fw-bold mb-0"><?php echo staffText($staff['firstName'] . ' ' . $staff['lastName']); ?></h5>
                                <div class="staff-role text-muted"><?php echo staffText($staff['staffType']); ?></div>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark">#<?php echo (int) $staff['staffID']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <div class="field-label">Contact Number</div><div class="field-value"><?php echo staffText($staff['contactNo']); ?></div>
                            </div>
                            <?php
                            $statusClass = 'status-inactive';
                            if ($staff['employmentStatus'] == 'Active') {
                                $statusClass = 'status-active';
                            } else if ($staff['employmentStatus'] == 'On Leave') {
                                $statusClass = 'status-on-leave';
                            }
                            ?>
                            <span class="staff-employment-status <?php echo $statusClass; ?>"><?php echo staffText($staff['employmentStatus']); ?></span>
                        </div>
                        
                        <!-- Actions Row: Available to logged-in Admin and Staff users -->
                        <?php if ($canManageStaff): ?>
                            <div class="border-top pt-3 d-flex justify-content-end gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-dark px-3 rounded-pill" onclick="viewStaff(<?php echo (int) $staff['staffID']; ?>)">VIEW</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary px-3 rounded-pill" onclick="editStaff(<?php echo (int) $staff['staffID']; ?>)">EDIT</button>
                                <form method="POST" action="backend%20logic/staff_backend.php"  class="d-inline">
                                    <input type="hidden" name="action" value="delete"><input type="hidden" name="staffID" value="<?php echo (int) $staff['staffID']; ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger px-3 rounded-pill" onclick="confirmStaffDelete(<?php echo (int) $staff['staffID']; ?>)">DELETE</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div id="noStaffMessage" class="col-12 text-center text-muted py-5" <?php if (count($staffRecords) > 0) echo 'hidden'; ?>>No staff found.</div>
        </div>
    </div>
</div>
<?php include 'includes/modals/staff_modal.php'; ?>
<script src="javascript/staff.js?v=<?php echo filemtime(__DIR__ . '/javascript/staff.js'); ?>"></script>
<?php include 'includes/footer.php'; ?>
