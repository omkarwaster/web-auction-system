<?php
// auction_api.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// small helper to output JSON + exit
function resp($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}

// DB connect
$host = "localhost";
$user = "root";
$pass = "";
$db   = "auction_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    resp(['success' => false, 'message' => 'DB connection failed', 'db_error' => $conn->connect_error], 500);
}

$action = $_POST['action'] ?? '';
if ($action !== 'place_bid') {
    resp(['success' => false, 'message' => 'Invalid action'], 400);
}

$auction_id = isset($_POST['auction_id']) ? intval($_POST['auction_id']) : 0;
$bid_raw    = $_POST['bid'] ?? null;

if ($auction_id <= 0 || $bid_raw === null) {
    resp(['success' => false, 'message' => 'Invalid input'], 400);
}
if (!is_numeric($bid_raw)) {
    resp(['success' => false, 'message' => 'Bid must be a numeric value'], 400);
}
$bid = floatval($bid_raw);

// ensure logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    resp(['success' => false, 'message' => 'You must be logged in to place a bid.'], 401);
}

// Load auction
$sql = "SELECT id, base_price, bid_increment, end_date FROM listings WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) resp(['success' => false, 'message' => 'DB prepare failed (auction)', 'db_error' => $conn->error], 500);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    resp(['success' => false, 'message' => 'Auction not found'], 404);
}
$auction = $res->fetch_assoc();
$stmt->close();

// check auction ended
if (strtotime($auction['end_date']) < time()) {
    resp(['success' => false, 'message' => 'Auction has already ended.'], 400);
}

// get current highest
$sql2 = "SELECT MAX(amount) AS highest FROM bids WHERE auction_id = ?";
$stmt2 = $conn->prepare($sql2);
if (!$stmt2) resp(['success' => false, 'message' => 'DB prepare failed (highest)', 'db_error' => $conn->error], 500);
$stmt2->bind_param("i", $auction_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
$row2 = $res2->fetch_assoc();
$currentHighest = $row2['highest'] ?? $auction['base_price'];
$stmt2->close();

// 5-minute rule (optional)
$sql3 = "SELECT created_at FROM bids WHERE auction_id = ? AND user_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt3 = $conn->prepare($sql3);
if (!$stmt3) resp(['success' => false, 'message' => 'DB prepare failed (last bid)', 'db_error' => $conn->error], 500);
$stmt3->bind_param("ii", $auction_id, $user_id);
$stmt3->execute();
$res3 = $stmt3->get_result();
if ($res3->num_rows > 0) {
    $last = $res3->fetch_assoc();
    $lastTs = strtotime($last['created_at']);
    if ((time() - $lastTs) < 5 * 60) {
        $remain = 5*60 - (time() - $lastTs);
        resp(['success' => false, 'message' => 'You can place another bid after ' . gmdate("i\\m s\\s", $remain) . '.'], 429);
    }
}
$stmt3->close();

// Validate amounts
$requiredMin = $currentHighest + (float)$auction['bid_increment'];
if ($bid <= $currentHighest) {
    resp(['success' => false, 'message' => 'You need to bid higher than the current highest amount.'], 400);
}
if ($bid < $requiredMin) {
    resp(['success' => false, 'message' => 'Your bid must be at least current highest + per-bid increment: ' . number_format($auction['bid_increment'], 2)], 400);
}

// Insert the bid (transactional)
$conn->begin_transaction();
$nowStr = date('Y-m-d H:i:s');
$sqlIns = "INSERT INTO bids (auction_id, user_id, amount, created_at) VALUES (?, ?, ?, ?)";
$stmtIns = $conn->prepare($sqlIns);
if (!$stmtIns) {
    $conn->rollback();
    resp(['success' => false, 'message' => 'DB prepare failed (insert)', 'db_error' => $conn->error], 500);
}
$stmtIns->bind_param("iids", $auction_id, $user_id, $bid, $nowStr);
$ok = $stmtIns->execute();
if (!$ok) {
    $err = $stmtIns->error;
    $stmtIns->close();
    $conn->rollback();
    resp(['success' => false, 'message' => 'Failed to place bid (server error).', 'db_error' => $err], 500);
}
$insertId = $conn->insert_id;
$stmtIns->close();

// fetch created_at from DB to be sure
$stmtSel = $conn->prepare("SELECT created_at FROM bids WHERE id = ?");
if ($stmtSel) {
    $stmtSel->bind_param("i", $insertId);
    $stmtSel->execute();
    $r = $stmtSel->get_result();
    $row = $r->fetch_assoc();
    $created_at = $row['created_at'] ?? $nowStr;
    $stmtSel->close();
} else {
    $created_at = $nowStr;
}

$conn->commit();

// response
resp([
    'success' => true,
    'message' => 'Bid placed successfully.',
    'new_highest' => $bid,
    'created_at' => $created_at,
    'insert_id' => $insertId
], 200);

?>
