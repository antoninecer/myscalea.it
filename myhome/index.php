<?php
// Nahraďte cílovou URL adresu za tu svou:
$cilova_url = 'https://myscalea.it/my_way.php';

// Pošleme HTTP hlavičku pro přesměrování
header('Location: ' . $cilova_url, true, 302);
exit;
?>
