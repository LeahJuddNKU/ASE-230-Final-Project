<?php
session_start();

// Check if logged in
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
    // Redirect on connection error
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: /app/error.php");
    exit;
}

// Validate user ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid user.";
    header("Location: /app/user.php?id=" . $_SESSION['user_id']);
    exit;
}
$profileUserId = $_GET['id'];

// Check access permissions
$isOwner = $_SESSION['user_id'] == $profileUserId;
$isAdmin = $_SESSION['role'] === 'admin';

if (!$isOwner && !$isAdmin) {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: /app/user.php?id=" . $_SESSION['user_id']);
    exit;
}

// Fetch user and contact details
try {
    $stmt = $pdo->prepare("SELECT display_name, bio FROM Users WHERE user_id = ?");
    $stmt->execute([$profileUserId]);
    $userDetails = $stmt->fetch();

    if (!$userDetails) {
        $_SESSION['error'] = "User not found.";
        header("Location: /app/user.php?id=" . $_SESSION['user_id']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT email, phone_number, website, other_info FROM Contact WHERE user_id = ?");
    $stmt->execute([$profileUserId]);
    $contactDetails = $stmt->fetch() ?: ['email' => '', 'phone_number' => '', 'website' => '', 'other_info' => ''];
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching details.";
    header("Location: /app/user.php?id=" . $_SESSION['user_id']);
    exit;
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $displayName = $_POST['display_name'];
    $bio = $_POST['bio'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phone_number'];
    $website = $_POST['website'];
    $otherInfo = $_POST['other_info'];

    try {
        // Update user details
        $stmt = $pdo->prepare("UPDATE Users SET display_name = ?, bio = ? WHERE user_id = ?");
        $stmt->execute([$displayName, $bio, $profileUserId]);

        // Update or insert contact details
        $stmt = $pdo->prepare(
            "INSERT INTO Contact (user_id, email, phone_number, website, other_info)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                email = VALUES(email), 
                phone_number = VALUES(phone_number), 
                website = VALUES(website), 
                other_info = VALUES(other_info)"
        );
        $stmt->execute([$profileUserId, $email, $phoneNumber, $website, $otherInfo]);

        // Redirect after success
        header("Location: /app/user.php?id=$profileUserId");
        exit;
    } catch (PDOException $e) {
        $message = "Error updating profile.";
    }
}
?>