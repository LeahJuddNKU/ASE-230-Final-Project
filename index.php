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
    die("Database connection failed: " . $e->getMessage());
}

// Fetch the 20 newest photos with user handle
try {
    $stmt = $pdo->prepare("
        SELECT g.id, g.image_link, g.subtitle, u.username 
        FROM Gallery g 
        JOIN Users u ON g.user_id = u.user_id 
        ORDER BY g.upload_date DESC 
        LIMIT 20
    ");
    $stmt->execute();
    $galleryItems = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching gallery data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Portfolio</title>
    <link href="theme/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="theme/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="theme/css/main.css" rel="stylesheet">
    <style>
        .gallery-card {
            width: 100%;
            aspect-ratio: 4 / 3; /* Maintain a 4:3 aspect ratio */
            object-fit: cover; /* Ensure consistent sizing */
        }

        .gallery-item {
            max-width: 300px; /* Limit max size */
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php include 'lib/header.php'; ?>

    <main class="container mt-5">
        <h1 class="text-center">Welcome to Art Portfolio!</h1>
        <h3 class="text-center">Connecting to artists made easy.</h3>
        <div class="row gy-4 justify-content-center">
            <?php foreach ($galleryItems as $item): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 gallery-item">
                    <div class="card">
                        <a href="/app/image.php?id=<?= $item['id'] ?>">
                            <img src="<?= htmlspecialchars($item['image_link']) ?>" class="card-img-top gallery-card" alt="User Image">
                        </a>
                        <div class="card-body text-center">
                            <p class="card-text">
                                <?= htmlspecialchars($item['subtitle']) ?> 
                                <br>
                                <small class="text-muted">By <?= htmlspecialchars($item['username']) ?></small>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include 'lib/footer.php'; ?>
    <script src="theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
