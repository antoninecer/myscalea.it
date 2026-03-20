<?php
require __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Vstupní data
$data = $_POST['data'] ?? 'https://myscalea.it/';

// QR kód
$qrCode = new QrCode($data);

// Writer
$writer = new PngWriter();
$result = $writer->write($qrCode);

// Výstup
header('Content-Type: ' . $result->getMimeType());
echo $result->getString();

