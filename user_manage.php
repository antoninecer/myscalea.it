<?php
session_start();
require_once 'inc/connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Refresh:3; url=index.php");
    exit('Access denied. Redirecting...');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role_id = (int)$_POST['role_id'];
    $agency_id = isset($_POST['agency_id']) ? (int)$_POST['agency_id'] : null;
    $password = trim($_POST['password']);

    if ($uid > 0 && isset($_POST['update_user'])) {
        $query = "UPDATE users SET username=?, email=?, phone=?, user_role_id=?, agency_id=?";
        $params = [$username, $email, $phone, $role_id, ($role_id == 4 ? $agency_id : null)];
        if (!empty($password)) {
            $query .= ", password_hash=?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        $query .= " WHERE id=?";
        $params[] = $uid;
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
    } elseif (isset($_POST['create_user']) && !empty($password)) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password_hash, user_role_id, is_verified, agency_id) VALUES (?, ?, ?, ?, ?, 1, ?)");
        $stmt->execute([
            $username,
            $email,
            $phone,
            password_hash($password, PASSWORD_DEFAULT),
            $role_id,
            ($role_id == 4 ? $agency_id : null)
        ]);
    } elseif ($uid > 0 && isset($_POST['delete_user']) && $uid != $_SESSION['user']['id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$uid]);
    }

    header("Location: user_manage.php");
    exit;
}

$users = $pdo->query("SELECT u.*, r.name as role FROM users u LEFT JOIN user_roles r ON u.user_role_id = r.id ORDER BY u.id")->fetchAll();
$roles = $pdo->query("SELECT * FROM user_roles ORDER BY id")->fetchAll();
$agencies = $pdo->query("SELECT id, agency_name FROM agencies ORDER BY agency_name")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<header class="main-header">
  <h1>👥 User Management</h1>
  <p>Manage your platform users here</p>
</header>

<section class="section">
  <h2>➕ Create New User</h2>
  <form method="POST" style="margin-bottom: 30px;">
    <input type="text" name="username" placeholder="Username" required style="width:15%;">
    <input type="email" name="email" placeholder="Email" required style="width:20%;">
    <input type="text" name="phone" placeholder="Phone" style="width:15%;">
    <input type="password" name="password" placeholder="Password" required style="width:15%;">
    <select name="role_id" style="width:15%;">
      <?php foreach ($roles as $r): ?>
        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="agency_id" style="width:20%;">
      <option value="">— Agency (only for agents) —</option>
      <?php foreach ($agencies as $id => $name): ?>
        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" name="create_user" class="btn-mini">➕ Add</button>
  </form>

  <table style="width:100%; border-collapse:collapse; font-size: 0.95em;">
    <thead>
      <tr style="background:#f5f5f5;">
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Agency</th>
        <th>Password</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
<?php foreach ($users as $u): ?>
<tr>
  <form method="POST" style="display:flex; gap:5px; align-items:center;">
    <input type="hidden" name="id" value="<?= $u['id'] ?>">
    <td><?= $u['id'] ?></td>
    <td><input type="text" name="username" value="<?= htmlspecialchars($u['username']) ?>" <?= ($u['id'] == $_SESSION['user']['id']) ? 'readonly' : '' ?> style="width:100px;"></td>
    <td><input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" <?= ($u['id'] == $_SESSION['user']['id']) ? 'readonly' : '' ?> style="width:180px;"></td>
    <td><input type="text" name="phone" value="<?= htmlspecialchars($u['phone'] ?? '') ?>" style="width:100px;"></td>
    <td>
      <?php if ($u['id'] == $_SESSION['user']['id']): ?>
        <?= htmlspecialchars($u['role']) ?>
        <input type="hidden" name="role_id" value="<?= $u['user_role_id'] ?>">
      <?php else: ?>
        <select name="role_id">
          <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>" <?= ($r['id'] == $u['user_role_id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($r['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </td>
    <td>
      <?php if ($u['user_role_id'] == 4): ?>
        <select name="agency_id">
          <option value="">— Select —</option>
          <?php foreach ($agencies as $id => $name): ?>
            <option value="<?= $id ?>" <?= ($u['agency_id'] == $id) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
          <?php endforeach; ?>
        </select>
      <?php else: ?>
        —
      <?php endif; ?>
    </td>
    <td><input type="password" name="password" placeholder="New password" style="width:140px;"></td>
    <td style="white-space: nowrap;">
      <div class="button-group">
        <button type="submit" name="update_user" class="btn-mini">💾</button>
        <?php if ($u['id'] != $_SESSION['user']['id']): ?>
        <button type="submit" name="delete_user" class="btn-mini delete" onclick="return confirm('Really delete?')">🗑️</button>
        <?php endif; ?>
      </div>
    </td>
  </form>
</tr>
<?php endforeach; ?>
    </tbody>
  </table>
</section>

<?php include 'footer.php'; ?>

<style>
  .btn-mini {
    padding: 6px 10px;
    font-size: 0.9em;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  .btn-mini:hover {
    background: #0056b3;
  }
  .btn-mini.delete {
    background: #dc3545;
  }
  .btn-mini.delete:hover {
    background: #c82333;
  }
  .button-group {
    display: flex;
    gap: 6px;
  } 
</style>
