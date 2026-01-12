<?php
session_start();
header('Content-Type: application/json');

// DB connect
$conn = new mysqli("localhost", "root", "", "auction_system");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'DB connection failed']);
    exit;
}

// Validate auction ID
$auctionId = isset($_GET['auction_id']) ? intval($_GET['auction_id']) : 0;
if (!$auctionId) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid auction ID']);
    exit;
}

// Fetch latest 50 bids
$sql = "SELECT amount, created_at FROM bids WHERE auction_id = ? ORDER BY created_at DESC LIMIT 50";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $auctionId);
$stmt->execute();
$res = $stmt->get_result();
$bids = $res->fetch_all(MYSQLI_ASSOC);

// Return JSON
echo json_encode(['success'=>true, 'bids'=>$bids]);

$stmt->close();
$conn->close();
?>
