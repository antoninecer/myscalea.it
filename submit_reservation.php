<?php
require 'inc/connect.php';

session_start();

$visitorId  = $_SESSION['visitor_id'] ?? null;
$range      = $_POST['range'] ?? '';
$guests     = intval($_POST['guests'] ?? 1);
$propertyId = intval($_POST['property_id'] ?? 0);
$total      = floatval($_POST['total'] ?? 0);

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
if (
    !$property ||
    empty($property['owner_name']) ||
    empty($property['bank_account']) ||
    empty($property['owner_email'])
) {
    exit('Property details incomplete.');
}

// Parse dates
if (!str_contains($range, ' to ')) {
    exit('Invalid date range.');
}
list($startStr, $endStr) = explode(' to ', $range, 2);

try {
    $dateFrom = new DateTime($startStr);
    $dateTo   = new DateTime($endStr);
} catch (Exception $e) {
    exit('Invalid dates.');
}

// Check overlap with existing pending/confirmed reservations
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM reservations
    WHERE property_id = ?
      AND status_code IN ('pending', 'confirmed')
      AND date_from < ?
      AND date_to > ?
");
$stmt->execute([
    $propertyId,
    $dateTo->format('Y-m-d'),
    $dateFrom->format('Y-m-d')
]);

if ((int)$stmt->fetchColumn() > 0) {
    exit('Selected date range is no longer available.');
}

// Create reservation
$stmt = $pdo->prepare("
    INSERT INTO reservations
        (property_id, visitor_id, date_from, date_to, guests, total_amount, currency, source_channel, status_code)
    VALUES
        (?, ?, ?, ?, ?, ?, 'EUR', 'direct', 'pending')
");
$stmt->execute([
    $propertyId,
    $visitorId,
    $dateFrom->format('Y-m-d'),
    $dateTo->format('Y-m-d'),
    $guests,
    $total
]);

$reservationId = (int)$pdo->lastInsertId();

// Příprava údajů vlastníka
$recipient = htmlspecialchars($property['owner_name'] ?? '');
$iban      = preg_replace('/\s+/', '', (string)($property['bank_account'] ?? ''));
$bic       = htmlspecialchars($property['bic'] ?? '');

// Generate token and variable symbol
$token = bin2hex(random_bytes(16));
$vs    = generateVariableSymbol($dateFrom, $visitor['email'], $reservationId);

$stmt = $pdo->prepare("UPDATE reservations SET confirmation_token = ? WHERE reservation_id = ?");
$stmt->execute([$token, $reservationId]);

// Due date
$dueDate = (new DateTime())->modify('+2 weekdays')->format('j. n. Y');

// Get HTML price breakdown
$rangeEncoded = urlencode($range);
$summaryUrl = "https://myscalea.it/calculate_price.php?range={$rangeEncoded}&guests={$guests}&property_id={$propertyId}";
$priceSummaryHtml = @file_get_contents($summaryUrl);
if (!$priceSummaryHtml) {
    $priceSummaryHtml = "<p style='color:red'>Could not retrieve price breakdown.</p>";
}

$message = "Rezervace $vs";
$qrHtml  = generateQrPayment($iban, $total, $vs, $message, $bic, $recipient);

$invoiceHtml = "
<h2>Proforma Invoice – {$property['name']}</h2>
<p><strong>Amount to Pay:</strong> " . number_format($total, 2) . " EUR</p>
<p><strong>Recipient:</strong> {$recipient}</p>
<p><strong>Variable Symbol:</strong> {$vs}</p>
<p><strong>Bank Account:</strong> {$property['bank_account']}</p>
<p><strong>Due Date:</strong> {$dueDate}</p>
<p><strong>Payment Note:</strong> Reservation {$vs}</p>
<p><strong>QR Payment</strong></p>
{$qrHtml}
<hr>
{$priceSummaryHtml}
<p style='margin-top:20px;'>
The reservation will be confirmed after the payment is received.
If the payment is not made by the due date, the reservation will be automatically canceled.
</p>";

// Send email to visitor
sendEmail(
    $visitor['email'],
    $visitor['name'],
    "Your reservation – payment instructions",
    "<p>Dear {$visitor['name']},</p>
     <p>Your reservation for <strong>{$property['name']}</strong> has been created. Please follow the payment instructions below.</p>
     {$invoiceHtml}"
);

// Send email to owner
$confirmationLink = "https://myscalea.it/confirm_payment.php?token={$token}";
sendEmail(
    $property['owner_email'],
    $property['owner_name'],
    "New reservation – confirm payment",
    "<p>New reservation for <strong>{$property['name']}</strong>.</p>
     <p>Please confirm the payment by clicking the link below:</p>
     <p><a href='{$confirmationLink}'>{$confirmationLink}</a></p>
     {$invoiceHtml}"
);

// Show invoice to user
echo $invoiceHtml;