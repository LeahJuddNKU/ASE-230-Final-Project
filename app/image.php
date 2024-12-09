<?php
session_start();

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

// Validate image ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No image ID provided.";
    header("Location: /index.php");
    exit;
}
$imageId = $_GET['id'];

// Fetch image details
try {
    $stmt = $pdo->prepare(
        "SELECT g.image_link, g.subtitle, g.upload_date, u.username, u.user_id, u.display_name
        FROM Gallery g
        JOIN Users u ON g.user_id = u.user_id
        WHERE g.id = ?"
    );
    $stmt->execute([$imageId]);
    $imageDetails = $stmt->fetch();

    if (!$imageDetails) {
        $_SESSION['error'] = "Image not found.";
        header("Location: /index.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching image.";
    header("Location: /index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($imageDetails['subtitle']) ?> - Art Portfolio</title>
    <link href="/theme/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/theme/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/theme/css/main.css" rel="stylesheet">
</head>

<body>
    <?php include '../lib/header.php'; ?>

    <main class="container mt-5">
        <!-- Image Section -->
        <div class="row">
            <div class="col-md-6">
                <!-- Image -->
                <a href="<?= htmlspecialchars($imageDetails['image_link']) ?>" target="_blank">
                    <img src="<?= htmlspecialchars($imageDetails['image_link']) ?>" class="img-fluid" alt="Image">
                </a>
            </div>
            <div class="col-md-6">
                <h1><?= htmlspecialchars($imageDetails['subtitle']) ?></h1>
                <p>Uploaded: <?= htmlspecialchars($imageDetails['upload_date']) ?></p>
                <p>
                    By:
                    <a href="/app/user.php?id=<?= htmlspecialchars($imageDetails['user_id']) ?>">
                        <?= htmlspecialchars($imageDetails['display_name']) ?>
                        (@<?= htmlspecialchars($imageDetails['username']) ?>)
                    </a>
                </p>
            </div>
        </div>
    </main>

    <?php include '../lib/footer.php'; ?>
    <script src="/theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>