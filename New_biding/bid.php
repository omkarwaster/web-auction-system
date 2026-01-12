<?php
session_start();
require 'db_connect.php'; // must set $conn

// Repair/verify user_id via users table
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id && !empty($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $u = $res->fetch_assoc();
            $user_id = (int)$u['id'];
            $_SESSION['user_id'] = $user_id;
        }
        $stmt->close();
    }
}

// Redirect if not logged in
$auctionId = intval($_POST['auction_id'] ?? 0);
if (!$user_id || $auctionId <= 0) {
    $_SESSION['flash_error'] = "You must be logged in to place a bid.";
    header("Location: auction.php?id=" . $auctionId);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_error'] = "Invalid request method.";
    header("Location: auction.php?id=" . $auctionId);
    exit;
}

$bidAmount = floatval($_POST['bid_amount'] ?? 0);

// Fetch auction details
$stmt = $conn->prepare("SELECT base_price, bid_increment, end_date FROM listings WHERE id = ?");
$stmt->bind_param("i", $auctionId);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $_SESSION['flash_error'] = "Auction not found.";
    header("Location: auction.php?id=" . $auctionId);
    exit;
}
$auction = $res->fetch_assoc();
$stmt->close();

// Check auction end
if (strtotime($auction['end_date']) < time()) {
    $_SESSION['flash_error'] = "This auction has already ended.";
    header("Location: auction.php?id=" . $auctionId);
    exit;
}

// Get current highest bid
$stmt = $conn->prepare("SELECT MAX(amount) AS highest FROM bids WHERE auction_id = ?");
$stmt->bind_param("i", $auctionId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

$currentHighest = $row['highest'] ?? null;
$referencePrice = ($currentHighest !== null) ? (float)$currentHighest : (float)$auction['base_price'];
$minAllowed = $referencePrice + (float)$auction['bid_increment'];

// Validate bid
if ($bidAmount < $minAllowed) {
    $_SESSION['flash_error'] = "Your bid must be at least ₹" . number_format($minAllowed, 2);
    header("Location: auction.php?id=" . $auctionId);
    exit;
}

// Insert bid
$nowStr = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT INTO bids (auction_id, user_id, amount, created_at) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iids", $auctionId, $user_id, $bidAmount, $nowStr);
if ($stmt->execute()) {
    $_SESSION['flash_success'] = "Bid placed successfully!";
} else {
    $_SESSION['flash_error'] = "Failed to place bid.";
}
$stmt->close();

header("Location: auction.php?id=" . $auctionId);
exit;
?>
