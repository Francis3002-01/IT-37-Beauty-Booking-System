<?php
session_start();

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
            </header>

            <!-- Error Message Alert -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 px-3 text-center fs-7 mb-4" role="alert">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

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