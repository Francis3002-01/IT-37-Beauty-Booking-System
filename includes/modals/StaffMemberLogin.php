<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Hardcoded Staff Credentials
define('STAFF_USER', 'staff');
define('STAFF_PASS', '123');

$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === STAFF_USER && $password === STAFF_PASS) {
        $_SESSION['user_role'] = 'staff';
        $_SESSION['username']  = $username;
        header('Location: appointments.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>

<!-- Staff Login Modal -->
<div class="modal fade" id="staffLoginModal" tabindex="-1"
    aria-labelledby="staffLoginModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <section class="modal-content">

            <header class="modal-header">
                <h2 class="modal-title fs-5" id="staffLoginModalLabel">
                    Staff Portal Login
                </h2>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </header>

            <section class="modal-body">

                <?php if (!empty($error)): ?>
                    <aside class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </aside>
                <?php endif; ?>

                <form method="POST"
                    action="includes/modals/StaffMemberLogin.php"
                    autocomplete="off">

                    <div class="mb-3">
                        <label for="staffUsername" class="form-label">
                            Username
                        </label>

                        <input type="text"
                            name="username"
                            id="staffUsername"
                            class="form-control custom-input"
                            placeholder="Enter staff username"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="staffPassword" class="form-label">
                            Password
                        </label>

                        <input type="password"
                            name="password"
                            id="staffPassword"
                            class="form-control custom-input"
                            placeholder="Enter password"
                            required>
                    </div>

                    <footer>
                        <button type="submit"
                            class="btn btn-dark w-100 py-2 fw-semibold custom-login-btn mb-3">
                            Sign In
                        </button>

                        <button type="button"
                            class="btn btn-outline-secondary w-100 py-2 fw-semibold custom-cancel-btn"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </footer>

                </form>

            </section>

        </section>
    </div>
</div>