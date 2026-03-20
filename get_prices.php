<?php
require 'inc/connect.php';

$prices = [];
$stmt = $pdo->query("SELECT price_date, standard_rate FROM price_calendar");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $prices[$row['price_date']] = (float)$row['standard_rate'];
}
header('Content-Type: application/json');
echo json_encode($prices);

