<?php
require 'admin_session.php';
require '../db_connect.php';

/* ---------- BASIC COUNTS ---------- */

// Total users (personal + business)
$totalUsers =
    $conn->query("SELECT COUNT(*) c FROM personal_users")->fetch_assoc()['c']
  + $conn->query("SELECT COUNT(*) c FROM business_users")->fetch_assoc()['c'];

// Total listings
$totalListings = $conn->query(
    "SELECT COUNT(*) c FROM listings"
)->fetch_assoc()['c'];

// Active auctions
$activeAuctions = $conn->query(
    "SELECT COUNT(*) c FROM listings WHERE end_date > NOW()"
)->fetch_assoc()['c'];

// Completed auctions
$completedAuctions = $conn->query(
    "SELECT COUNT(*) c FROM listings WHERE end_date <= NOW()"
)->fetch_assoc()['c'];

/* ---------- GRAPH DATA ---------- */

// Auctions per category
$categoryStats = $conn->query("
    SELECT c.category_name, COUNT(l.id) AS total
    FROM categories c
    LEFT JOIN listings l ON c.category_id = l.category_id
    GROUP BY c.category_id
");

$catLabels = [];
$catCounts = [];
while ($row = $categoryStats->fetch_assoc()) {
    $catLabels[] = $row['category_name'];
    $catCounts[] = $row['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {font-family:Arial;background:#f2f3f5;margin:0;}
header{
 background:#007bff;color:white;padding:15px 20px;
 display:flex;justify-content:space-between;align-items:center;
}
header a{
 background:#0056b3;color:white;
 padding:6px 12px;border-radius:6px;text-decoration:none;
}
.container{width:95%;max-width:1200px;margin:20px auto;}
.cards{
 display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
 gap:20px;
}
.card{
 background:white;padding:20px;border-radius:8px;
 box-shadow:0 2px 6px rgba(0,0,0,0.1);
}
.card h3{margin:0;color:#007bff;}
.graph-box{
 background:white;margin-top:30px;
 padding:20px;border-radius:8px;
 box-shadow:0 2px 6px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<header>
  <h2>Admin Dashboard</h2>
  <a href="admin_logout.php">Logout</a>
</header>

<div class="container">

<div class="cards">
  <div class="card"><h3>Total Users</h3><p><?= $totalUsers ?></p></div>
  <div class="card"><h3>Total Listings</h3><p><?= $totalListings ?></p></div>
  <div class="card"><h3>Active Auctions</h3><p><?= $activeAuctions ?></p></div>
  <div class="card"><h3>Completed Auctions</h3><p><?= $completedAuctions ?></p></div>
</div>

<div class="graph-box">
  <h3>Listings by Category</h3>
  <canvas id="categoryChart"></canvas>
</div>

</div>

<script>
new Chart(document.getElementById('categoryChart'),{
 type:'bar',
 data:{
   labels: <?= json_encode($catLabels) ?>,
   datasets:[{
     label:'Listings',
     data: <?= json_encode($catCounts) ?>,
     backgroundColor:'#36A2EB'
   }]
 },
 options:{responsive:true}
});
</script>

</body>
</html>
