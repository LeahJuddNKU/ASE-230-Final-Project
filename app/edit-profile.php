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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="../theme/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../theme/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../theme/css/main.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: auto;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .btn-success {
            width: 100%;
        }
    </style>
</head>

<body>
    <?php include '../lib/header.php'; ?>

    <main class="container mt-5">
        <div class="form-container">
            <!-- Dynamic Header -->
            <h1 class="form-header">
                <?= $isOwner ? "Edit Your Profile" : "Editing " . htmlspecialchars($userDetails['display_name']) . "'s Profile" ?>
            </h1>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST">
                <!-- User Information -->
                <div class="mb-3">
                    <label for="display_name" class="form-label">Display Name</label>
                    <input type="text" name="display_name" id="display_name" class="form-control"
                        value="<?= htmlspecialchars($userDetails['display_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea name="bio" id="bio" class="form-control"
                        rows="4"><?= htmlspecialchars($userDetails['bio']) ?></textarea>
                </div>

                <!-- Contact Information -->
                <h2 class="form-header">Contact Information</h2>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                        value="<?= htmlspecialchars($contactDetails['email']) ?>">
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" name="phone_number" id="phone_number" class="form-control"
                        value="<?= htmlspecialchars($contactDetails['phone_number']) ?>">
                </div>
                <div class="mb-3">
                    <label for="website" class="form-label">Website</label>
                    <input type="url" name="website" id="website" class="form-control"
                        value="<?= htmlspecialchars($contactDetails['website']) ?>">
                </div>
                <div class="mb-3">
                    <label for="other_info" class="form-label">Other Info</label>
                    <textarea name="other_info" id="other_info" class="form-control"
                        rows="4"><?= htmlspecialchars($contactDetails['other_info']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-success">Save Changes</button>
            </form>
        </div>
    </main>

    <?php include '../lib/footer.php'; ?>
    <script src="../theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>