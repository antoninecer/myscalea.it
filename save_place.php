<?php
// save_place.php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

require_once __DIR__ . '/inc/connect.php';

// Získání dat z formuláře
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;
$category_id = $_POST['category_id'] ?? null;
$opening_hours_raw = $_POST['opening_hours'] ?? '{}'; // JSON string
$phone = $_POST['phone'] ?? null;
$email = $_POST['email'] ?? null;
$address = $_POST['address'] ?? null;
$website = $_POST['website'] ?? null;
$tags = $_POST['tags'] ?? null; // může být i null
$url = $_POST['url'] ?? null;

// Validace a fallback na prázdný JSON pokud něco zlobí
$opening_hours = json_validate($opening_hours_raw) ? $opening_hours_raw : '{}';

if (!function_exists('json_validate')) {
    function json_validate($string) {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}

// Zpracování obrázku
$photo_filename = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tmpName = $_FILES['photo']['tmp_name'];
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $photo_filename = uniqid('place_', true) . '.' . strtolower($ext);
    $targetFile = $uploadDir . $photo_filename;

    move_uploaded_file($tmpName, $targetFile);
}

try {
    $stmt = $pdo->prepare("INSERT INTO places (name, description, latitude, longitude, category_id, opening_hours, phone, website, email, address, tags, created_at)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $name, $description, $latitude, $longitude, $category_id,
        $opening_hours, $phone, $website, $email, $address, $tags
    ]);

    // Photo file handling (pokud máš sloupec např. photo_filename ve `places`)
    // $pdo->lastInsertId() ti vrátí ID posledního záznamu
    // Lze snadno rozšířit.

    header("Location: index.php");
    exit;
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}

