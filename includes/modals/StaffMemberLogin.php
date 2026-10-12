<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

$error = $_SESSION['staff_login_error'] ?? '';
unset($_SESSION['staff_login_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!is_string($username) || !is_string($password) || $username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        // Prepared statement to look up the user in the database
        // Checking for role = 'Receptionist' (or adjust to match your staff role name)
        $sql = "SELECT userID, userName, passwordHash, role, accountStatus
                FROM user
                WHERE userName = ?
                  AND role = 'Receptionist'
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('s', $username);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if (
                    $user &&
                    $user['accountStatus'] === 'Active' &&
                    is_string($user['passwordHash']) &&
                    password_verify($password, $user['passwordHash'])
                ) {
                    // Prevent session fixation after successful login.
                    session_regenerate_id(true);

                    $_SESSION['userID'] = $user['userID'];
                    $_SESSION['user_role'] = 'staff';
                    $_SESSION['username'] = $user['userName'];

                    $stmt->close();
                    $conn->close();

                    header('Location: ../../appointments.php');
                    exit();
                }
            }

            $stmt->close();
        }

        $conn->close();

        // Save error message and return to the landing page modal view
        $_SESSION['staff_login_error'] = 'Your username or password is incorrect.';

        header('Location: ../../landingpage.php?login=staff');
        exit();
    }
}
?>

<!-- Staff Login Modal -->
<div
    class="modal fade"
    id="staffLoginModal"
    tabindex="-1"
    aria-labelledby="staffLoginModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <section class="modal-content">
            <header class="modal-header">
                <h2 class="modal-title fs-5" id="staffLoginModalLabel">Staff Portal Login</h2>
            </header>

            <section class="modal-body">
                <?php if (!empty($error)): ?>
                <aside class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </aside>
                <?php endif; ?>

                <form
                    method="POST"
                    action="includes/modals/StaffMemberLogin.php"
                    autocomplete="off"
                >
                    <div class="mb-3">
                        <label for="staffUsername" class="form-label"> Username </label>

                        <input
                            type="text"
                            name="username"
                            id="staffUsername"
                            class="form-control custom-input"
                            placeholder="Enter staff username"
                            required
                        />
                    </div>

                    <div class="mb-3">
                        <label for="staffPassword" class="form-label"> Password </label>

                        <input
                            type="password"
                            name="password"
                            id="staffPassword"
                            class="form-control custom-input"
                            placeholder="Enter password"
                            required
                        />
                    </div>

                    <footer>
                        <button
                            type="submit"
                            class="btn btn-dark w-100 py-2 fw-semibold custom-login-btn mb-3"
                        >
                            Sign In
                        </button>

                        <button
                            type="button"
                            class="btn btn-outline-secondary w-100 py-2 fw-semibold custom-cancel-btn"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>
                    </footer>
                </form>
            </section>
        </section>
    </div>
</div>
