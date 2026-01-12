<?php
session_start();

// Session handling
$userId      = $_SESSION['user_id'] ?? null;
$userEmail   = $_SESSION['email'] ?? '';
$accountType = $_SESSION['accountType'] ?? '';

// DB connection
$conn = new mysqli("localhost", "root", "", "auction_system");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Auction ID check
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { die("Invalid auction ID."); }
$auctionId = intval($_GET['id']);

// Auction details (include bid_increment)
$sql = "SELECT id, title, description, image_path, base_price, bid_increment, end_date 
        FROM listings WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $auctionId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { die("Auction not found."); }
$auction = $result->fetch_assoc();

// Auction end check
$auctionEnded = (strtotime($auction['end_date']) < time());

// Get current highest bid
$sqlBid = "SELECT MAX(amount) as highest FROM bids WHERE auction_id = ?";
$stmtBid = $conn->prepare($sqlBid);
$stmtBid->bind_param("i", $auctionId);
$stmtBid->execute();
$resBid = $stmtBid->get_result();
$rowBid = $resBid->fetch_assoc();
$currentBid = $rowBid['highest'] ?? $auction['base_price'];

// Load recent bids
$sqlRecent = "SELECT amount, created_at FROM bids 
              WHERE auction_id = ? ORDER BY created_at DESC LIMIT 50";
$stmtRecent = $conn->prepare($sqlRecent);
$stmtRecent->bind_param("i", $auctionId);
$stmtRecent->execute();
$resRecent = $stmtRecent->get_result();
$recentBids = $resRecent->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title><?php echo htmlspecialchars($auction['title']); ?> - Auction</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: #f8fafc; margin: 0; color: #2d3748; line-height: 1.6; }
    header { background: #fff; padding: 12px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
    .logo { font-size: 1.5rem; font-weight: 700; color: #007bff; text-decoration: none; }
    #hamburger-container { position: relative; }
    #hamburger-toggle { background: transparent; border: none; font-size: 20px; padding: 8px; cursor: pointer; border-radius: 6px; }
    .hamburger-menu { position: absolute; top: 42px; right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); min-width: 180px; display: none; z-index: 1001; overflow: hidden; }
    .hamburger-menu.open { display: block; }
    .hamburger-menu div { padding: 12px 14px; cursor: pointer; font-weight: 500; color: #2d3748; }
    .hamburger-menu div:hover { background: #f8fafc; color: #007bff; }

    .container { max-width: 1200px; margin: 24px auto; padding: 0 20px; display: flex; gap: 50px; align-items: flex-start; }
    .left-side { flex: 1; }
    .product-image img { width: 100%; max-width: 520px; border-radius: 8px; border: 1px solid #e2e8f0; display: block; }

    .bid-history { margin-top: 20px; background: #fff; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .bid-entry { font-size: 0.9rem; color: #2d3748; margin-bottom: 6px; }

    .product-details { flex: 1.4; display: flex; flex-direction: column; gap: 18px; }
    .product-title { font-size: 1.7rem; font-weight: 700; color: #1a202c; }
    .price-box { background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; max-width: 420px; }
    .price-label { font-size: 0.9rem; color: #4a5568; }
    .price { font-size: 1.6rem; color: #007bff; font-weight: 700; margin-top: 6px; }
    .desc { font-size: 1rem; color: #4a5568; white-space: pre-wrap; }
    .meta { font-size: 0.92rem; color: #718096; }
    .countdown { font-weight: 700; color: #e53e3e; }

    .bid-section { border: 1px solid #e2e8f0; padding: 14px; border-radius: 8px; background: #fff; max-width: 420px; }
    .bid-section h3 { margin: 0 0 8px; font-size: 1.05rem; }
    .bid-section input[type="number"] { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 10px; font-size: 1rem; }
    .bid-btn { width: 100%; background: #007bff; color: #fff; padding: 12px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: transform .12s ease, box-shadow .12s ease; }
    .bid-btn:hover { background: #0056b3; transform: translateY(-2px); }

    .alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; }
    .alert.success { background: #e6ffed; color: #256029; border: 1px solid #a3e6b1; }
    .alert.error { background: #ffe6e6; color: #7a0000; border: 1px solid #f5a5a5; }

    @media (max-width: 900px) {
      .container { flex-direction: column; gap: 18px; }
      .product-image img { max-width: 100%; }
      .product-details { width: 100%; }
    }
  </style>
</head>
<body>
  <header>
    <a class="logo" href="index.php">WebAuction</a>
    <div id="hamburger-container">
      <button id="hamburger-toggle"><i class="fas fa-bars"></i></button>
      <nav id="hamburger-menu" class="hamburger-menu">
        <div onclick="window.location='user_profile.php'">Profile</div>
        <div onclick="window.location='explore_items.php'">Explore</div>
      </nav>
    </div>
  </header>

  <main class="container">
    <!-- LEFT SIDE -->
    <div class="left-side">
      <div class="product-image">
        <img src="<?php echo htmlspecialchars($auction['image_path']); ?>" alt="Auction Image">
      </div>
      <div class="bid-history">
        <h3>Recent Bids</h3>
        <div id="bidList">
          <?php
            if (count($recentBids) === 0) {
              echo '<div class="bid-entry">No bids yet. Be the first!</div>';
            } else {
              foreach ($recentBids as $b) {
                $amt = number_format($b['amount'],2);
                $time = date("Y-m-d H:i:s", strtotime($b['created_at']));
                echo "<div class=\"bid-entry\">₹{$amt} — {$time}</div>";
              }
            }
          ?>
        </div>
      </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="product-details">
      <div class="product-title"><?php echo htmlspecialchars($auction['title']); ?></div>

      <!-- Flash messages -->
      <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert success"><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
      <?php endif; ?>
      <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert error"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
      <?php endif; ?>

      <div class="price-box">
        <div class="price-label">Current Highest Bid</div>
        <div class="price">₹<?php echo number_format($currentBid, 2); ?></div>
      </div>

      <div class="desc"><?php echo nl2br(htmlspecialchars($auction['description'])); ?></div>
      <div class="meta">Auction ends on: <?php echo htmlspecialchars($auction['end_date']); ?></div>
      <div class="countdown" id="countdown"></div>
      <div class="meta" style="font-weight:600;margin-top:6px;">
        Per bid increment: ₹<?php echo number_format($auction['bid_increment'],2); ?>
      </div>

      <?php if (!$auctionEnded): ?>
        <?php if (!$userId): ?>
          <div class="bid-section">
            <h3>Place Your Bid</h3>
            <div style="padding:10px;background:#fff8f0;border-radius:6px;border:1px solid #fde2c7;color:#7a4b00;">
              You need to <a href="login.html">login</a> before placing any bids.
            </div>
          </div>
        <?php else: ?>
          <div class="bid-section">
            <h3>Place Your Bid</h3>
            <form action="bid.php" method="POST">
              <input type="hidden" name="auction_id" value="<?php echo $auctionId; ?>">
              <input type="number" name="bid_amount" placeholder="Enter your bid amount" step="0.01"
                min="<?php echo number_format($currentBid + $auction['bid_increment'], 2, '.', ''); ?>" required>
              <button type="submit" class="bid-btn">Submit Bid</button>
            </form>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div style="color:#e53e3e;font-weight:700;">This auction has ended.</div>
      <?php endif; ?>
    </div>
  </main>

  <script>
    const toggle = document.getElementById("hamburger-toggle");
    const menu = document.getElementById("hamburger-menu");
    toggle.addEventListener("click", (e) => { e.stopPropagation(); menu.classList.toggle("open"); });
    document.addEventListener("click", (e) => { if (!e.target.closest("#hamburger-container")) menu.classList.remove("open"); });

    const endDateMs = new Date("<?php echo addslashes($auction['end_date']); ?>").getTime();
    const countdownEl = document.getElementById('countdown');
    function updateCountdown() {
      const now = Date.now();
      const diff = endDateMs - now;
      if (diff <= 0) { countdownEl.textContent = 'Auction ended'; return; }
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff / 3600000) % 24);
      const m = Math.floor((diff / 60000) % 60);
      const s = Math.floor((diff / 1000) % 60);
      countdownEl.textContent = `Time left: ${d}d ${h}h ${m}m ${s}s`;
    }
    setInterval(updateCountdown, 1000); updateCountdown();
  </script>
</body>
</html>
<?php
$stmt->close();
$stmtBid->close();
$stmtRecent->close();
$conn->close();
?>
