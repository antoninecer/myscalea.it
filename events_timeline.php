<?php
require_once 'inc/connect.php';

$limit = (isset($_GET['limit']) && is_numeric($_GET['limit']) && intval($_GET['limit']) > 0) ? intval($_GET['limit']) : null;
$categoryFilter = isset($_GET['category']) ? explode(',', $_GET['category']) : [];
$fromDate = isset($_GET['from']) ? DateTime::createFromFormat('Y-m-d', $_GET['from']) : new DateTime();

$month = (int)$fromDate->format('n');
$day = (int)$fromDate->format('j');

$sql = "SELECT e.name, e.description, e.start_day, e.start_month, e.duration_days, e.location, e.image_source_url, 
               o.name AS organizer_name, o.website AS organizer_website,
               c.label AS category_label,
               e.website
        FROM events e
        LEFT JOIN event_organizers o ON e.organizer_id = o.id
        LEFT JOIN event_categories c ON e.category_id = c.id
        WHERE (e.start_month IS NULL OR e.start_month > :month OR (e.start_month = :month AND e.start_day >= :day))";

$params = [
    'month' => $month,
    'day' => $day,
];

// Kategorie jako pojmenované parametry
if (!empty($categoryFilter)) {
    $categoryPlaceholders = [];
    foreach ($categoryFilter as $i => $cat) {
        $paramName = "cat$i";
        $categoryPlaceholders[] = ":$paramName";
        $params[$paramName] = $cat;
    }
    $sql .= " AND e.category_id IN (" . implode(', ', $categoryPlaceholders) . ")";
}

$sql .= " ORDER BY e.start_month, e.start_day";
if ($limit !== null) {
    $sql .= " LIMIT $limit";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
  .timeline-wrapper {
    overflow-x: auto;
    padding: 1rem;
    white-space: nowrap;
  }
  .timeline {
    display: flex;
    gap: 1rem;
  }
  .event {
    flex: 0 0 auto;
    position: relative;
    border-radius: 12px;
    min-width: 240px;
    height: 300px;
    background-color: #ccc;
    background-size: cover;
    background-position: center;
    color: #000;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    overflow: hidden;
    border: 2px dashed red;
    text-decoration: none;
  }
  .event .overlay {
    position: absolute;
    bottom: 0;
    width: 100%;
    padding: 0.75rem;
    background: rgba(255,255,255,0.8);
    backdrop-filter: blur(3px);
  }
  .event h4 {
    margin: 0 0 0.25rem;
    font-size: 1.1em;
  }
  .event small, .event em, .event p {
    font-size: 0.85em;
    margin: 0;
  }
  .event p {
    margin-top: 0.3rem;
  }
</style>

<div class="timeline-wrapper">
  <div class="timeline">
    <?php foreach ($events as $event): 
      // Kategorie pro fallback obrázek
      $category = strtolower(preg_replace('/\s+/', '_', $event['category_label'] ?? 'default'));
      $fallbackImage = "/fallbacks/{$category}.jpg"; // např. /fallbacks/music.jpg

      // Pokud máme externí obrázek, použijeme proxy, jinak fallback
      $imageUrl = (!empty($event['image_source_url']) && stripos($event['image_source_url'], 'http') === 0)
        ? 'image_proxy.php?url=' . urlencode($event['image_source_url'])
        : $fallbackImage;

      // Použij odkaz z eventu nebo organizátora
      $hasLink = !empty($event['website']) || !empty($event['organizer_website']);
      $linkHref = $event['website'] ?? $event['organizer_website'] ?? '';
      $linkStart = $hasLink
        ? '<a class="event" href="' . htmlspecialchars($linkHref) . '" target="_blank" style="background-image: url(\'' . htmlspecialchars($imageUrl) . '\')">'
        : '<div class="event" style="background-image: url(\'' . htmlspecialchars($imageUrl) . '\')">';
      $linkEnd = $hasLink ? '</a>' : '</div>';
      echo $linkStart;
    ?>
        <div class="overlay">
          <h4><?= htmlspecialchars($event['name']) ?></h4>
          <small><?= $event['start_day'] ?>.<?= $event['start_month'] ?> (<?= $event['duration_days'] ?> day<?= $event['duration_days'] > 1 ? 's' : '' ?>)</small><br>
          <em><?= htmlspecialchars($event['category_label'] ?? '') ?></em><br>
          <span style="font-size:0.9em; color:#555;"><?= htmlspecialchars($event['location']) ?></span>
          <p><?= htmlspecialchars($event['description']) ?></p>
          <?php if (!empty($event['organizer_website'])): ?>
            <a href="<?= htmlspecialchars($event['organizer_website']) ?>" target="_blank"><?= htmlspecialchars($event['organizer_name']) ?></a>
          <?php elseif (!empty($event['organizer_name'])): ?>
            <strong><?= htmlspecialchars($event['organizer_name']) ?></strong>
          <?php endif; ?>
          <div style="font-size: 0.7em; color: #999; margin-top: 0.5em;">
            <?= htmlspecialchars($event['image_source_url'] ?: $fallbackImage) ?>
          </div>
        </div>
      <?= $linkEnd ?>
    <?php endforeach; ?>
  </div>
</div>

