<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
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
    // Redirect to error page or profile with error message
    $_SESSION['error'] = "Database connection failed: " . $e->getMessage();
    header("Location: /app/error.php");
    exit;
}

// Validate image ID and check ownership or admin privileges
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid request. No image ID provided.";
    header("Location: /app/user.php?id=" . $_SESSION['user_id']);
    exit;
}

$imageId = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT user_id, image_link FROM Gallery WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch();

    if (!$image) {
        $_SESSION['error'] = "Image not found.";
        header("Location: /app/user.php?id=" . $_SESSION['user_id']);
        exit;
    }

    $isOwner = $image['user_id'] == $_SESSION['user_id'];
    $isAdmin = $_SESSION['role'] === 'admin';

    if (!$isOwner && !$isAdmin) {
        $_SESSION['error'] = "Unauthorized access.";
        header("Location: /app/user.php?id=" . $_SESSION['user_id']);
        exit;
    }

    // Delete the image file from the server
    if (file_exists($image['image_link'])) {
        unlink($image['image_link']);
    }

    // Delete the image record from the database
    $stmt = $pdo->prepare("DELETE FROM Gallery WHERE id = ?");
    $stmt->execute([$imageId]);

    // Redirect back to the user's profile page with success message
    $_SESSION['success'] = "Image deleted successfully.";
    header("Location: /app/user.php?id=" . $image['user_id']);
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting image: " . $e->getMessage();
    header("Location: /app/user.php?id=" . $_SESSION['user_id']);
    exit;
}
?>