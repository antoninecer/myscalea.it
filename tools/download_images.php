<?php
require_once '../inc/connect.php';

$saveDir = __DIR__ . '/../uploads/';
$baseUrl = 'https://myscalea.it/uploads/';

$query = $pdo->query("SELECT id, image_source_url FROM events WHERE image_source_url IS NOT NULL AND image_source_url LIKE 'https://%'");
$events = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($events as $event) {
    $url = $event['image_source_url'];
    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    $ext = strtolower($ext ?: 'jpg');
    $filename = 'event_' . $event['id'] . '.' . $ext;
    $path = $saveDir . $filename;

    echo "Downloading ID {$event['id']} from $url ... ";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10, // maximální čas stahování
        CURLOPT_CONNECTTIMEOUT => 5, // maximální čas spojení
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    ]);
    $data = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($http === 200 && $data) {
        file_put_contents($path, $data);
        $stmt = $pdo->prepare("UPDATE events SET image_source_url = ? WHERE id = ?");
        $stmt->execute([$baseUrl . $filename, $event['id']]);
        echo "✔️ saved as $filename\n";
    } else {
        echo "❌ failed ($http - $err)\n";
    }
}

