<?php
/* TAB VIEW FOR BEAUTICIANS
 * ------------------------------------------------------------------
 * Main tab for managing salon beauticians.
 * Acts as the visual shell. It relies on PHP includes for layout components (Header, Sidebar, Modals, Footer) and JavaScript for dynamic content rendering.
*/

$pageTitle = 'Beauticians - beautyReserve';

//config db
require_once __DIR__ . '/config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(); //start session if not already started
}
$message = $_SESSION['beautician_message'] ?? '';
$status = $_SESSION['beautician_status'] ?? 'error';
unset($_SESSION['beautician_message'], $_SESSION['beautician_status']);

//retrieve beautician records from database
$result = $conn->query('SELECT beauticianID, beauticianType, lastName, firstName, address, contactNo, employmentStatus, dateJoined, dateSeparated FROM beauticians ORDER BY beauticianID');
$beauticians = [];
while ($beautician = $result->fetch_assoc()) {
    $beauticians[] = $beautician;
}

$result->free();
$conn->close();

function beauticianText($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES); //type errorr guard. htmlspecialchars convert, en_quotes converts '"
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo beauticianText($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/beauticians.css?v=<?php echo filemtime(__DIR__ . '/css/beauticians.css'); ?>" rel="stylesheet">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <div class="dashboard-card">
        <h1 class="fw-bold mb-4" style="font-size:1.75rem;">Beauticians</h1>
        <?php if ($message != ''): ?>
            <div id="beauticianFeedback" class="alert alert-<?php echo $status === 'success' ? 'success' : 'danger'; ?>" role="alert"><?php echo beauticianText($message); ?></div>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <input type="text" id="searchInput" class="search-pill" placeholder="Search beauticians..." oninput="filterBeauticians()">
            <button type="button" class="btn btn-create" onclick="openCreateModal()">Add New Beautician</button>
        </div>
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <?php foreach (['All', 'Hair Stylist', 'Makeup Artist', 'Nail Artist', 'Esthetician'] as $type): ?>
                <button type="button" class="filter-pill <?php if ($type == 'All') echo 'active'; ?>" onclick="setFilter(this.textContent.trim(), this)"><?php echo beauticianText($type); ?></button>
            <?php endforeach; ?>
        </div>
        <div class="row" id="beauticiansGrid">
            <?php foreach ($beauticians as $beautician): ?>
                <div class="col-md-6 col-lg-4 mb-4 beautician-record" id="beautician-<?php echo (int) $beautician['beauticianID']; ?>"
                     data-first-name="<?php echo beauticianText($beautician['firstName']); ?>"
                     data-last-name="<?php echo beauticianText($beautician['lastName']); ?>"
                     data-type="<?php echo beauticianText($beautician['beauticianType']); ?>"
                     data-address="<?php echo beauticianText($beautician['address']); ?>"
                     data-contact="<?php echo beauticianText($beautician['contactNo']); ?>"
                     data-status="<?php echo beauticianText($beautician['employmentStatus']); ?>"
                     data-joined="<?php echo beauticianText($beautician['dateJoined']); ?>"
                     data-separated="<?php echo beauticianText($beautician['dateSeparated']); ?>"
                >
                    <div class="beautician-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div><h5 class="fw-bold mb-1"><?php echo beauticianText($beautician['firstName'] . ' ' . $beautician['lastName']); ?></h5></div>
                            <span class="badge bg-light text-dark">#<?php echo (int) $beautician['beauticianID']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                            <div><div class="field-label">Specialization</div><div class="field-value"><?php echo beauticianText($beautician['beauticianType']); ?></div></div>
                            <?php
                            $statusClass = 'status-inactive';
                            if ($beautician['employmentStatus'] == 'Active') {
                                $statusClass = 'status-active';
                            } else if ($beautician['employmentStatus'] == 'On Leave') {
                                $statusClass = 'status-on-leave';
                            }
                            ?>
                            <span class="beautician-employment-status <?php echo $statusClass; ?>"><?php echo beauticianText($beautician['employmentStatus']); ?></span>
                        </div>
                        <div class="mb-3">
                            <div class="field-label">Contact Number</div><div class="field-value"><?php echo beauticianText($beautician['contactNo']); ?></div>
                        </div>
                        <div class="border-top pt-3 d-flex justify-content-end gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-dark px-3 rounded-pill" onclick="viewBeautician(<?php echo (int) $beautician['beauticianID']; ?>)">VIEW</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3 rounded-pill" onclick="editBeautician(<?php echo (int) $beautician['beauticianID']; ?>)">EDIT</button>
                            <form method="POST" action="backend%20logic/beauticians_backend.php" onsubmit="return confirmBeauticianDelete()" class="d-inline">
                                <input type="hidden" name="action" value="delete"><input type="hidden" name="beauticianID" value="<?php echo (int) $beautician['beauticianID']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger px-3 rounded-pill">DELETE</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div id="noBeauticiansMessage" class="col-12 text-center text-muted py-5" <?php if (count($beauticians) > 0) echo 'hidden'; ?>>No beauticians found.</div>
        </div>
    </div>
</div>
<?php include 'includes/modals/beauticians_modal.php'; ?>
<script src="javascript/beauticians.js?v=<?php echo filemtime(__DIR__ . '/javascript/beauticians.js'); ?>"></script>
<?php include 'includes/footer.php'; ?>
