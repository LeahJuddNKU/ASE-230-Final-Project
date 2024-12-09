<?php
session_start();

// Redirect if no error set
if (!isset($_SESSION['error'])) {
    header("Location: /index.php");
    exit;
}

// Get and clear error message
$errorMessage = $_SESSION['error'];
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link href="/theme/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/theme/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/theme/css/main.css" rel="stylesheet">
    <style>
        .error-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }

        .btn-home {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php include '../lib/header.php'; ?>

    <main class="container">
        <div class="error-container">
            <h1 class="text-danger">An Error Occurred</h1>
            <p class="text-muted"><?= htmlspecialchars($errorMessage) ?></p>
            <a href="/index.php" class="btn btn-primary btn-home">
                <i class="bi bi-house-door"></i> Return to Home
            </a>
        </div>
    </main>

    <?php include '../lib/footer.php'; ?>
    <script src="/theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>