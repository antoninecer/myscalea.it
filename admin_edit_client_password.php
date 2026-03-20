<?php
session_start();
require_once 'inc/connect.php';

// Admin-only access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo '<h1>403 Forbidden</h1><p>Access denied.</p>';
    exit;
}

// Initialize messages
$error   = '';
$success = false;

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'], $_POST['password'])) {
    $clientId = (int) $_POST['client_id'];
    $newPass  = trim($_POST['password']);

    // Basic validation
    if (strlen($newPass) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Hash and update
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE clients SET password_hash = ? WHERE id = ?");
        if ($stmt->execute([$hash, $clientId])) {
            $success = true;
        } else {
            $error = 'Failed to update password. Please try again.';
        }
    }
}

// If editing a specific client
$editingClient = null;
if (isset($_GET['id'])) {
    $id   = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT id, name, email FROM clients WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $editingClient = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all clients for list view
$allClients = [];
if (!$editingClient) {
    $stmt = $pdo->query("SELECT id, name, email FROM clients ORDER BY name ASC");
    $allClients = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<section class="section" style="max-width:600px;margin:2rem auto;">
    <h1>Edit Client Password</h1>

    <?php if ($error): ?>
        <div style="padding:1rem;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;margin-bottom:1rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php elseif ($success): ?>
        <div style="padding:1rem;background:#d4edda;color:#155724;border:1px solid #c3e6cb;margin-bottom:1rem;">
            Password updated successfully.
        </div>
    <?php endif; ?>

    <?php if ($editingClient): ?>
        <form method="POST" action="admin_edit_client_password.php?id=<?= $editingClient['id'] ?>" style="background:#f9f9f9;padding:1rem;border-radius:8px;">
            <input type="hidden" name="client_id" value="<?= $editingClient['id'] ?>">
            <p><strong>Name:</strong> <?= htmlspecialchars($editingClient['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($editingClient['email']) ?></p>

            <label for="password">New Password *</label>
            <input type="password" name="password" id="password" required minlength="6" style="width:100%;padding:0.5rem;margin-bottom:1rem;">

            <button type="submit" style="padding:0.7rem 1.5rem;background:#007bff;color:#fff;border:none;border-radius:5px;cursor:pointer;">
                Update Password
            </button>
            <a href="admin_edit_client_password.php" style="margin-left:1rem;">← Back to list</a>
        </form>

    <?php else: ?>
        <h2>All Clients</h2>
        <?php if ($allClients): ?>
            <ul style="list-style:none;padding:0;">
                <?php foreach ($allClients as $client): ?>
                    <li style="margin-bottom:0.5rem;">
                        <?= htmlspecialchars($client['name']) ?> (<?= htmlspecialchars($client['email']) ?>)
                        — <a href="admin_edit_client_password.php?id=<?= $client['id'] ?>">Edit Password</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No clients found.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>

