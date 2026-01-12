<?php
require 'admin_session.php';
require '../db_connect.php';

// Add category
if (isset($_POST['add'])) {
    $name = $_POST['category_name'];
    $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
    $stmt->bind_param("s",$name);
    $stmt->execute();
}

// Delete category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM categories WHERE category_id=$id");
}

$categories = $conn->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Categories</title>
<style><?php include 'admin_style.css'; ?></style>
</head>
<body>

<header>
<h2>Manage Categories</h2>
<a href="admin_dashboard.php">⬅ Back</a>
</header>

<div class="container">

<form method="post">
<input type="text" name="category_name" placeholder="New category" required>
<button name="add">Add</button>
</form>

<table>
<tr><th>ID</th><th>Name</th><th>Action</th></tr>
<?php while($c = $categories->fetch_assoc()): ?>
<tr>
<td><?= $c['category_id'] ?></td>
<td><?= $c['category_name'] ?></td>
<td><a href="?delete=<?= $c['category_id'] ?>">🗑</a></td>
</tr>
<?php endwhile; ?>
</table>

</div>
</body>
</html>
