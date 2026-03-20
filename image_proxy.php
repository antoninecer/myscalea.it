<?php
$url = $_GET['url'] ?? '';
$fallback = $_GET['fallback'] ?? '/fallbacks/default.jpg';

// Validace URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    header("Location: $fallback");
    exit;
}

// Inicializace cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0'); // ← důležité!
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

// Získání hlaviček
$headers = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

// Zkontroluj, jestli je dostupný obrázek
if ($httpCode === 200 && strpos($contentType, 'image') !== false) {
    // Znovu načti obsah obrázku
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $imageData = curl_exec($ch);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    header("Content-Type: $contentType");
    echo $imageData;
} else {
    // Fallback obrázek
    header("Location: $fallback");
    exit;
}

