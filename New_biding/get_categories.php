<?php
header('Content-Type: application/json');
require_once "db_connect.php"; // <-- use your DB connection file

try {
    $stmt = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
} catch (Exception $e) {
    echo json_encode([]);
}
?>
