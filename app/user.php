<?php
include '../lib/header.php'; // Header and session start

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
    $_SESSION['error'] = "Database error.";
    header("Location: /app/error.php");
    exit;
}

// Validate user ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid user.";
    header("Location: /index.php");
    exit;
}
$profileUserId = $_GET['id'];

// Fetch user details
try {
    $stmt = $pdo->prepare("SELECT username, display_name, bio FROM Users WHERE user_id = ?");
    $stmt->execute([$profileUserId]);
    $userDetails = $stmt->fetch();

    if (!$userDetails) {
        $_SESSION['error'] = "User not found.";
        header("Location: /index.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching user.";
    header("Location: /app/error.php");
    exit;
}

// Fetch user's images
try {
    $stmt = $pdo->prepare("SELECT id, image_link, subtitle FROM Gallery WHERE user_id = ? ORDER BY upload_date DESC");
    $stmt->execute([$profileUserId]);
    $userImages = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching gallery.";
    header("Location: /app/error.php");
    exit;
}

// Fetch contact details
try {
    $stmt = $pdo->prepare("SELECT email, phone_number, website, other_info FROM Contact WHERE user_id = ?");
    $stmt->execute([$profileUserId]);
    $contactDetails = $stmt->fetch() ?: [];
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching contact.";
    header("Location: /app/error.php");
    exit;
}

// Check ownership/admin
$isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profileUserId;
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($userDetails['display_name']) ?>'s Profile</title>
    <link href="/theme/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/theme/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/theme/css/main.css" rel="stylesheet">
</head>

<body>
    <main class="container mt-5">
        <!-- User Profile -->
        <div class="text-center mb-5">
            <h1><?= htmlspecialchars($userDetails['display_name']) ?></h1>
            <p>@<?= htmlspecialchars($userDetails['username']) ?></p>
            <p><?= $userDetails['bio'] ? htmlspecialchars($userDetails['bio']) : "No bio yet." ?></p>

            <!-- Contact Information -->
            <div class="mt-4">
                <ul class="list-unstyled d-flex flex-column align-items-center">
                    <?php if (!empty($contactDetails['email'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-envelope-fill me-2"></i>
                            <?= htmlspecialchars($contactDetails['email']) ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($contactDetails['phone_number'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-telephone-fill me-2"></i>
                            <?= htmlspecialchars($contactDetails['phone_number']) ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($contactDetails['website'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-globe me-2"></i>
                            <a href="<?= htmlspecialchars($contactDetails['website']) ?>" target="_blank">
                                <?= htmlspecialchars($contactDetails['website']) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($contactDetails['other_info'])): ?>
                        <li class="mb-2">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <?= nl2br(htmlspecialchars($contactDetails['other_info'])) ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <?php if ($isOwner || $isAdmin): ?>
                <a href="/app/edit-profile.php?id=<?= $profileUserId ?>" class="btn btn-primary mt-4">Edit Profile</a>
            <?php endif; ?>
        </div>

        <!-- User's Images -->
        <h2 class="text-center">Gallery</h2>
        <div class="row gy-4 justify-content-center">
            <?php if (count($userImages) > 0): ?>
                <?php foreach ($userImages as $image): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card h-100">
                            <a href="/app/image.php?id=<?= htmlspecialchars($image['id']) ?>">
                                <img src="<?= htmlspecialchars($image['image_link']) ?>" class="card-img-top" alt="Image">
                            </a>
                            <div class="card-body text-center">
                                <p class="card-text"><?= htmlspecialchars($image['subtitle']) ?></p>
                                <?php if ($isOwner || $isAdmin): ?>
                                    <a href="/app/delete-image.php?id=<?= htmlspecialchars($image['id']) ?>"
                                        class="btn btn-danger btn-sm">Delete</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No images uploaded yet.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../lib/footer.php'; ?>
    <script src="/theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>