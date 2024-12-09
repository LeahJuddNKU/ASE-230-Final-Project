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
    $username = htmlspecialchars(trim($_POST['username']));
    $display_name = htmlspecialchars(trim($_POST['display_name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    // Validate inputs
    if (!$email) {
        $message = "Invalid email format.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO Users (username, display_name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $display_name, $email, $hashedPassword]);
            $message = "Registration successful. You can now sign in.";
        } catch (PDOException $e) {
            $message = "Error registering user.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            <h2 class="text-center">Register</h2>

            <!-- Login Button -->
            <button onclick="location.href='./login.php'" type="button" class="btn btn-secondary w-100">
                Already have an account? Login
            </button>

            <!-- Display Error/Success Message -->
            <?php if ($message): ?>
                <p class="error-message text-center"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="display_name" class="form-label">Display Name</label>
                    <input type="text" name="display_name" id="display_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Register</button>
            </form>
        </div>
    </div>

    <script src="../theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>