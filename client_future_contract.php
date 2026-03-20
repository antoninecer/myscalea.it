<?php
// client_future_contract.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<p style='color:red;'>❌ Tento skript je dostupný pouze přes POST.</p>";
    exit;
}

$templatePath = __DIR__ . '/client_future_contract.txt';
if (!file_exists($templatePath)) {
    echo "<p style='color:red;'>❌ Šablona smlouvy nebyla nalezena.</p>";
    exit;
}
$template = file_get_contents($templatePath);
print_r($_POST);
// Mapa: český název z POST => klíč v šabloně
$map = [
    'name' => '{{name}}',
    'email' => '{{email}}',
    'Účel' => '{{purpose}}',
    'Pronájem' => '{{rental}}',
    'Forma_pronájmu' => '{{rental_form}}',
    'Ložnice' => '{{bedrooms}}',
    'Koupelny' => '{{bathrooms}}',
    'Balkon_/_terasa' => '{{balcony}}',
    'Počet_osob_/_dětí' => '{{persons_children}}',
    'Parkování' => '{{parking}}',
    'Rozsah_pater' => '{{floors_range}}',
    'Výtah_od_patra' => '{{elevator_from}}',
    'Vzdálenost_od_moře' => '{{distance_sea}}',
    'Vzdálenost_od_centra' => '{{distance_center}}',
    'Minimální_plocha' => '{{min_area}}',
    'Způsob_financování' => '{{financing}}',
    'Finanční_poradce' => '{{advisor}}',
    'Cenový_strop' => '{{price_cap}}',
    'Forma_prohlídek' => '{{viewing_form}}',
    'today' => '{{today}}'
];

// Vytvořit pole hodnot pro šablonu
$data = [];
foreach ($map as $postKey => $templateKey) {
    if ($postKey === 'today') {
        $data[$templateKey] = date('d.m.Y');
    } else {
        $data[$templateKey] = htmlspecialchars(trim($_POST[$postKey] ?? '—'));
    }
}

// Vložit data do šablony
$filled = str_replace(array_keys($data), array_values($data), $template);

// Výstup HTML
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Smlouva o budoucí spolupráci</title>
    <style>
        body { font-family: sans-serif; padding: 30px; max-width: 800px; margin: auto; line-height: 1.6; }
        pre { white-space: pre-wrap; }
        .print-button { margin-top: 20px; }
    </style>
</head>
<body>
<pre><?= $filled ?></pre>
<p class="print-button"><button onclick="window.print()">🖨️ Vytisknout</button></p>
</body>
</html>
