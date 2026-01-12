<?php
session_start();
require 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['accountType'])) {
    die("Please log in first.");
}

// Get user info from users table
$userEmail = $_SESSION['email'];

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

// Fetch user's listings
$sql = "SELECT l.id, l.title, l.category_id, c.category_name, l.base_price, l.end_date,
        l.seller_id, 
        (SELECT MAX(b.amount) FROM bids b WHERE b.auction_id = l.id) AS highest_bid
        FROM listings l
        JOIN categories c ON l.category_id = c.category_id
        WHERE l.seller_id = ?
        ORDER BY l.end_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$listings = [];
while ($row = $result->fetch_assoc()) {
    $listings[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Listings - WebAuction</title>
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
.card { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); margin-bottom:20px;}
.card h3 { margin-top:0; font-size:18px; color:#007bff;}
table { width:100%; border-collapse: collapse; margin-top:20px; }
table th, table td { border:1px solid #ccc; padding:8px; text-align:center; font-size:14px; }
table th { background:#007bff; color:#fff; }
button { background:#007bff; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; transition:0.3s; }
button:hover { background:#0056b3; }

/* 🌙 Dark Theme */
body.dark { background: #181818; color: #f5f5f5; }
body.dark header { background: #0754d7ff; }
body.dark header a { background: #2d5eb1ff; color: #f5f5f5; }
body.dark header a:hover { background: #3182ce; }
body.dark .container,
body.dark .greeting,
body.dark .card { background: #242424; color: #f5f5f5; border: 1px solid #444; }
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
<h1><a href="index.php">WebAuction</a></h1>
<a href="user_profile.php">Back to Profile</a>
</header>

<div class="container">
<div class="greeting">
<h2>Hello, <?php echo htmlspecialchars($userName); ?></h2>
<p>Here are your active and sold listings.</p>
</div>

<?php if (empty($listings)): ?>
<div class="card">
    <h3>No Listings Yet</h3>
    <p>You haven't created any listings. Start selling your products now!</p>
    <button onclick="location.href='create_listing.php'">Create Listing</button>
</div>
<?php else: ?>
<div class="card">
<h3>My Listings</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Category</th>
            <th>Base Price (₹)</th>
            <th>Highest Bid (₹)</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($listings as $l): ?>
        <tr>
            <td><?php echo $l['id']; ?></td>
            <td><?php echo htmlspecialchars($l['title']); ?></td>
            <td><?php echo htmlspecialchars($l['category_name']); ?></td>
            <td><?php echo number_format($l['base_price'],2); ?></td>
            <td><?php echo $l['highest_bid'] ? number_format($l['highest_bid'],2) : '-'; ?></td>
            <td><?php echo $l['end_date']; ?></td>
            <td><?php echo (strtotime($l['end_date']) > time()) ? 'Active' : 'Sold'; ?></td>
            <td>
                <button onclick="location.href='auction.php?id=<?php echo $l['id']; ?>'">View</button>
                <button onclick="location.href='edit_listing.php?id=<?php echo $l['id']; ?>'">Edit</button>
            </td>
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
</script>

</body>
</html>
