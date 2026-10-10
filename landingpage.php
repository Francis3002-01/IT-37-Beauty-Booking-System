<?php
$pageTitle = 'Welcome - beautyReserve';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <button type="button" class="role-card w-100 text-start border-0 bg-white" 
                            data-bs-toggle="modal" 
                            data-bs-target="#loginModal" 
                            data-action="AdminLogin.php" 
                            data-role-title="Administrator Portal" 
                            data-role-badge="Management" 
                            data-icon="icons/admin.png">
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
                    <button type="button" class="role-card w-100 text-start border-0 bg-white" 
                            data-bs-toggle="modal" 
                            data-bs-target="#loginModal" 
                            data-action="StaffMemberLogin.php" 
                            data-role-title="Staff Portal" 
                            data-role-badge="Specialist" 
                            data-icon="icons/staff member.png">
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

    <!-- Shared Dynamic Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal-content border-0 shadow-lg">
                
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-badge modal-icon-badge">
                            <img id="modalRoleIcon" src="icons/admin.png" alt="" class="role-icon">
                        </div>
                        <div>
                            <span id="modalRoleBadge" class="role-badge mb-1 d-inline-block">Management</span>
                            <h5 class="modal-title font-weight-bold" id="loginModalLabel">Administrator Portal</h5>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-4">
                    <form id="loginForm" method="POST" action="AdminLogin.php" autocomplete="off">
                        <div class="mb-3">
                            <label for="usernameInput" class="form-label text-secondary fw-semibold fs-7">Username</label>
                            <input type="text" name="username" class="form-control custom-input" id="usernameInput" placeholder="Enter username" required>
                        </div>

                        <div class="mb-3">
                            <label for="passwordInput" class="form-label text-secondary fw-semibold fs-7">Password</label>
                            <input type="password" name="password" class="form-control custom-input" id="passwordInput" placeholder="Enter password" required>
                        </div>

                        <!-- Combined Action Buttons with tight gap -->
                        <div class="d-flex flex-column gap-2 mt-4">
                            <button type="submit" class="btn btn-dark w-100 py-2.5 fw-semibold custom-login-btn">
                                Sign In
                            </button>
                            <button type="button" class="btn btn-outline-danger w-100 py-2.5 fw-semibold custom-cancel-btn" data-bs-dismiss="modal">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const loginModal = document.getElementById('loginModal');
        const loginForm = document.getElementById('loginForm');

        loginModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const title = button.getAttribute('data-role-title');
            const badge = button.getAttribute('data-role-badge');
            const icon = button.getAttribute('data-icon');

            // Dynamically set action URL to AdminLogin.php or StaffMemberLogin.php
            loginForm.action = action;
            document.getElementById('loginModalLabel').textContent = title;
            document.getElementById('modalRoleBadge').textContent = badge;
            document.getElementById('modalRoleIcon').src = icon;
            loginForm.reset();
        });
    </script>
</body>
</html>