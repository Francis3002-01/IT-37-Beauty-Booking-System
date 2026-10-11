<?php
/* TAB VIEW FOR CLIENTS
 * ------------------------------------------------------------------
 * Main tab for managing salon clients.
 * Acts as the visual shell. It relies on PHP includes for layout components (Header, Sidebar, Modals, Footer) and JavaScript for dynamic content rendering.
*/

$pageTitle = 'Clients - beautyReserve';

//config db
require_once __DIR__ . '/config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(); //start session if not already started
}
$message = $_SESSION['client_message'] ?? '';
$status = $_SESSION['client_status'] ?? 'error';
unset($_SESSION['client_message'], $_SESSION['client_status']);

//retrieve client records from database
$result = $conn->query('SELECT clientID, lastName, firstName, address, contactNo FROM client ORDER BY clientID');
$clients = [];
while ($client = $result->fetch_assoc()) {
    $clients[] = $client;
}

$result->free();
$conn->close();

function clientText($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES); //type errorr guard. htmlspecialchars convert, en_quotes converts '"
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo clientText($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/clients.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <div class="dashboard-card">
        <h1 class="fw-bold mb-4" style="font-size: 1.75rem;">Clients</h1>
        <?php if ($message != ''): ?>
            <div id="clientFeedback" class="alert alert-<?php echo $status === 'success' ? 'success' : 'danger'; ?>" role="alert"><?php echo clientText($message); ?></div>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <input type="text" id="searchInput" class="search-pill" placeholder="Search Clients..." oninput="filterClients()">
            <button type="button" class="btn btn-create" onclick="openCreateModal()">Add New Client</button>
        </div>
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <button type="button" class="filter-pill active" onclick="setFilter('All', this)">All</button>
            <button type="button" class="filter-pill" onclick="setFilter('Recent', this)">Recently Added</button>
            <button type="button" class="filter-pill" onclick="setFilter('AZ', this)">Name (A-Z)</button>
        </div>
        <div class="d-flex flex-column gap-3" id="clientsList">
            <?php foreach ($clients as $client): ?>
                <div class="client-row-card client-record" id="client-<?php echo (int) $client['clientID']; ?>"
                     data-id="<?php echo (int) $client['clientID']; ?>"
                     data-first-name="<?php echo clientText($client['firstName']); ?>"
                     data-last-name="<?php echo clientText($client['lastName']); ?>"
                     data-address="<?php echo clientText($client['address']); ?>"
                     data-contact="<?php echo clientText($client['contactNo']); ?>">
                    <div class="client-avatar-placeholder"></div>
                    <div class="client-info-wrapper">
                        <div class="client-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <h3 class="client-name"><?php echo clientText($client['firstName'] . ' ' . $client['lastName']); ?></h3>
                                <span class="badge bg-light text-dark">#<?php echo (int) $client['clientID']; ?></span>
                            </div>
                            <div class="client-actions">
                                <button type="button" class="btn-card-action" onclick="editClient(<?php echo (int) $client['clientID']; ?>)">Edit</button>
                                <form method="POST" action="backend%20logic/clients_backend.php" onsubmit="return confirmClientDelete()" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="clientID" value="<?php echo (int) $client['clientID']; ?>">
                                    <button type="submit" class="btn-card-action btn-delete">Delete</button>
                                </form>
                            </div>
                        </div>
                        <div class="client-details-grid">
                            <div class="client-detail-item"><span class="client-detail-label">Address</span><span class="client-detail-value"><?php echo clientText($client['address']); ?></span></div>
                            <div class="client-detail-item"><span class="client-detail-label">Contact Number</span><span class="client-detail-value"><?php echo clientText($client['contactNo']); ?></span></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div id="noClientsMessage" class="text-center text-muted py-5" <?php if (count($clients) > 0) echo 'hidden'; ?>>No clients found.</div>
    </div>
</div>
<?php include 'includes/modals/clients_modal.php'; ?>
<script src="javascript/clients.js?v=<?php echo filemtime(__DIR__ . '/javascript/clients.js'); ?>"></script>
<?php include 'includes/footer.php'; ?>
