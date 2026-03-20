<?php
require 'inc/connect.php';

// Get inputs
$range = $_GET['range'] ?? '';
$guests = intval($_GET['guests'] ?? 1);
$propertyId = intval($_GET['property_id'] ?? 0);

if (!$range || !$propertyId) {
    exit('Invalid input.');
}

list($startStr, $endStr) = explode(' to ', $range);
$start = new DateTime($startStr);
$end = new DateTime($endStr);

$interval = new DateInterval('P1D');
$period = new DatePeriod($start, $interval, $end); // excludes the last day = number of nights

// Load unavailable dates from Google Calendar
$stmt = $pdo->prepare("SELECT calendar_id FROM properties WHERE property_id = ?");
$stmt->execute([$propertyId]);
$calendarId = $stmt->fetchColumn();

$icalUrl = 'https://calendar.google.com/calendar/ical/' . urlencode($calendarId) . '/public/basic.ics';
$icalData = @file_get_contents($icalUrl);
$disabled = [];
if ($icalData !== false) {
    preg_match_all('/DTSTART(?:;VALUE=DATE)?:(\d{8})\s*DTEND(?:;VALUE=DATE)?:(\d{8})/', $icalData, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $s = DateTime::createFromFormat('Ymd', $match[1]);
        $e = DateTime::createFromFormat('Ymd', $match[2]);
        $e->modify('-1 day');
        while ($s <= $e) {
            $disabled[] = $s->format('Y-m-d');
            $s->modify('+1 day');
        }
    }
}

$disabled = array_unique($disabled);

// Load prices from DB
$stmt = $pdo->query("SELECT price_date, standard_rate, description FROM price_calendar");
$prices = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $prices[$row['price_date']] = [
        'rate' => (float)$row['standard_rate'],
        'desc' => $row['description'] ?? '—'
    ];
}

// Calculation
$output = "<h3>Booking Summary</h3>";
$output .= "<p>Number of guests: <strong>$guests</strong></p>";
$output .= "<table><thead><tr><th>Date</th><th>Description</th><th>Price</th></tr></thead><tbody>";

$total = 0;
$nights = 0;
$unavailable = [];

foreach ($period as $date) {
    $key = $date->format('Y-m-d'); // pro lookup v $prices
    $d = formatDateStandard($key); // pro zobrazení

    if (in_array($key, $disabled)) {
        $unavailable[] = $d;
        continue;
    }

    $rate = $prices[$key]['rate'] ?? 0;
    $desc = $prices[$key]['desc'] ?? '—';
    //$output .= "<tr><td>$d</td><td>" . htmlspecialchars($desc) . "</td><td>" . number_format($rate, 2) . " €</td></tr>";
    $output .= "<tr>
  <td style='padding-right: 1em;'>$d</td>
  <td style='padding-right: 1em;'>" . htmlspecialchars($desc) . "</td>
  <td style='padding-right: 1em; text-align: right;'>" . number_format($rate, 2) . " €</td>
</tr>";
    $total += $rate;
    $nights++;
}


$output .= "</tbody></table>";

if ($unavailable) {
    echo "<p style='color:red'>The selected range includes unavailable dates: " . implode(', ', $unavailable) . "</p>";
    exit;
}

if ($guests > 2) {
    $extra = ($guests - 2) * $nights * 15;
    $output .= "<p>Extra charge for guests over 2: <strong>" . number_format($extra, 2) . " €</strong></p>";
    $total += $extra;
}

$output .= "<hr><p><strong>Total amount: " . number_format($total, 2) . " €</strong></p>";
echo $output;
