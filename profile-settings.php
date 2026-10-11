<?php

session_start();


require_once 'config/database.php';

$updateMessage = $_SESSION['profile_message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'success';

unset($_SESSION['profile_message'], $_SESSION['message_type']);

// Get the logged-in username from the existing login session.
$loggedInUsername = $_SESSION['username']
    ?? $_SESSION['user_name']
    ?? null;

// Ensure a user is logged in.
if (empty($loggedInUsername)) {
    header("Location: admin_login.php");
    exit;
}

// Load the logged-in user's account from the database.
$stmt = $conn->prepare(
    "SELECT userID, userName, passwordHash, role
    FROM `user`
    WHERE userName = ?
    LIMIT 1"
);

$stmt->bind_param("s", $loggedInUsername);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

// Stop if the account cannot be found.
if (!$user) {
    session_unset();
    session_destroy();

    header("Location: admin_login.php");
    exit;
}

// Synchronize session values with the database.
$_SESSION['username'] = $user['userName'];
$_SESSION['user_name'] = $user['userName'];
$_SESSION['user_role'] = $user['role'];

// Handle form submissions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Update username.
    if (isset($_POST['update_profile'])) {

        $newUsername = trim($_POST['username'] ?? '');

        if ($newUsername === '') {

            $updateMessage = "Username cannot be empty.";
            $messageType = "danger";
        } else {

            // Check whether another account already uses this username.
            $stmt = $conn->prepare(
                "SELECT userID
                FROM `user`
                WHERE userName = ? AND userID != ?
                LIMIT 1"
            );

            $stmt->bind_param("si", $newUsername, $user['userID']);
            $stmt->execute();

            $duplicate = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($duplicate) {

                $updateMessage = "That username is already taken.";
                $messageType = "danger";
            } else {

                // Save the new username.
                $stmt = $conn->prepare(
                    "UPDATE `user`
                    SET userName = ?
                    WHERE userID = ?"
                );

                $stmt->bind_param("si", $newUsername, $user['userID']);

                if ($stmt->execute()) {

                    // Synchronize the session and local account data.
                    $user['userName'] = $newUsername;
                    $_SESSION['username'] = $newUsername;
                    $_SESSION['user_name'] = $newUsername;

                    $updateMessage = "Username updated successfully!";
                    $messageType = "success";
                } else {

                    $updateMessage = "Unable to update your username.";
                    $messageType = "danger";
                }

                $stmt->close();
            }
        }

        // Update password.
    } elseif (isset($_POST['update_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Verify the current password against the stored hash.
        if (!password_verify($currentPassword, $user['passwordHash'])) {
            $updateMessage = "Incorrect current password entered.";
            $messageType = "danger";
        } 
        
        elseif ($newPassword !== $confirmPassword) {
            $updateMessage = "New passwords do not match.";
            $messageType = "danger";
        } 
        
        elseif (strlen($newPassword) < 8) {
            $updateMessage = "New password must be at least 8 characters long.";
            $messageType = "danger";
        } 
        
        else {
            // Hash the new password before saving it.
            $newPasswordHash = password_hash($newPassword,PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "UPDATE `user`
                SET passwordHash = ?
                WHERE userID = ?"
            );

            $stmt->bind_param(
                "si",
                $newPasswordHash,
                $user['userID']
            );

            if ($stmt->execute()) {
                $updateMessage = "Password changed successfully!";
                $messageType = "success";
            } 
            
            else {
                $updateMessage = "Unable to update your password.";
                $messageType = "danger";
            }

            $stmt->close();
        }
    }

    // Preserve the feedback message across the redirect.
    $_SESSION['profile_message'] = $updateMessage;
    $_SESSION['message_type'] = $messageType;

    // Redirect to prevent form resubmission on refresh.
    header("Location: profile-settings.php");
    exit;
}

$pageTitle = "Profile Settings - beautyReserve";
?>

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

    <!-- Custom Page Stylesheet (Includes sidebar and layout styles) -->
    <link href="css/profile-settings.css" rel="stylesheet">
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <section class="dashboard-card">

            <header>
                <h1 class="fw-bold mb-4" style="font-size: 1.75rem;">Profile Settings</h1>
            </header>

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
                    <section class="p-4 border rounded-4 bg-light h-100 d-flex flex-column justify-content-between">
                        <form method="POST" action="profile-settings.php">
                            <h2 class="fw-bold mb-3 text-dark" style="font-size: 1.15rem;">Personal Information</h2>

                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Username</label>
                                <input type="text" class="form-control rounded-3" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label fw-semibold">Access Role</label>
                                <!-- Disabled field because role shouldn't be edited via standard profile settings -->
                                <input type="text" class="form-control bg-white rounded-3" id="role" value="<?php echo htmlspecialchars($_SESSION['user_role']); ?>" disabled>
                            </div>

                            <input type="hidden" name="update_profile" value="1">
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-create">Save Changes</button>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Column 2: Change Password -->
                <div class="col-lg-6">
                    <section class="p-4 border rounded-4 bg-light h-100 d-flex flex-column justify-content-between">
                        <form method="POST" action="profile-settings.php">
                            <h2 class="fw-bold mb-3 text-dark" style="font-size: 1.15rem;">Change Password</h2>

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold">Current Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control rounded-start-3" id="current_password" name="current_password" required placeholder="Enter current password">
                                    <button class="btn btn-outline-secondary rounded-end-3" type="button" data-password-toggle="current_password" aria-label="Show current password" aria-pressed="false">
                                        <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label fw-semibold">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control rounded-start-3" id="new_password" name="new_password" required placeholder="At least 8 characters">
                                    <button class="btn btn-outline-secondary rounded-end-3" type="button" data-password-toggle="new_password" aria-label="Show new password" aria-pressed="false">
                                        <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control rounded-start-3" id="confirm_password" name="confirm_password" required placeholder="Re-enter new password">
                                    <button class="btn btn-outline-secondary rounded-end-3" type="button" data-password-toggle="confirm_password" aria-label="Show confirm new password" aria-pressed="false">
                                        <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                                    </button>
                                </div>

                                <div id="password_match_message" class="text-danger small mt-1" role="alert" style="display: none;">
                                    Passwords do not match.
                                </div>
                            </div>


                            <input type="hidden" name="update_password" value="1">
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-create">Update Password</button>
                            </div>
                        </form>
                    </section>
                </div>

            </div>
        </section>
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(function(button) {
            button.addEventListener('click', function() {
                const input = document.getElementById(button.dataset.passwordToggle);
                const icon = button.querySelector('i');
                const isHidden = input.type === 'password';

                input.type = isHidden ? 'text' : 'password';

                icon.classList.toggle('fa-eye', isHidden);
                icon.classList.toggle('fa-eye-slash', !isHidden);

                const fieldName = {
                    current_password: 'current password',
                    new_password: 'new password',
                    confirm_password: 'confirm new password'
                } [button.dataset.passwordToggle];

                button.setAttribute(
                    'aria-label',
                    (isHidden ? 'Hide ' : 'Show ') + fieldName
                );

                button.setAttribute('aria-pressed', String(isHidden));
            });
        });

        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordMatchMessage = document.getElementById('password_match_message');

        function checkPasswordMatch() {
            const newValue = newPassword.value;
            const confirmValue = confirmPassword.value;

            // Reset the styling if the confirmation is empty.
            if (confirmValue === '') {
                newPassword.classList.remove('is-invalid', 'is-valid');
                confirmPassword.classList.remove('is-invalid', 'is-valid');
                passwordMatchMessage.style.display = 'none';
                return;
            }

            // Turn both fields red when the passwords do not match.
            if (newValue !== confirmValue) {
                newPassword.classList.add('is-invalid');
                newPassword.classList.remove('is-valid');

                confirmPassword.classList.add('is-invalid');
                confirmPassword.classList.remove('is-valid');

                passwordMatchMessage.textContent = 'Passwords do not match.';
                passwordMatchMessage.style.display = 'block';
            } 
            
            else {
                // Turn both fields green when the passwords match.
                newPassword.classList.remove('is-invalid');
                newPassword.classList.add('is-valid');

                confirmPassword.classList.remove('is-invalid');
                confirmPassword.classList.add('is-valid');

                passwordMatchMessage.style.display = 'none';
            }
        }


        newPassword.addEventListener('input', checkPasswordMatch);
        confirmPassword.addEventListener('input', checkPasswordMatch);
    </script>

</body>

</html>