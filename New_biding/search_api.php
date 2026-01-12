<?php
// search_api.php
header('Content-Type: application/json');
require 'db.php'; // include your DB connection

if (isset($_GET['query'])) {
    $query = "%" . $_GET['query'] . "%";

    $stmt = $conn->prepare("SELECT id, title FROM listings WHERE title LIKE ? LIMIT 5");
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row;
    }

    echo json_encode($suggestions);
} else {
    echo json_encode([]);
}
