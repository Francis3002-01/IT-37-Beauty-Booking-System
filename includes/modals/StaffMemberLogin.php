
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

// Retrieve any login error from the previous request.
$error = $_SESSION['staff_login_error'] ?? '';
unset($_SESSION['staff_login_error']);

// Process the login form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate input.
    if (
        !is_string($username) ||
        !is_string($password) ||
        $username === '' ||
        $password === ''
    ) {
        $_SESSION['staff_login_error'] =
            'Please enter your username and password.';

        header('Location: ../../landingpage.php?login=staff');
        exit;
    }

    // Find the account using the submitted username.
    $sql = "SELECT userID, staffID, userName, passwordHash,
                   role, accountStatus
            FROM `user`
            WHERE userName = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log('Staff login prepare error: ' . $conn->error);

        $_SESSION['staff_login_error'] =
            'Unable to process your login. Please try again later.';

        header('Location: ../../landingpage.php?login=staff');
        exit;
    }

    $stmt->bind_param('s', $username);

    if (!$stmt->execute()) {
        error_log('Staff login execute error: ' . $stmt->error);
        $stmt->close();

        $_SESSION['staff_login_error'] =
            'Unable to process your login. Please try again later.';

        header('Location: ../../landingpage.php?login=staff');
        exit;
    }

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify the credentials, role, and account status.
    if (
        $user &&
        isset($user['role']) &&
        strcasecmp($user['role'], 'Staff') === 0 &&
        $user['accountStatus'] === 'Active' &&
        is_string($user['passwordHash']) &&
        password_verify($password, $user['passwordHash'])
    ) {
        // Regenerate the session ID after successful authentication.
        session_regenerate_id(true);

        // Store authenticated user information in the session.
        $_SESSION['userID'] = $user['userID'];
        $_SESSION['staffID'] = $user['staffID'];
        $_SESSION['user_role'] = strtolower($user['role']);
        $_SESSION['username'] = $user['userName'];

        unset($_SESSION['staff_login_error']);

        // Redirect to the staff appointments page.
        header('Location: ../../appointments.php');
        exit;
    }

    // Invalid credentials or an inactive/non-staff account.
    $_SESSION['staff_login_error'] =
        'Invalid username or password.';

    header('Location: ../../landingpage.php?login=staff');
    exit;
}
?>

<!-- Staff Member Login Modal -->
<div class="modal fade"
     id="staffLoginModal"
     tabindex="-1"
     aria-labelledby="staffLoginModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="staffLoginModalLabel">
                    Staff Portal Login
                </h2>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <p class="text-secondary">
                    Sign in to view your schedule and appointments.
                </p>

                <!-- Login Form -->
                <form method="POST"
                      action="includes/modals/StaffMemberLogin.php"
                      autocomplete="off">

                    <div class="mb-3">
                        <label for="staffUsername"
                               class="form-label text-secondary fw-semibold">
                            Username
                        </label>

                        <input type="text"
                               name="username"
                               id="staffUsername"
                               class="form-control custom-input"
                               placeholder="Enter staff username"
                               autocomplete="username"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="staffPassword"
                               class="form-label text-secondary fw-semibold">
                            Password
                        </label>

                        <input type="password"
                               name="password"
                               id="staffPassword"
                               class="form-control custom-input"
                               placeholder="Enter password"
                               autocomplete="current-password"
                               required>
                    </div>

                    <!-- Login Error Message -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php
                            echo htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </div>
                    <?php endif; ?>

                    <!-- Sign In Button -->
                    <button type="submit"
                            class="btn btn-primary w-100 py-2 fw-semibold">
                        Sign In
                    </button>

                    <!-- Cancel Button -->
                    <button type="button"
                            class="btn btn-outline-secondary w-100 py-2 mt-2"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>
