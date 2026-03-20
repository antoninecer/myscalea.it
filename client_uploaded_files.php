<?php
session_start();
require_once 'inc/connect.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'manager'])) {
    echo "Access denied.";
    exit;
}

$stmt = $pdo->query("SELECT email, name FROM clients WHERE verified = 1");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>📁 Nahrané dokumenty klientů</h2>

<?php foreach ($clients as $client):
    $clean = preg_replace('/[^a-zA-Z]/', '', $client['email']);
    $dir = __DIR__ . "/clients/$clean";
    $web_path = "/clients/$clean";
    if (file_exists($dir)):
        $files = array_diff(scandir($dir), ['.', '..']);
?>
    <div style="margin-bottom:20px;">
        <h3><?= htmlspecialchars($client['name']) ?> (<?= htmlspecialchars($client['email']) ?>)</h3>
        <ul>
            <?php foreach ($files as $file): ?>
                <li><a href="<?= $web_path . '/' . $file ?>" target="_blank"><?= htmlspecialchars($file) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; endforeach; ?>
