<?php
@session_start(); // Start the session

// Check login status
$isLoggedIn = isset($_SESSION['user_id']);
$displayName = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : "Guest"; // Default to "Guest"
$role = $isLoggedIn ? $_SESSION['role'] : null; // Get role if logged in
?>

<header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <!-- Logo Section -->
        <a href="/index.php" class="logo d-flex align-items-center me-auto">
            <i class="bi bi-camera"></i>
            <h1>Art Portfolio</h1>
        </a>

        <!-- Navigation Menu -->
        <nav id="navmenu" class="navmenu d-flex align-items-center">
            <?php if ($isLoggedIn): ?>
                <!-- Admin Menu -->
                <?php if ($role === 'admin'): ?>
                    <div class="dropdown me-3">
                        <a href="#" class="btn btn-danger dropdown-toggle" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Admin Menu
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item" href="/app/manage-users.php">Manage Users</a></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- User Menu -->
                <div class="dropdown">
                    <a href="#" class="btn dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $displayName ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="/app/edit-profile.php?id=<?= htmlspecialchars($_SESSION['user_id']) ?>">Edit Profile</a></li>
                        <li><a class="dropdown-item" href="/app/upload.php">Upload Image</a></li>
                        <li><a class="dropdown-item" href="/app/user.php?id=<?= htmlspecialchars($_SESSION['user_id']) ?>">User Page</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/auth/logout.php">Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- Guest Menu -->
                <div class="d-flex gap-2">
                    <a href="/auth/login.php" class="btn btn-outline-primary">Login</a>
                    <a href="/auth/register.php" class="btn btn-outline-success">Register</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>
