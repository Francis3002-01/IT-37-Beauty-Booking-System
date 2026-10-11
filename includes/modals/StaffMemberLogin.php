<?php
session_start();

require_once __DIR__ . '/../../config/database.php';

$error =$_SESSION['staff_login_error'] ?? '';
unset($_SESSION['staff_login_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password =$_POST['password'] ?? '';

    if (
        !is_string($username) OR !is_string($password) OR
        $username === '' OR$password === ''
    ) {
        $error = 'Please enter your username and password.';
    } else {
        // Fetch staff user record from database
        $sql = "SELECT userID, staffID, userName, passwordHash, role, accountStatus
                FROM `user`
                WHERE userName = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('s',$username);

            if ($stmt->execute()) {
                $result =$stmt->get_result();
                $user =$result->fetch_assoc();

                if ($user && $user['accountStatus'] === 'Active' && is_string($user['passwordHash']) && password_verify($password,$user['passwordHash'])) {
                    // Prevent session fixation after successful login
                    session_regenerate_id(true);

                    $_SESSION['userID']    =$user['userID'];
                    $_SESSION['staffID']   =$user['staffID'];
                    $_SESSION['user_role'] = strtolower($user['role']);
                    $_SESSION['username']  =$user['userName'];

                    $stmt->close();

                    header('Location: ../../appointments.php');
                    exit;
                }
            }

            $stmt->close();
        }

        // Store error message and redirect back to landingpage modal
        $_SESSION['staff_login_error'] = 'Invalid username or password.';

        header('Location: ../../landingpage.php?login=staff');
        exit;
    }
}
?>
<<<<<<< HEAD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - beautyReserve</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/landingpage.css" rel="stylesheet">
</head>
<body>
    <main class="welcome-container">
        <!-- Main Translucent Container -->
        <div class="main-card login-card-wrapper">
            
            <!-- Header Section -->
            <header class="welcome-header text-center mb-4">
                <div class="icon-badge mx-auto mb-3">
                    <img src="icons/staff member.png" alt="" class="role-icon" aria-hidden="true">
                </div>
                <span class="role-badge mb-2 d-inline-block">Specialist</span>
                <h1 class="welcome-title fs-2">Staff Portal Login</h1>
                <p class="welcome-copy mb-0">Sign in to view your schedule and appointments.</p>
=======

<!-- Staff Login Modal -->
<div class="modal fade" id="staffLoginModal" tabindex="-1" aria-labelledby="staffLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <section class="modal-content">

            <header class="modal-header">
                <h2 class="modal-title fs-5" id="staffLoginModalLabel">
                    Staff Portal Login
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
>>>>>>> 3414e15 (Login Functionality, and Appointment functionality)
            </header>

            <!-- Error Message Alert -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 px-3 text-center fs-7 mb-4" role="alert">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

<<<<<<< HEAD
            <!-- Login Form -->
            <form method="POST" action="StaffMemberLogin.php" autocomplete="off">
                <div class="mb-3">
                    <label for="username" class="form-label text-secondary fw-semibold fs-7">Username</label>
                    <input type="text" name="username" id="username" class="form-control custom-input" placeholder="Enter staff username" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label text-secondary fw-semibold fs-7">Password</label>
                    <input type="password" name="password" id="password" class="form-control custom-input" placeholder="Enter password" required>
                </div>

                <button type="submit" class="btn btn-dark w-100 py-2.5 fw-semibold custom-login-btn mb-3">
                    Sign In
                </button>

                <div class="text-center">
                    <a href="index.php" class="text-decoration-none text-muted fs-7">&larr; Back to Role Selection</a>
                </div>
            </form>

        </div>
    </main>
</body>
</html>
=======
                <p class="text-muted mb-4">
                    Sign in to view assigned appointments and schedules.
                </p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="includes/modals/StaffMemberLogin.php" autocomplete="off">

                    <div class="mb-3">
                        <label for="staffUsername" class="form-label">Username</label>
                        <input
                            type="text"
                            name="username"
                            id="staffUsername"
                            class="form-control"
                            placeholder="Enter staff username"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="staffPassword" class="form-label">Password</label>
                        <input
                            type="password"
                            name="password"
                            id="staffPassword"
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

            </section>

        </section>
    </div>
</div>
>>>>>>> 3414e15 (Login Functionality, and Appointment functionality)
