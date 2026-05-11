<?php
declare(strict_types=1);

require 'inc/connect.php';

session_start();

function failWithMessage(string $message, int $statusCode = 400): void {
    http_response_code($statusCode);
    echo '<div style="max-width:720px;margin:40px auto;font-family:sans-serif;padding:20px;border:1px solid #e2e8f0;border-radius:12px;">';
    echo '<h2>Reservation could not be completed</h2>';
    echo '<p>' . htmlspecialchars($message) . '</p>';
    echo '<p><a href="javascript:history.back()">Go back</a></p>';
    echo '</div>';
    exit;
}

function parseBookingRange(string $range): array {
    if (!str_contains($range, ' to ')) {
        failWithMessage('Invalid date range.');
    }

    [$startStr, $endStr] = explode(' to ', $range, 2);

    $dateFrom = DateTime::createFromFormat('Y-m-d', trim($startStr));
    $dateTo = DateTime::createFromFormat('Y-m-d', trim($endStr));

    if (!$dateFrom || !$dateTo) {
        failWithMessage('Invalid dates.');
    }

    $dateFrom->setTime(0, 0, 0);
    $dateTo->setTime(0, 0, 0);

    if ($dateFrom >= $dateTo) {
        failWithMessage('Check-out date must be after check-in date.');
    }

    if ($dateFrom < (new DateTime('today'))) {
        failWithMessage('Reservation cannot start in the past.');
    }

    return [$dateFrom, $dateTo];
}

function bookingDates(DateTime $dateFrom, DateTime $dateTo): array {
    $dates = [];
    $cursor = clone $dateFrom;
    $end = clone $dateTo;
    $end->modify('-1 day');

    while ($cursor <= $end) {
        $dates[] = $cursor->format('Y-m-d');
        $cursor->modify('+1 day');
    }

    return $dates;
}

function fetchGoogleBusyDates(?string $calendarId): array {
    if (!$calendarId) {
        return [];
    }

    $icalUrl = 'https://calendar.google.com/calendar/ical/' . urlencode($calendarId) . '/public/basic.ics';
    $icalData = @file_get_contents($icalUrl);

    if ($icalData === false) {
        return [];
    }

    $busyDates = [];
    preg_match_all('/DTSTART(?:;VALUE=DATE)?:(\d{8})\s*DTEND(?:;VALUE=DATE)?:(\d{8})/', $icalData, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $start = DateTime::createFromFormat('Ymd', $match[1]);
        $end = DateTime::createFromFormat('Ymd', $match[2]);

        if (!$start || !$end) {
            continue;
        }

        $end->modify('-1 day');

        while ($start <= $end) {
            $busyDates[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }
    }

    return array_values(array_unique($busyDates));
}

function calculateBookingTotal(PDO $pdo, DateTime $dateFrom, DateTime $dateTo, int $guests): array {
    $dates = bookingDates($dateFrom, $dateTo);
    if (!$dates) {
        failWithMessage('The selected stay has no billable nights.');
    }

    $placeholders = implode(',', array_fill(0, count($dates), '?'));
    $stmt = $pdo->prepare("
        SELECT price_date, standard_rate, description
        FROM price_calendar
        WHERE price_date IN ($placeholders)
    ");
    $stmt->execute($dates);

    $prices = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $prices[$row['price_date']] = [
            'rate' => (float)$row['standard_rate'],
            'description' => $row['description'] ?? '—',
        ];
    }

    $total = 0.0;
    $rows = [];

    foreach ($dates as $date) {
        $rate = $prices[$date]['rate'] ?? 0.0;
        $description = $prices[$date]['description'] ?? '—';

        $total += $rate;
        $rows[] = [
            'date' => $date,
            'description' => $description,
            'rate' => $rate,
        ];
    }

    $extraGuestFee = 0.0;
    if ($guests > 2) {
        $extraGuestFee = ($guests - 2) * count($dates) * 15.0;
        $total += $extraGuestFee;
    }

    if ($total <= 0) {
        failWithMessage('Price calendar is missing for the selected dates.');
    }

    return [
        'total' => round($total, 2),
        'nights' => count($dates),
        'rows' => $rows,
        'extra_guest_fee' => round($extraGuestFee, 2),
    ];
}

function renderPriceSummary(array $calculation, int $guests): string {
    $html = '<h3>Booking Summary</h3>';
    $html .= '<p>Number of guests: <strong>' . (int)$guests . '</strong></p>';
    $html .= '<table style="border-collapse:collapse;"><thead><tr><th style="text-align:left;padding-right:1em;">Date</th><th style="text-align:left;padding-right:1em;">Description</th><th style="text-align:right;">Price</th></tr></thead><tbody>';

    foreach ($calculation['rows'] as $row) {
        $html .= '<tr>';
        $html .= '<td style="padding-right:1em;">' . htmlspecialchars(formatDateStandard($row['date'])) . '</td>';
        $html .= '<td style="padding-right:1em;">' . htmlspecialchars($row['description']) . '</td>';
        $html .= '<td style="text-align:right;">' . number_format((float)$row['rate'], 2) . ' €</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    if ($calculation['extra_guest_fee'] > 0) {
        $html .= '<p>Extra charge for guests over 2: <strong>' . number_format($calculation['extra_guest_fee'], 2) . ' €</strong></p>';
    }

    $html .= '<hr><p><strong>Total amount: ' . number_format($calculation['total'], 2) . ' €</strong></p>';

    return $html;
}

function findOrCreateVisitor(PDO $pdo, ?int $sessionVisitorId, string $name, string $email, string $phone): array {
    if ($sessionVisitorId) {
        $stmt = $pdo->prepare("SELECT * FROM visitors WHERE id = ?");
        $stmt->execute([$sessionVisitorId]);
        $visitor = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($visitor) {
            return $visitor;
        }
    }

    $stmt = $pdo->prepare("SELECT * FROM visitors WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $visitor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($visitor) {
        $update = $pdo->prepare("
            UPDATE visitors
            SET
                name = CASE WHEN name = '' OR name IS NULL THEN ? ELSE name END,
                phone = CASE WHEN phone = '' OR phone IS NULL THEN ? ELSE phone END
            WHERE id = ?
        ");
        $update->execute([$name, $phone, $visitor['id']]);

        $stmt = $pdo->prepare("SELECT * FROM visitors WHERE id = ?");
        $stmt->execute([$visitor['id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $token = bin2hex(random_bytes(16));

    $insert = $pdo->prepare("
        INSERT INTO visitors
            (name, email, phone, password_hash, verification_token, verified, visit_count, loyalty_status)
        VALUES
            (?, ?, ?, NULL, ?, 0, 0, 'new')
    ");
    $insert->execute([$name, $email, $phone ?: null, $token]);

    $visitorId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare("SELECT * FROM visitors WHERE id = ?");
    $stmt->execute([$visitorId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$range = trim($_POST['range'] ?? '');
$guests = max(1, intval($_POST['guests'] ?? 1));
$propertyId = intval($_POST['property_id'] ?? 0);
$guestName = trim($_POST['guest_name'] ?? '');
$guestEmail = strtolower(trim($_POST['guest_email'] ?? ''));
$guestPhone = trim($_POST['guest_phone'] ?? '');
$guestNote = trim($_POST['guest_note'] ?? '');
$privacyAgree = isset($_POST['privacy_agree']);

if (!$range || !$propertyId || !$guestName || !$guestEmail || !$privacyAgree) {
    failWithMessage('Please fill in all required fields.');
}

if (!filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
    failWithMessage('Please enter a valid e-mail address.');
}

$stmt = $pdo->prepare("SELECT * FROM properties WHERE property_id = ? AND status = 'active'");
$stmt->execute([$propertyId]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (
    !$property ||
    empty($property['owner_name']) ||
    empty($property['bank_account']) ||
    empty($property['owner_email']) ||
    empty($property['bic'])
) {
    failWithMessage('Property payment details are incomplete.');
}

$maxOccupancy = max(1, (int)($property['max_occupancy'] ?? 6));
if ($guests > $maxOccupancy) {
    failWithMessage('The number of guests is higher than the maximum occupancy.');
}

[$dateFrom, $dateTo] = parseBookingRange($range);
$selectedDates = bookingDates($dateFrom, $dateTo);

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
    failWithMessage('Selected date range is no longer available.');
}

// Check Google Calendar busy dates again on submit
$googleBusyDates = fetchGoogleBusyDates($property['calendar_id'] ?? null);
if (array_intersect($selectedDates, $googleBusyDates)) {
    failWithMessage('Selected date range is already blocked in the apartment calendar.');
}

// Server-side price calculation. Do not trust hidden/browser total.
$calculation = calculateBookingTotal($pdo, $dateFrom, $dateTo, $guests);
$total = (float)$calculation['total'];

$visitor = findOrCreateVisitor(
    $pdo,
    $_SESSION['visitor_id'] ?? null,
    $guestName,
    $guestEmail,
    $guestPhone
);

if (!$visitor || empty($visitor['id'])) {
    failWithMessage('Could not create visitor profile.');
}

$notes = [];
if ($guestPhone !== '') {
    $notes[] = 'Phone: ' . $guestPhone;
}
if ($guestNote !== '') {
    $notes[] = 'Guest note: ' . $guestNote;
}
$notes[] = 'Created from guest booking form.';

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("
        INSERT INTO reservations
            (property_id, visitor_id, date_from, date_to, guests, total_amount, currency, source_channel, notes, status_code)
        VALUES
            (?, ?, ?, ?, ?, ?, 'EUR', 'direct', ?, 'pending')
    ");
    $stmt->execute([
        $propertyId,
        (int)$visitor['id'],
        $dateFrom->format('Y-m-d'),
        $dateTo->format('Y-m-d'),
        $guests,
        $total,
        implode("\n", $notes),
    ]);

    $reservationId = (int)$pdo->lastInsertId();

    $token = bin2hex(random_bytes(16));
    $vs = generateVariableSymbol($dateFrom, $visitor['email'], $reservationId);

    $stmt = $pdo->prepare("UPDATE reservations SET confirmation_token = ? WHERE reservation_id = ?");
    $stmt->execute([$token, $reservationId]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Reservation insert failed: ' . $e->getMessage());
    failWithMessage('Reservation could not be saved.');
}

$recipient = htmlspecialchars($property['owner_name'] ?? '');
$iban = preg_replace('/\s+/', '', (string)($property['bank_account'] ?? ''));
$bic = htmlspecialchars($property['bic'] ?? '');
$dueDate = (new DateTime())->modify('+2 weekdays')->format('j. n. Y');
$message = "Rezervace $vs";
$qrHtml = generateQrPayment($iban, $total, $vs, $message, $bic, $recipient);
$priceSummaryHtml = renderPriceSummary($calculation, $guests);

$invoiceHtml = "
<h2>Proforma Invoice – " . htmlspecialchars($property['name']) . "</h2>
<p><strong>Reservation ID:</strong> {$reservationId}</p>
<p><strong>Stay:</strong> " . htmlspecialchars($dateFrom->format('j. n. Y')) . " – " . htmlspecialchars($dateTo->format('j. n. Y')) . "</p>
<p><strong>Guests:</strong> " . (int)$guests . "</p>
<p><strong>Amount to Pay:</strong> " . number_format($total, 2) . " EUR</p>
<p><strong>Recipient:</strong> {$recipient}</p>
<p><strong>Variable Symbol:</strong> {$vs}</p>
<p><strong>Bank Account:</strong> " . htmlspecialchars($property['bank_account']) . "</p>
<p><strong>BIC/SWIFT:</strong> {$bic}</p>
<p><strong>Due Date:</strong> {$dueDate}</p>
<p><strong>Payment Note:</strong> Reservation {$vs}</p>
<p><strong>QR Payment</strong></p>
{$qrHtml}
<hr>
{$priceSummaryHtml}
<p style='margin-top:20px;'>
The reservation is preliminary and will be confirmed after the payment is received.
If the payment is not made by the due date, the reservation can be canceled.
</p>";

$visitorName = $visitor['name'] ?: $guestName;
$visitorEmail = $visitor['email'] ?: $guestEmail;

sendEmail(
    $visitorEmail,
    $visitorName,
    "Your reservation – payment instructions",
    "<p>Dear " . htmlspecialchars($visitorName) . ",</p>
     <p>Your preliminary reservation for <strong>" . htmlspecialchars($property['name']) . "</strong> has been created. Please follow the payment instructions below.</p>
     {$invoiceHtml}"
);

$confirmationLink = "https://myscalea.it/confirm_payment.php?token={$token}";
sendEmail(
    $property['owner_email'],
    $property['owner_name'],
    "New reservation – confirm payment",
    "<p>New preliminary reservation for <strong>" . htmlspecialchars($property['name']) . "</strong>.</p>
     <p><strong>Guest:</strong> " . htmlspecialchars($visitorName) . "<br>
     <strong>Email:</strong> " . htmlspecialchars($visitorEmail) . "<br>
     <strong>Phone:</strong> " . htmlspecialchars($guestPhone ?: '-') . "</p>
     <p>After the payment arrives, confirm it here:</p>
     <p><a href='{$confirmationLink}'>{$confirmationLink}</a></p>
     {$invoiceHtml}"
);

include 'header.php';
include 'menu.php';
?>

<section class="section">
  <div style="max-width:760px;margin:auto;">
    <h1>Reservation created</h1>
    <p>
      Thank you. Your preliminary reservation has been created and payment instructions were sent to your e-mail.
    </p>
    <div style="border:1px solid #ddd;border-radius:12px;padding:18px;background:#fff;">
      <?= $invoiceHtml ?>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
