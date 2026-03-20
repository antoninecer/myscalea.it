<?php
require 'inc/connect.php';

use Defr\QRPlatba\QRPlatba;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

session_start();

$visitorId = $_SESSION['visitor_id'] ?? null;
$clientId = null; // deaktivováno, již se nepoužívá

$range = $_POST['range'] ?? '';
$guests = intval($_POST['guests'] ?? 1);
$propertyId = intval($_POST['property_id'] ?? 0);
$total = floatval($_POST['total'] ?? 0);

#print_r($_SESSION);
#echo "<br>";
#print_r($_POST);
#echo "<hr>";

if (!$visitorId || !$range || !$propertyId || !$total) {
    exit('Missing required fields.');
}

// Fetch visitor
$stmt = $pdo->prepare("SELECT name, email FROM visitors WHERE id = ?");
$stmt->execute([$visitorId]);
$visitor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$visitor) {
    exit('Visitor not found.');
}

// Fetch property
$stmt = $pdo->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->execute([$propertyId]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$property || empty($property['bank_account']) || empty($property['owner_email'])) {
    exit('Property details incomplete.');
}

// Parse dates
list($startStr, $endStr) = explode(' to ', $range);
$dateFrom = new DateTime($startStr);
$dateTo = new DateTime($endStr);

// Create reservation
$stmt = $pdo->prepare("INSERT INTO reservations 
    (property_id, visitor_id, date_from, date_to, guests, total_amount, currency, source_channel, status_code)
    VALUES (?, ?, ?, ?, ?, ?, 'EUR', 'direct', 'pending')");
$stmt->execute([
    $propertyId, $visitorId, $dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d'), $guests, $total
]);
$reservationId = $pdo->lastInsertId();

// Příprava údajů vlastníka
$recipient = htmlspecialchars($property['owner_name']);
$iban = htmlspecialchars($property['bank_account']);
$bic = htmlspecialchars($property['bic']);

// Generate token and variable symbol
$token = bin2hex(random_bytes(16));
$vs = generateVariableSymbol($dateFrom, $visitor['email'], $reservationId);

$stmt = $pdo->prepare("UPDATE reservations SET confirmation_token = ? WHERE reservation_id = ?");
$stmt->execute([$token, $reservationId]);

// Due date
$dueDate = (new DateTime())->modify('+2 weekdays')->format('j. n. Y');

// Get HTML price breakdown
$rangeEncoded = urlencode($range);
$summaryUrl = "https://myscalea.it/calculate_price.php?range=$rangeEncoded&guests=$guests&property_id=$propertyId";
$priceSummaryHtml = @file_get_contents($summaryUrl);
if (!$priceSummaryHtml) {
    $priceSummaryHtml = "<p style='color:red'>Could not retrieve price breakdown.</p>";
}

$message = "Rezervace $vs";
$reservation_id = $pdo->lastInsertId();



$rangeDecoded = urldecode($rangeEncoded); // "2025-05-28 to 2025-06-04"
list($dateFromStr, $dateToStr) = explode(' to ', $rangeDecoded);
$datefrom = new DateTime($dateFromStr);
$dateto = new DateTime($dateToStr);
$dateFromStr = $datefrom->format('Y-m-d');

// Vygeneruj variable symbol (VS) – použij email nebo rezervaci jako fallback
$emailForVS = $_SESSION['user']['email'] ?? $_SESSION['visitor_name'] ?? 'guest';
$vs = generateVariableSymbol(new DateTime($dateFromStr), $emailForVS, $reservation_id);

$message = "Rezervace $vs";
$qrHtml = generateQrPayment($iban, $total, $vs, $message, $bic, $recipient);

$invoiceHtml = "<h2>Proforma Invoice – {$property['name']}</h2>
<p><strong>Amount to Pay:</strong> " . number_format($total, 2) . " EUR</p>
<p><strong>Recipient:</strong> $recipient</p>
<p><strong>Variable Symbol:</strong> $vs</p>
<p><strong>Bank Account:</strong> {$property['bank_account']}</p>
<p><strong>Due Date:</strong> $dueDate</p>
<p><strong>Payment Note:</strong> Reservation $vs</p>
<p><strong>QR Payment</strong></p>
$qrHtml
<hr>
$priceSummaryHtml
<p style='margin-top:20px;'>The reservation will be confirmed after the payment is received. If the payment is not made by the due date, the reservation will be automatically canceled.</p>";

// Send email to visitor
sendEmail($visitor['email'], $visitor['name'], "Your reservation – payment instructions",
    "<p>Dear {$visitor['name']},</p>
    <p>Your reservation for <strong>{$property['name']}</strong> has been created. Please follow the payment instructions below.</p>
    $invoiceHtml");

// Send email to owner
$confirmationLink = "https://myscalea.it/confirm_payment.php?token=$token";
sendEmail($property['owner_email'], $property['owner_name'], "New reservation – confirm payment",
    "<p>New reservation for <strong>{$property['name']}</strong>.</p>
    <p>Please confirm the payment by clicking the link below:</p>
    <p><a href='$confirmationLink'>$confirmationLink</a></p>
    $invoiceHtml");

// Show invoice to user
echo $invoiceHtml;
