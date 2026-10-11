<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

$error = $_SESSION['admin_login_error'] ?? '';
unset($_SESSION['admin_login_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        !is_string($username) || !is_string($password) ||
        $username === '' || $password === ''
    ) {

        $error = 'Please enter your username and password.';
    } else {
        // Prepared statement prevents SQL injection.
        $sql = "SELECT userID, userName, passwordHash, role, accountStatus
                FROM user
                WHERE userName = ?
                  AND role = 'Admin'
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('s', $username);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user && $user['accountStatus'] === 'Active' && is_string($user['passwordHash']) && password_verify($password, $user['passwordHash'])) {
                    // Prevent session fixation after successful login.
                    session_regenerate_id(true);

                    $_SESSION['userID'] = $user['userID'];
                    $_SESSION['user_role'] = 'admin';
                    $_SESSION['username'] = $user['userName'];

                    $stmt->close();

                    header('Location: ../../appointments.php');
                    exit;
                }
            }

            $stmt->close();
        }

        // Save a generic error message and return to the landing page.
        $_SESSION['admin_login_error'] =
            'Your username or password is incorrect.';

        header('Location: ../../landingpage.php?login=admin');
        exit;
    }
}
?>

<!-- Main Translucent Container -->
<div class="modal fade" id="adminLoginModal" tabindex="-1" aria-labelledby="adminLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="adminLoginModalLabel">
                    Administrator Login
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <p class="text-muted mb-4">
                    Sign in to manage schedules and system settings.
                </p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="includes/modals/AdminLogin.php" autocomplete="off">

                    <div class="mb-3">
                        <label for="adminUsername" class="form-label">Username</label>
                        <input
                            type="text"
                            name="username"
                            id="adminUsername"
                            class="form-control"
                            placeholder="Enter admin username"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Password</label>

                        <input
                            type="password"
                            name="password"
                            id="adminPassword"
                            class="form-control"
                            placeholder="Enter password"
                            autocomplete="off"
                            required>

                    </div>

                    <button type="submit" class="btn btn-dark w-100 py-2 fw-semibold custom-login-btn mb-3">
                        Sign In
                    </button>

                    <div class="text-center">
                        <button type="button"
                            class="btn btn-outline-secondary w-100 py-2 fw-semibold custom-cancel-btn"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>