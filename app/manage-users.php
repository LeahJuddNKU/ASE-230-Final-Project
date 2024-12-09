<?php
session_start();

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /auth/login.php");
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'art_portfolio';
$user = 'root';
$password = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    // Redirect on connection error
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: /app/error.php");
    exit;
}

// Fetch users
try {
    $stmt = $pdo->query("SELECT user_id, username, display_name, email, role FROM Users ORDER BY user_id ASC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching users.";
    header("Location: /app/error.php");
    exit;
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $deleteUserId = $_POST['delete_user_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = ?");
        $stmt->execute([$deleteUserId]);
        $_SESSION['success'] = "User deleted.";
        header("Location: /admin/manage-users.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting user.";
        header("Location: /admin/manage-users.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="/theme/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/theme/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/theme/css/main.css" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
        }

        .table thead th,
        .table tbody td {
            text-align: center;
        }

        .btn-sm {
            font-size: 0.9rem;
        }

        .card {
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }
    </style>
</head>

<body>
    <?php include '../lib/header.php'; ?>

    <main class="container mt-5">
        <div class="card">
            <h1 class="text-center mb-4">Manage Users</h1>

            <!-- Display success/error messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- User Table -->
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Display Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['display_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- View User -->
                                    <a href="/app/user.php?id=<?= htmlspecialchars($user['user_id']) ?>"
                                        class="btn btn-info btn-sm">
                                        <i class="bi bi-person-lines-fill"></i> View
                                    </a>
                                    <!-- Edit User -->
                                    <a href="/app/edit-profile.php?id=<?= htmlspecialchars($user['user_id']) ?>"
                                        class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </a>
                                    <!-- Delete User -->
                                    <form method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="delete_user_id"
                                            value="<?= htmlspecialchars($user['user_id']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash-fill"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include '../lib/footer.php'; ?>
    <script src="/theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>