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
    $_SESSION['error'] = "Database connection failed.";
    header("Location: /app/error.php");
    exit;
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $subtitle = htmlspecialchars($_POST['subtitle']);
    $uploadDir = __DIR__ . '/../data/images/';
    $allowedTypes = ['image/jpeg', 'image/png'];
    $maxFileSize = 2 * 1024 * 1024; // 2 MB

    // Validate file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);
        $fileSize = $_FILES['image']['size'];
        $fileType = mime_content_type($fileTmpPath);
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $newFileName = uniqid('img_', true) . '.' . $fileExtension;

        if (!in_array($fileType, $allowedTypes)) {
            $message = "Only JPG and PNG allowed.";
        } elseif ($fileSize > $maxFileSize) {
            $message = "File size exceeds 2MB.";
        } else {
            $destPath = $uploadDir . $newFileName;

            // Ensure directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move file
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                try {
                    // Save to database
                    $stmt = $pdo->prepare(
                        "INSERT INTO Gallery (user_id, image_link, subtitle, upload_date) 
                         VALUES (?, ?, ?, NOW(6))"
                    );
                    $stmt->execute([$userId, "/data/images/" . $newFileName, $subtitle]);
                    $_SESSION['success'] = "Image uploaded successfully.";
                    header("Location: /app/gallery.php");
                    exit;
                } catch (PDOException $e) {
                    $message = "Error saving image.";
                }
            } else {
                $message = "Error moving uploaded file.";
            }
        }
    } else {
        $message = "Error uploading file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image</title>
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

        .btn-primary {
            width: 100%;
        }

        .card {
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .alert-info {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <?php include '../lib/header.php'; ?>

    <main class="container mt-5">
        <div class="form-container">
            <h1 class="form-header">Upload Image</h1>

            <!-- Display messages -->
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php elseif (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Upload Form -->
            <div class="card p-4">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="image" class="form-label">Select Image</label>
                        <input type="file" name="image" id="image" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="subtitle" class="form-label">Subtitle</label>
                        <input type="text" name="subtitle" id="subtitle" class="form-control" maxlength="255" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-arrow-up"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </main>

    <?php include '../lib/footer.php'; ?>
    <script src="../theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>