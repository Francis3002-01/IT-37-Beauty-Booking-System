<?php

$pageTitle = 'Welcome - beautyReserve';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <link href="css/landingpage.css" rel="stylesheet">
</head>

<body>
    <main class="welcome-container">
        <!-- Main Covering Card -->
        <div class="main-card">
            <!-- Header Section -->
            <header class="welcome-header text-center">
                <span class="welcome-brand">beautyReserve</span>
                <h1 class="welcome-title">Welcome!</h1>
                <p class="welcome-copy">Select your designation to proceed to your dashboard.</p>
            </header>

            <!-- Role Cards Grid -->
            <div class="row g-4 role-grid">
                <!-- Admin Card -->
                <div class="col-md-6">

                    <button type="button"
                        class="role-card w-100 text-start border-0 bg-white"
                        data-bs-toggle="modal"
                        data-bs-target="#adminLoginModal">

                        <div class="role-card-header">

                            <div class="icon-badge">
                                <img src="icons/admin.png" alt="" class="role-icon" aria-hidden="true">
                            </div>
                            <span class="role-badge">Management</span>
                        </div>
                        <div class="role-card-body">
                            <h2 class="role-name">Administrator</h2>
                            <p class="role-desc">Manage schedules, staff performance, system settings, and analytics.</p>
                        </div>
                        <div class="role-card-footer">
                            <span class="action-text">Continue to Login</span>
                            <span class="role-arrow" aria-hidden="true">&rarr;</span>
                        </div>
                    </button>
                </div>

                <!-- Staff Card -->
                <div class="col-md-6">

                    <button type="button"
                        class="role-card w-100 text-start border-0 bg-white"
                        data-bs-toggle="modal"
                        data-bs-target="#staffLoginModal">

                        <div class="role-card-header">
                            <div class="icon-badge">
                                <img src="icons/staff member.png" alt="" class="role-icon" aria-hidden="true">
                            </div>
                            <span class="role-badge">Specialist</span>
                        </div>
                        <div class="role-card-body">
                            <h2 class="role-name">Staff Member</h2>
                            <p class="role-desc">View assigned appointments, client details, and daily work schedules.</p>
                        </div>
                        <div class="role-card-footer">
                            <span class="action-text">Continue to Login</span>
                            <span class="role-arrow" aria-hidden="true">&rarr;</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/modals/AdminLogin.php'; ?>
    <?php include __DIR__ . '/includes/modals/StaffMemberLogin.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const params = new URLSearchParams(window.location.search);
            const loginRole = params.get('login');

            if (loginRole === 'admin' || loginRole === 'staff') {
                const modalId = loginRole === 'admin' ?
                    'adminLoginModal' :
                    'staffLoginModal';

                const modalElement = document.getElementById(modalId);

                if (modalElement) {
                    bootstrap.Modal.getOrCreateInstance(modalElement).show();

                    // Clear the username and password fields.
                    if (loginRole === 'admin') {
                        document.getElementById('adminUsername').value = '';
                        document.getElementById('adminPassword').value = '';
                    }
                }

                // Remove the login parameter after opening the modal.
                params.delete('login');

                const newQuery = params.toString();
                const newUrl = window.location.pathname +
                    (newQuery ? '?' + newQuery : '') +
                    window.location.hash;

                window.history.replaceState({}, '', newUrl);
            }

            const togglePassword = document.getElementById('toggleAdminPassword');
            const passwordInput = document.getElementById('adminPassword');
            const passwordIcon = document.getElementById('adminPasswordIcon');

            if (togglePassword && passwordInput && passwordIcon) {
                togglePassword.addEventListener('click', function() {
                    const showPassword = passwordInput.type === 'password';

                    passwordInput.type = showPassword ? 'text' : 'password';
                    passwordIcon.classList.toggle('fa-eye', showPassword);
                    passwordIcon.classList.toggle('fa-eye-slash', !showPassword);

                    togglePassword.setAttribute(
                        'aria-label',
                        showPassword ? 'Hide password' : 'Show password'
                    );
                });
            }
        });
    </script>
</body>

</html>