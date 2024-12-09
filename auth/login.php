<?php
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
    $message = "Error connecting to the database.";
}

// Initialize message
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: ../index.php");
            exit;
        } else {
            $message = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        $message = "An error occurred. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="../theme/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../theme/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../theme/css/main.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 400px;
            margin: auto;
            margin-top: 50px;
        }

        .btn-primary {
            margin-top: 10px;
        }

        .error-message {
            font-size: 0.9rem;
            color: red;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center">Login</h2>

            <!-- Registration Button -->
            <button onclick="location.href='./register.php'" type="button" class="btn btn-secondary w-100">
                Need an account? Register here!
            </button>

            <!-- Display Error Message -->
            <?php if ($message): ?>
                <p class="error-message text-center"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>

    <script src="../theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>