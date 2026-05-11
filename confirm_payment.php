<?php
declare(strict_types=1);

session_start();
require 'inc/connect.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

if ($token === '' || !preg_match('/^[a-f0-9]{32}$/', $token)) {
    http_response_code(400);
    exit('Invalid confirmation token.');
}

$stmt = $pdo->prepare("
    SELECT
        r.*,
        p.name AS property_name,
        p.owner_name,
        p.owner_email,
        v.name AS visitor_name,
        v.email AS visitor_email,
        v.phone AS visitor_phone
    FROM reservations r
    JOIN properties p ON p.property_id = r.property_id
    JOIN visitors v ON v.id = r.visitor_id
    WHERE r.confirmation_token = ?
    LIMIT 1
");
$stmt->execute([$token]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    http_response_code(404);
    exit('Reservation not found.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($reservation['status_code'] === 'confirmed') {
        $message = 'Reservation was already confirmed.';
    } else {
        $update = $pdo->prepare("
            UPDATE reservations
            SET status_code = 'confirmed'
            WHERE reservation_id = ?
              AND confirmation_token = ?
        ");
        $update->execute([(int)$reservation['reservation_id'], $token]);

        $reservation['status_code'] = 'confirmed';
        $message = 'Payment confirmed and reservation marked as confirmed.';

        sendEmail(
            $reservation['visitor_email'],
            $reservation['visitor_name'],
            'Reservation confirmed',
            '<p>Dear ' . htmlspecialchars($reservation['visitor_name']) . ',</p>
             <p>Your reservation for <strong>' . htmlspecialchars($reservation['property_name']) . '</strong> has been confirmed.</p>
             <p>Stay: ' . htmlspecialchars(formatDateStandard($reservation['date_from'])) . ' – ' . htmlspecialchars(formatDateStandard($reservation['date_to'])) . '</p>'
        );
    }
}

include 'header.php';
include 'menu.php';
?>

<section class="section">
  <div style="max-width:760px;margin:auto;">
    <h1>Confirm payment</h1>

    <?php if ($message): ?>
      <p style="padding:12px;border-radius:10px;background:#e8fff4;border:1px solid #a7f3d0;">
        <?= htmlspecialchars($message) ?>
      </p>
    <?php endif; ?>

    <div style="border:1px solid #ddd;border-radius:12px;padding:18px;background:#fff;">
      <p><strong>Reservation ID:</strong> <?= (int)$reservation['reservation_id'] ?></p>
      <p><strong>Property:</strong> <?= htmlspecialchars($reservation['property_name']) ?></p>
      <p><strong>Guest:</strong> <?= htmlspecialchars($reservation['visitor_name']) ?>, <?= htmlspecialchars($reservation['visitor_email']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($reservation['visitor_phone'] ?? '-') ?></p>
      <p><strong>Stay:</strong> <?= htmlspecialchars(formatDateStandard($reservation['date_from'])) ?> – <?= htmlspecialchars(formatDateStandard($reservation['date_to'])) ?></p>
      <p><strong>Guests:</strong> <?= (int)$reservation['guests'] ?></p>
      <p><strong>Total:</strong> <?= number_format((float)$reservation['total_amount'], 2) ?> <?= htmlspecialchars($reservation['currency']) ?></p>
      <p><strong>Status:</strong> <?= htmlspecialchars($reservation['status_code'] ?? 'pending') ?></p>
    </div>

    <?php if ($reservation['status_code'] !== 'confirmed'): ?>
      <form method="POST" style="margin-top:20px;">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <button type="submit" class="cta-button">
          Confirm payment received
        </button>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php include 'footer.php'; ?>
