<?php
// property.php
session_start();
require 'inc/connect.php';

// 1. Load property
$propertyId = intval($_GET['property_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM properties WHERE property_id = :id AND status = 'active'");
$stmt->execute([':id' => $propertyId]);
$property = $stmt->fetch();
if (!$property) {
    http_response_code(404);
    exit('Property not found');
}

// 2. Fetch reserved date ranges for this property
$resStmt = $pdo->prepare("
  SELECT date_from, date_to
    FROM reservations
   WHERE property_id = :pid
     AND status <> 'cancelled'");
$resStmt->execute([':pid' => $propertyId]);
$reservations = $resStmt->fetchAll();

// Build array of disabled dates
$disabled = [];
foreach ($reservations as $r) {
    $start = new DateTime($r['date_from']);
    $end   = new DateTime($r['date_to']);
    // date_to is exclusive, so disable up to day before
    $end->modify('-1 day');
    for ($d = $start; $d <= $end; $d->modify('+1 day')) {
        $disabled[] = $d->format('Y-m-d');
    }
}
// unique and JSON
$disabled = array_unique($disabled);
$disabledJson = json_encode(array_values($disabled));

// 3. Handle booking form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $df = $_POST['date_from'];
    $dt = $_POST['date_to'];
    $guests = max(1, intval($_POST['guests']));
    // validate
    if (!$df || !$dt || strtotime($df) >= strtotime($dt)) {
        $errors[] = 'Invalid date range.';
    }
    // overlap check
    $ovStmt = $pdo->prepare(
        "SELECT 1 FROM reservations
         WHERE property_id = :pid
           AND status <> 'cancelled'
           AND date_from < :dt
           AND date_to > :df"
    );
    $ovStmt->execute([':pid'=>$propertyId, ':df'=>$df, ':dt'=>$dt]);
    if ($ovStmt->fetch()) {
        $errors[] = 'Selected dates are not available.';
    }
    if (empty($errors)) {
        // calculate total via price_calendar
        $priceStmt = $pdo->prepare(
            "SELECT SUM(
                CASE :plan
                  WHEN 'low' THEN low_rate
                  WHEN 'standard' THEN standard_rate
                  WHEN 'high' THEN high_rate
                END
              ) + (SUM(extra_fee) * GREATEST(:guests - 2,0)) AS total
             FROM (
               WITH RECURSIVE cal AS (
                 SELECT :df AS dt
                 UNION ALL
                 SELECT DATE_ADD(dt,INTERVAL 1 DAY)
                   FROM cal WHERE DATE_ADD(dt,INTERVAL 1 DAY) < :dt
               ) SELECT dt FROM cal
             ) days
             JOIN price_calendar pc ON pc.price_date = days.dt;"
        );
        $priceStmt->execute([':plan'=>$property['rate_plan'], ':guests'=>$guests, ':df'=>$df, ':dt'=>$dt]);
        $total = $priceStmt->fetchColumn();
        // insert reservation
        $ins = $pdo->prepare(
            "INSERT INTO reservations
              (property_id, client_id, date_from, date_to, guests, total_amount)
             VALUES
              (:pid, :cid, :df, :dt, :g, :tot)"
        );
        $ins->execute([
            ':pid'=>$propertyId,
            ':cid'=>$_SESSION['client_id'] ?? null,
            ':df'=>$df,
            ':dt'=>$dt,
            ':g'=>$guests,
            ':tot'=>round($total,2)
        ]);
        // redirect to confirmation
        header("Location: confirm.php?reservation_id=" . $pdo->lastInsertId());
        exit;
    }
}

include 'header.php';
include 'menu.php';
?>

<section class="section property-detail">
  <h1><?= htmlspecialchars($property['name']) ?></h1>
  <p><?= htmlspecialchars($property['address']) ?></p>

  <div id="datepicker"></div>

  <form method="post" class="booking-form">
    <label>Check-in: <input type="text" name="date_from" id="from" required></label>
    <label>Check-out: <input type="text" name="date_to" id="to" required></label>
    <label>Guests: <input type="number" name="guests" min="1" value="1"></label>
    <button type="submit">Book Now</button>
  </form>
  <?php if (!empty($errors)): ?>
    <div class="errors">
      <?php foreach ($errors as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script>
  const disabledDates = <?= $disabledJson ?>;
  flatpickr("#from", {
    dateFormat:'Y-m-d',
    disable: disabledDates,
    onChange: function(selectedDates, dateStr, instance) {
      toCalendar.set('minDate', dateStr);
    }
  });
  const toCalendar = flatpickr("#to", {
    dateFormat:'Y-m-d',
    disable: disabledDates
  });
</script>

<?php include 'footer.php'; ?>

