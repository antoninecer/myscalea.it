<?php
session_start();
require_once 'inc/connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'agent' || !$_SESSION['user']['agency_id']) {
    exit("Access denied");
}

// Načti údaje o agentuře
$agency_id = $_SESSION['user']['agency_id'];
$stmt = $pdo->prepare("SELECT * FROM agencies WHERE id = ?");
$stmt->execute([$agency_id]);
$agency = $stmt->fetch();

if (!$agency) exit("Agency not found");

$safeName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($agency['agency_name']));
$galleryUsername = $safeName;
$galleryPassword = generatePiwigoPassword($agency['agency_name'], $agency_id);

// Připrav cURL požadavek
$loginUrl = 'https://gallery.myscalea.it/identification.php';
$postFields = http_build_query([
    'username' => $galleryUsername,
    'password' => $galleryPassword,
    'login'    => 'Login'
]);

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
curl_close($ch);

// Přečti cookie z cookie.txt
if (preg_match('/Set-Cookie: pwg_id=([^;]+)/', $response, $matches)) {
    $pwg_id = $matches[1];

    // Nastav cookie a přesměruj
    setcookie('pwg_id', $pwg_id, time() + 3600, '/', 'gallery.myscalea.it', true, true);
    header("Location: https://gallery.myscalea.it/");
    exit;
} else {
    exit("Login failed");
}
