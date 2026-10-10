<?php
/**
 * MAIN PROFILE SETTINGS VIEW (`profile-settings.php`)
 * ------------------------------------------------------------------
 * Purpose: Manages admin profile information and password updates using session state.
 * Architecture: 
 * - Uses PHP `$_SESSION` to persist and sync data across the sidebar and profile form.
 * - Fully database-ready: session arrays and mock updates can later be replaced with SQL queries.
 */

session_start();

// 1. Initialize session defaults if they don't exist yet (matches sidebar fallbacks)
if (!isset($_SESSION['user_name'])) {$_SESSION['user_name'] = 'John Francis';
}
if (!isset($_SESSION['user_role'])) {$_SESSION['user_role'] = 'Administrator';
}
if (!isset($_SESSION['user_email'])) {$_SESSION['user_email'] = 'johnfrancis@beautyreserve.com';
}
// Mock stored password hash for prototyping (Default: 'password123')
if (!isset($_SESSION['user_password'])) {$_SESSION['user_password'] = password_hash('password123', PASSWORD_DEFAULT);
}

$updateMessage = "";
$messageType = "success"; // success or danger

// 2. Handle form submission (Mock Mode / Session State Sync)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // FUTURE DB MAP: UPDATE users SET full_name = ?, email = ? WHERE id = ?
        $_SESSION['user_name']  = trim($_POST['full_name']);$_SESSION['user_email'] = trim($_POST['email']);$updateMessage = "Profile details updated successfully! (Sidebar and session are now synced)";
        $messageType = "success";
    } 
    elseif (isset($_POST['update_password'])) {
        $currentPassword =$_POST['current_password'];
        $newPassword     =$_POST['new_password'];
        $confirmPassword =$_POST['confirm_password'];

        // FUTURE DB MAP: Verify password from database row using password_verify()
        if (!password_verify($currentPassword, $_SESSION['user_password'])) {$updateMessage = "Incorrect current password entered.";
            $messageType = "danger";
        } elseif ($newPassword !== $confirmPassword) {$updateMessage = "New passwords do not match.";
            $messageType = "danger";
        } elseif (strlen($newPassword) < 8) {$updateMessage = "New password must be at least 8 characters long.";
            $messageType = "danger";
        } else {
            // FUTURE DB MAP: UPDATE users SET password_hash = ? WHERE id = ?
            $_SESSION['user_password'] = password_hash($newPassword, PASSWORD_DEFAULT);$updateMessage = "Password changed successfully! (Mock Mode)";
            $messageType = "success";
        }
    }
}

$pageTitle = "Profile Settings - beautyReserve";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Page Stylesheet (Includes sidebar and layout styles) -->
    <link href="css/profile-settings.css" rel="stylesheet">
</head>
<body>

<?php 
// Include sidebar inside <body> so HTML renders in valid order
include 'includes/sidebar.php'; 
?>

<!-- Main Content Area -->
<div class="main-content">
    <div class="dashboard-card">
        <h1 class="fw-bold mb-4" style="font-size: 1.75rem;">Profile Settings</h1>

        <!-- Feedback Alert -->
        <?php if (!empty($updateMessage)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show mb-4" role="alert">
                <?php echo htmlspecialchars($updateMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Responsive Two-Column Grid to fill screen width gracefully -->
        <div class="row g-4">
            
            <!-- Column 1: General Profile Information -->
            <div class="col-lg-6">
                <div class="p-4 border rounded-4 bg-light h-100 d-flex flex-column justify-content-between">
                    <form method="POST" action="profile-settings.php">
                        <h5 class="fw-bold mb-3 text-dark" style="font-size: 1.15rem;">Personal Information</h5>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control rounded-3" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control rounded-3" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label fw-semibold">Access Role</label>
                            <!-- Disabled field because role shouldn't be edited via standard profile settings -->
                            <input type="text" class="form-control bg-white rounded-3" id="role" value="<?php echo htmlspecialchars($_SESSION['user_role']); ?>" disabled>
                            <div class="form-text text-muted">Role permissions are managed by system administrators.</div>
                        </div>

                        <input type="hidden" name="update_profile" value="1">
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-create">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Column 2: Change Password -->
            <div class="col-lg-6">
                <div class="p-4 border rounded-4 bg-light h-100 d-flex flex-column justify-content-between">
                    <form method="POST" action="profile-settings.php">
                        <h5 class="fw-bold mb-3 text-dark" style="font-size: 1.15rem;">Change Password</h5>

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">Current Password</label>
                            <input type="password" class="form-control rounded-3" id="current_password" name="current_password" required placeholder="Enter current password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold">New Password</label>
                            <input type="password" class="form-control rounded-3" id="new_password" name="new_password" required placeholder="At least 8 characters">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" class="form-control rounded-3" id="confirm_password" name="confirm_password" required placeholder="Re-enter new password">
                        </div>

                        <input type="hidden" name="update_password" value="1">
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-create">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'includes/footer.php'; ?>
</body>
</html>