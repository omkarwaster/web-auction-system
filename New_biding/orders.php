<?php
session_start();
require 'db_connect.php'; // your DB connection file

// ✅ Ensure user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['accountType'])) {
    die("Please log in first.");
}

$userEmail = $_SESSION['email'];
$accountType = $_SESSION['accountType'];

// ✅ Fetch user ID from users table
$stmtUser = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
$stmtUser->bind_param("s", $userEmail);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
if ($rowUser = $resultUser->fetch_assoc()) {
    $userId = $rowUser['id'];
    $userName = $rowUser['name'];
} else {
    die("User not found.");
}
$stmtUser->close();

// ✅ Fetch purchases (items user has won as buyer)
$sql = "
SELECT 
    l.id AS auction_id,
    l.title,
    l.category_id,
    c.category_name,
    b.user_id AS buyer_id,
    b.amount AS final_price,
    b.created_at AS bought_at,
    (SELECT MAX(b2.amount) FROM bids b2 WHERE b2.auction_id = l.id) AS current_highest
FROM listings l
JOIN categories c ON l.category_id = c.category_id
JOIN bids b ON l.id = b.auction_id
WHERE b.user_id = ? 
AND b.amount = (
    SELECT MAX(b2.amount) FROM bids b2 WHERE b2.auction_id = l.id
)
ORDER BY b.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$purchases = [];
$itemsBoughtData = [];
$totalSpentData = [];

while ($row = $result->fetch_assoc()) {
    $purchases[] = $row;
    // For charts aggregation
    $cat = $row['category_name'];
    if (!isset($itemsBoughtData[$cat])) {
        $itemsBoughtData[$cat] = 0;
        $totalSpentData[$cat] = 0;
    }
    $itemsBoughtData[$cat]++;
    $totalSpentData[$cat] += $row['final_price'];
}

$chartCategories = array_keys($itemsBoughtData);
$itemsBought = array_values($itemsBoughtData);
$totalSpent = array_values($totalSpentData);

$stmt->close();

// Fetch products user has bid on (not necessarily won)
$sql2 = "
SELECT DISTINCT 
    l.id AS auction_id,
    l.title,
    l.category_id,
    c.category_name,
    MAX(b.amount) AS highest_bid,
    MAX(b.created_at) AS last_bid_time,
    (SELECT MAX(b2.amount) FROM bids b2 WHERE b2.auction_id = l.id) AS current_highest
FROM listings l
JOIN categories c ON l.category_id = c.category_id
JOIN bids b ON l.id = b.auction_id
WHERE b.user_id = ?
GROUP BY l.id, l.title, l.category_id, c.category_name
ORDER BY last_bid_time DESC
";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $userId);
$stmt2->execute();
$result2 = $stmt2->get_result();

$biddedProducts = [];
while ($row = $result2->fetch_assoc()) {
    $biddedProducts[] = $row;
}

$stmt2->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Orders - Auction System</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family: Arial, sans-serif; margin:0; background:#f2f3f5; color:#111; }
header { background:#007bff; color:#fff; padding:15px 20px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 6px rgba(0,0,0,0.2);}
header h1 { margin:0; font-size:22px;}
header a { color:#fff; text-decoration:none; font-weight:bold; font-size:14px; background:#0056b3; padding:6px 12px; border-radius:6px; transition:0.3s;}
header a:hover { background:#00408f;}
.container { width:95%; max-width:1200px; margin:20px auto; }
.greeting { margin-bottom:20px; background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);}
.greeting h2 { margin:0 0 5px; font-size:20px; color:#333;}
.greeting p { font-size:14px; color:#666;}
.cards { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px,1fr)); gap:20px; }
.card { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); transition:0.3s;}
.card h3 { margin-top:0; font-size:18px; color:#007bff;}
.card p { font-size:14px; color:#555; margin:8px 0 16px;}
.card button { background:#0056b3; border:none; border-radius:8px; padding:8px 15px; cursor:pointer; font-weight:bold; font-size:14px; color:#fff; transition:background 0.3s;}
.card button:hover { background:#00408f;}
table { width:100%; border-collapse: collapse; margin-top:20px; }
table th, table td { border:1px solid #ccc; padding:8px; text-align:center; font-size:14px; }
table th { background:#007bff; color:#fff; }
canvas { margin-top:15px; }
.chart-section { display:flex; flex-wrap:wrap; gap:20px; }
.chart-card { flex:1 1 500px; background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);}

/* Dark Theme */
body.dark { background: #181818; color: #f5f5f5; }
body.dark header { background: #0754d7ff; }
body.dark header a { background: #2d5eb1ff; color: #f5f5f5; }
body.dark header a:hover { background: #3182ce; }
body.dark .container,
body.dark .greeting,
body.dark .card,
body.dark .chart-card { background: #242424; color: #f5f5f5; border: 1px solid #444; }
body.dark h2, body.dark h3 { color: #f5f5f5; }
body.dark table { background:#242424; border-color:#444; }
body.dark table th { background:#3182ce; color:#fff; }
body.dark table td { border-color:#444; color:#ddd; }
body.dark button { background:#3182ce; color:#fff; }
body.dark button:hover { background:#2563eb; }
</style>
</head>
<body>

<header>
<h1><a href='index.php'>WebAuction</a></h1>
<div>
  <a href="user_profile.php">Back to Profile</a>
</div>
</header>

<div class="container">
  <div class="greeting">
    <h2>Hello, <?php echo htmlspecialchars($userName); ?></h2>
    <p>Here you can view your purchases and your bidding activity.</p>
  </div>

  <div class="cards">
    <div class="card">
      <h3>View Purchased Items</h3>
      <p>Check the products you have successfully bought.</p>
      <button onclick="document.getElementById('purchased').scrollIntoView({behavior:'smooth'})">Go to Purchases</button>
    </div>
    <div class="card">
      <h3>View Bidded Products</h3>
      <p>See all the products you have placed bids on.</p>
      <button onclick="document.getElementById('bidded').scrollIntoView({behavior:'smooth'})">Go to Bidded Products</button>
    </div>
  </div>
</div>

<div class="container" id="purchased">
  <h2>📦 Purchased Items</h2>
  <?php if (empty($purchases)): ?>
    <div class="card">
        <h3>No Purchases Yet</h3>
        <p>You haven't bought any items yet. Explore auctions and place your first bid!</p>
        <button onclick="location.href='explore_items.php'">Explore Auctions</button>
    </div>
  <?php else: ?>
    <div class="chart-section">
        <div class="chart-card">
            <h3>📊 Purchases Overview by Category</h3>
            <canvas id="mainChart"></canvas>
            <script>
            const categories = <?php echo json_encode($chartCategories); ?>;
            const itemsBought = <?php echo json_encode($itemsBought); ?>;
            const totalSpent = <?php echo json_encode($totalSpent); ?>;
            new Chart(document.getElementById('mainChart'), {
                type: 'bar',
                data: {
                    labels: categories,
                    datasets: [
                        { label:'Items Bought', data: itemsBought, backgroundColor:'rgba(54,162,235,0.7)'},
                        { label:'Total Spent (₹)', data: totalSpent, backgroundColor:'rgba(255,99,132,0.7)'}
                    ]
                },
                options: { responsive:true, plugins:{ legend:{ position:'top' } } }
            });
            </script>
        </div>

        <div class="chart-card">
            <h3>🛒 Purchases Table</h3>
            <table>
                <thead>
                    <tr>
                        <th>Auction ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Current Highest Bid (₹)</th>
                        <th>Buyer ID</th>
                        <th>Price Paid (₹)</th>
                        <th>Bought At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($purchases as $p): ?>
                    <tr>
                        <td><?php echo $p['auction_id']; ?></td>
                        <td><a href="auction.php?id=<?php echo $p['auction_id']; ?>"><?php echo htmlspecialchars($p['title']); ?></a></td>
                        <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                        <td><?php echo number_format($p['current_highest'],2); ?></td>
                        <td><?php echo $p['buyer_id']; ?></td>
                        <td><?php echo number_format($p['final_price'],2); ?></td>
                        <td><?php echo $p['bought_at']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
  <?php endif; ?>
</div>

<div class="container" id="bidded">
  <h2>🎯 Bidded Products</h2>
  <?php if (empty($biddedProducts)): ?>
    <div class="card">
        <h3>No Bids Yet</h3>
        <p>You haven't placed any bids yet. Start bidding now!</p>
        <button onclick="location.href='explore_items.php'">Explore Auctions</button>
    </div>
  <?php else: ?>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Auction ID</th>
                    <th>Title</th>
                    <th>Category</th>s
                    <th>Current Highest Bid (₹)</th>
                    <th>Your Highest Bid (₹)</th>
                    <th>Last Bid Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($biddedProducts as $bp): ?>
                <tr>
                    <td><?php echo $bp['auction_id']; ?></td>
                    <td><a href="auction.php?id=<?php echo $bp['auction_id']; ?>"><?php echo htmlspecialchars($bp['title']); ?></a></td>
                    <td><?php echo htmlspecialchars($bp['category_name']); ?></td>
                    <td><?php echo number_format($bp['current_highest'],2); ?></td>
                    <td><?php echo number_format($bp['highest_bid'],2); ?></td>
                    <td><?php echo $bp['last_bid_time']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
  <?php endif; ?>
</div>

<script>
// Apply saved theme
const savedTheme = localStorage.getItem("theme") || "light";
if (savedTheme === "dark") {
    document.body.classList.add("dark");
}

// Toggle theme
function toggleTheme() {
    document.body.classList.toggle("dark");
    const newTheme = document.body.classList.contains("dark") ? "dark" : "light";
    localStorage.setItem("theme", newTheme);
}
</script>

</body>
</html>

