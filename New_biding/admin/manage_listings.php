<?php
require 'admin_session.php';
require '../db_connect.php';

// Feature toggle
if (isset($_GET['feature'])) {
    $id = (int)$_GET['feature'];
    $conn->query("UPDATE listings SET featured = 1-featured WHERE id=$id");
}

// Delete listing
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM listings WHERE id=$id");
}

$listings = $conn->query("
SELECT l.*, c.category_name 
FROM listings l
JOIN categories c ON l.category_id = c.category_id
ORDER BY l.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Listings</title>
<style>
<?php include 'admin_style.css'; ?>
</style>
</head>
<body>

<header>
  <h2>Manage Listings</h2>
  <a href="admin_dashboard.php">⬅ Back</a>
</header>

<div class="container">
<table>
<tr>
<th>ID</th><th>Title</th><th>Category</th><th>Price</th><th>Featured</th><th>Actions</th>
</tr>

<?php while($l = $listings->fetch_assoc()): ?>
<tr>
<td><?= $l['id'] ?></td>
<td><?= htmlspecialchars($l['title']) ?></td>
<td><?= $l['category_name'] ?></td>
<td>₹<?= $l['base_price'] ?></td>
<td><?= $l['featured'] ? 'Yes' : 'No' ?></td>
<td>
<a href="?feature=<?= $l['id'] ?>">⭐</a>
<a href="?delete=<?= $l['id'] ?>" onclick="return confirm('Delete listing?')">🗑</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>
