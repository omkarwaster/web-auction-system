<?php
require 'admin_session.php';
require '../db_connect.php';

// Suspend user
if (isset($_GET['suspend'])) {
    $id = (int)$_GET['suspend'];
    $conn->query("UPDATE personal_users SET status='suspended' WHERE id=$id");
}

// Delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM personal_users WHERE id=$id");
}

$users = $conn->query("SELECT * FROM personal_users");
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>
<style><?php include 'admin_style.css'; ?></style>
</head>
<body>

<header>
<h2>Manage Users</h2>
<a href="admin_dashboard.php">⬅ Back</a>
</header>

<div class="container">
<table>
<tr>
<th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Actions</th>
</tr>

<?php while($u = $users->fetch_assoc()): ?>
<tr>
<td><?= $u['id'] ?></td>
<td><?= htmlspecialchars($u['name']) ?></td>
<td><?= $u['email'] ?></td>
<td><?= $u['status'] ?? 'active' ?></td>
<td>
<a href="?suspend=<?= $u['id'] ?>">⛔</a>
<a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Delete user?')">🗑</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

</body>
</html>
