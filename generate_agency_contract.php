<?php
session_start();
require_once 'inc/connect.php';
require('vendor/fpdf.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['username'] !== 'tonda') {
    exit('Access denied. Only authorized user can generate contracts.');
}

$agency_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$agency_id) {
    exit('Missing agency ID.');
}

// Load agency info
$stmt = $pdo->prepare("SELECT * FROM agencies WHERE id = ?");
$stmt->execute([$agency_id]);
$agency = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$agency) {
    exit('Agency not found.');
}

// Load template
$template = file_get_contents('smlouvy/ramcovasmlouva1it.txt');
if (!$template) {
    exit('Template file not found.');
}

// Replace placeholders
$template = str_replace('{{agency_name}}', $agency['agency_name'], $template);
$template = str_replace('{{agency_address}}', $agency['agency_address'], $template);
$template = str_replace('{{agency_city}}', $agency['agency_city'], $template);
$template = str_replace('{{agency_zip}}', $agency['agency_zip'], $template);
$template = str_replace('{{agency_country}}', $agency['agency_country'], $template);
$template = str_replace('{{representative_name}}', $agency['representative_name'], $template);
$template = str_replace('{{CREATED}}', date('d.m.Y'), $template);

// Convert encoding
$text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $template);

// Prepare PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$lines = explode("\n", $text);

foreach ($lines as $line) {
    if (strpos($line, '{{SIGNATURE_PLACE}}') !== false) {
        $pdf->Ln(5);
        $pdf->Image('smlouvy/sign/tonda_sign.png', 10, $pdf->GetY(), 20);
        $pdf->Ln(20);
    } else {
        $pdf->MultiCell(0, 6, $line);
    }
}

// Create target folder if not exists
$folderName = 'agencies/' . preg_replace('/[^a-zA-Z0-9]/', '', $agency['agency_name']);
if (!is_dir($folderName)) {
    mkdir($folderName, 0777, true);
}

// Save final PDF to disk
$filename = $folderName . '/ramcova_smlouva_' . preg_replace('/[^a-zA-Z0-9]/', '', $agency['agency_name']) . '_prefirmato.pdf';
$pdf->Output('F', $filename);

// Redirect back with status
header('Location: agency_list.php?generated=1');
exit;

