<?php
// property_account.php
// Správa účetnictví nemovitosti: výdaje, příjmy a souhrny pro daňové podklady
// Migration:
//   ALTER TABLE property_expenses
//     ADD COLUMN depreciation_years INT DEFAULT NULL AFTER category,
//     ADD COLUMN currency CHAR(3) NOT NULL DEFAULT 'EUR' AFTER amount;

require 'inc/connect.php';
session_start();
$propertyId = intval($_GET['id'] ?? 0);
if (!$propertyId) {
    exit('Chybí ID nemovitosti');
}

// Seznam měn
$currencies = ['EUR', 'CZK', 'USD', 'GBP'];
$defaultCurrency = 'EUR';

// Zpracování formulářů
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['action']) && $_POST['action'] === 'add_expense') {
        $category = $_POST['category'];
        // Automatické odpisy
        if ($category === 'acquisition') {
            $years = 30;
        } elseif ($category === 'improvement') {
            $years = $_POST['depreciation_years'] ?: null;
        } else {
            $years = null;
        }
        $currency = in_array($_POST['currency'], $currencies) ? $_POST['currency'] : $defaultCurrency;
        $stmt = $pdo->prepare(
            'INSERT INTO property_expenses
               (property_id, date, description, amount, currency, category, depreciation_years)
             VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $propertyId,
            $_POST['date'],
            $_POST['description'],
            $_POST['amount'],
            $currency,
            $category,
            $years
        ]);
        header("Location: property_account.php?id={$propertyId}");
        exit;
    }
}

// Načtení nemovitosti
try {
    $stmt = $pdo->prepare(
        'SELECT name, address, type, number_of_rooms, number_of_beds, max_occupancy
           FROM properties WHERE property_id = ?'
    );
    $stmt->execute([$propertyId]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$property) {
        throw new Exception('Nemovitost nenalezena');
    }
} catch (Exception $e) {
    exit('Chyba: ' . $e->getMessage());
}

// Načtení výdajů
$expensesStmt = $pdo->prepare(
    'SELECT * FROM property_expenses
     WHERE property_id = ?
     ORDER BY date DESC'
);
$expensesStmt->execute([$propertyId]);
$expenses = $expensesStmt->fetchAll(PDO::FETCH_ASSOC);

// Kontrola existence acquisition
$acqStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM property_expenses
     WHERE property_id = ? AND category = 'acquisition'"
);
$acqStmt->execute([$propertyId]);
$hasAcquisition = (int)$acqStmt->fetchColumn() > 0;

// Načtení příjmů
$incomeStmt = $pdo->prepare(
    "SELECT reservation_id, date_from, date_to, total_amount
     FROM reservations
     WHERE property_id = ?
     ORDER BY date_from DESC"
);
$incomeStmt->execute([$propertyId]);
$incomes = $incomeStmt->fetchAll(PDO::FETCH_ASSOC);

// Výpočet souhrnů podle roku
$summary = [];
foreach ($expenses as $e) {
    $yr = (int)date('Y', strtotime($e['date']));
    if (!isset($summary[$yr])) {
        $summary[$yr] = ['repairs' => 0, 'depreciation' => 0, 'income' => 0];
    }
    if ($e['category'] === 'repair') {
        $summary[$yr]['repairs'] += (float)$e['amount'];
    } else {
        $years = (int)$e['depreciation_years'];
        if ($years > 0) {
            $annual = (float)$e['amount'] / $years;
            for ($i = 0; $i < $years; $i++) {
                $y = $yr + $i;
                if (!isset($summary[$y])) {
                    $summary[$y] = ['repairs' => 0, 'depreciation' => 0, 'income' => 0];
                }
                $summary[$y]['depreciation'] += $annual;
            }
        }
    }
}
foreach ($incomes as $inc) {
    $yr = (int)date('Y', strtotime($inc['date_from']));
    if (!isset($summary[$yr])) {
        $summary[$yr] = ['repairs' => 0, 'depreciation' => 0, 'income' => 0];
    }
    $summary[$yr]['income'] += (float)$inc['total_amount'];
}
ksort($summary);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Účetnictví nemovitosti: <?= htmlspecialchars($property['name']) ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin: 2em; }
    .header-info, .section { margin-bottom: 2em; }
    .header-info p { margin: .3em 0; }
    .note { background: #f9f9f9; border-left: 4px solid #ccc; padding: .5em; margin-bottom: 1em; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 1em; }
    th, td { border: 1px solid #ddd; padding: .5em; vertical-align: top; }
    th { background: #f0f0f0; }
    textarea, input[type="text"], input[type="number"], select, input[type="date"] {
      width: 100%; box-sizing: border-box; padding: .5em; margin: .3em 0;
    }
    button { padding: .5em 1em; }
  </style>
</head>
<body>
  <h1>Účetnictví nemovitosti: <?= htmlspecialchars($property['name']) ?></h1>
  <div class="header-info">
    <p><strong>Adresa:</strong> <?= nl2br(htmlspecialchars($property['address'])) ?></p>
    <p><strong>Typ:</strong> <?= htmlspecialchars($property['type']) ?></p>
    <p><strong>Pokojů:</strong> <?= htmlspecialchars($property['number_of_rooms']) ?></p>
    <p><strong>Postelí:</strong> <?= htmlspecialchars($property['number_of_beds']) ?></p>
    <p><strong>Max. obsazenost:</strong> <?= htmlspecialchars($property['max_occupancy']) ?> osob</p>
  </div>

  <div class="section" id="expenses">
    <h2>Výdaje</h2>
    <div class="note">
      <strong>Poznámka:</strong> Opravy se uplatňují jednorázově v daném roce, pořízení a vylepšení se odepisují dlouhodobě.
    </div>
    <style>
  .form-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
  }
  .form-inline label {
    display: flex;
    flex-direction: column;
    margin: 0;
    flex: 1;              /* všechna pole základem stejná šířka */
  }
  .form-inline label.description {
    flex: 3;              /* popis 3× širší než ostatní */
  }
  .form-inline textarea {
    resize: vertical;     /* umožní vertikálně zvětšit, ale ne horizontálně */
    min-height: 2.5em;
  }
</style>

<form method="post" class="form-inline">
  <input type="hidden" name="action" value="add_expense">

  <label>
    Datum
    <input type="date" name="date" required>
  </label>

  <label class="description">
    Popis
    <textarea name="description" rows="1" required></textarea>
  </label>

  <label>
    Částka
    <input type="number" step="0.01" name="amount" required>
  </label>

  <label>
    Měna
    <select name="currency">
      <?php foreach ($currencies as $c): ?>
        <option value="<?= $c ?>"<?= $c === $defaultCurrency ? ' selected' : '' ?>><?= $c ?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>
    Kategorie
    <select name="category">
      <option value="repair">Oprava</option>
      <option value="improvement">Vylepšení</option>
      <?php if (!$hasAcquisition): ?>
        <option value="acquisition">Pořízení</option>
      <?php endif; ?>
    </select>
  </label>

  <label>
    Roky odpisu
    <input type="number" name="depreciation_years" min="1" <?= $hasAcquisition ? 'disabled' : '' ?>>
  </label>

  <button type="submit">Uložit</button>
</form>

    <table>
  <thead>
    <tr>
      <th>Datum</th><th>Popis</th><th>Částka</th><th>Měna</th><th>Kategorie</th><th>Roky odpisu</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($expenses as $e): ?>
    <tr>
      <td><?= htmlspecialchars($e['date'] ?? '') ?></td>
      <td><?= nl2br(htmlspecialchars($e['description'] ?? '')) ?></td>
      <td><?= number_format($e['amount'] ?? 0, 2, ',', ' ') ?></td>
      <td><?= htmlspecialchars($e['currency'] ?? '') ?></td>
      <td><?= htmlspecialchars($e['category'] ?? '') ?></td>
      <td><?= htmlspecialchars($e['depreciation_years'] ?? '') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

  </div>

  <div class="section" id="incomes">
    <h2>Příjmy z pronájmu</h2>
    <!-- Příjmy sekce může být přidána podobně jako výdaje -->
  </div>

  <div class="section" id="summary">
    <h2>Daňový souhrn podle roku</h2>
    <table>
      <thead>
        <tr><th>Rok</th><th>Příjmy</th><th>Opravy</th><th>Odpisy</th><th>Výsledek</th><th>Měna</th></tr>
      </thead>
      <tbody>
      <?php foreach ($summary as $yr => $data): ?>
        <tr>
          <td><?= $yr ?></td>
          <td><?= number_format($data['income'], 2, ',', ' ') ?></td>
          <td><?= number_format($data['repairs'], 2, ',', ' ') ?></td>
          <td><?= number_format($data['depreciation'], 2, ',', ' ') ?></td>
          <td><?= number_format($data['income'] - $data['repairs'] - $data['depreciation'], 2, ',', ' ') ?></td>
          <td><?= $defaultCurrency ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
