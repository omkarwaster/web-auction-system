<?php
session_start();
$isLoggedIn = isset($_SESSION['user_email']);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auction_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Get category ID from URL
$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch category name
$catStmt = $conn->prepare("SELECT category_name FROM categories WHERE category_id = ?");
$catStmt->bind_param("i", $categoryId);
$catStmt->execute();
$catResult = $catStmt->get_result();
$categoryName = $catResult->num_rows > 0 ? $catResult->fetch_assoc()['category_name'] : "Unknown";
$catStmt->close();

// Fetch listings for this category
$stmt = $conn->prepare("
    SELECT id, title, description, image_path, base_price
    FROM listings
    WHERE category_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($categoryName); ?> - WebAuction</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body { margin:0; font-family:Segoe UI,Arial,sans-serif; background:#f4f6f9; color:#333; }
    main { padding:30px; max-width:1300px; margin:0 auto; }
    h1 { font-size:26px; margin-bottom:25px; text-align:left; color:#222; }

    .grid {
      display:grid;
      grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));
      gap:25px;
    }
    .listing-card {
      background:#fff;
      border:1px solid #e0e0e0;
      border-radius:10px;
      overflow:hidden;
      box-shadow:0 2px 6px rgba(0,0,0,0.08);
      transition:all 0.2s ease;
      display:flex;
      flex-direction:column;
      height:280px;
    }
    .listing-card:hover { transform:translateY(-5px); box-shadow:0 6px 15px rgba(0,0,0,0.15); }
    .listing-card img { width:100%; height:160px; object-fit:cover; }
    .listing-body { padding:10px; flex:1; display:flex; flex-direction:column; justify-content:space-between; }
    .listing-title { font-size:14px; font-weight:500; color:#222; margin-bottom:8px; line-height:1.3em; height:36px; overflow:hidden; }
    .listing-price { color:#007BFF; font-size:15px; font-weight:bold; }
  </style>
</head>
<body>

<!-- ✅ Header same as explore_items.php -->
<header style="background:#fff; color:#333; padding:15px 25px; box-shadow:0 2px 5px rgba(0,0,0,0.1); position:sticky; top:0; z-index:1000;">
  <div style="display:flex; align-items:center; justify-content:space-between; max-width:1200px; margin:0 auto;">
    <div style="font-size:22px; font-weight:bold; color:#007BFF;">
      <a href="index.php" style="color:#007BFF; text-decoration:none;">WebAuction</a>
    </div>
    <nav style="display:flex; gap:25px; font-size:16px;">
      <a href="explore_items.php" style="color:#333; text-decoration:none;">Explore</a>
      <a href="about.php" style="color:#333; text-decoration:none;">About</a>
      <a href="contact.php" style="color:#333; text-decoration:none;">Contact</a>
    </nav>
    <div style="display:flex; align-items:center; gap:15px; position:relative;">
      <div style="position:relative; flex:0 0 300px;">
        <input type="text" placeholder="Search auctions..." style="width:100%; padding:8px 12px; border-radius:20px; border:1px solid #ccc; outline:none;">
      </div>
      <div style="position:relative;">
        <i class="fas fa-bars" id="hamburger-icon" style="font-size:22px; cursor:pointer; color:#333;"></i>
      </div>
    </div>
  </div>
</header>

<main>
  <h1>All Listings in <?php echo htmlspecialchars($categoryName); ?></h1>

  <?php if (!empty($listings)): ?>
    <div class="grid">
      <?php foreach ($listings as $listing): ?>
        <div class="listing-card" onclick="window.location='auction.php?id=<?php echo $listing['id']; ?>'">
          <img src="<?php echo htmlspecialchars($listing['image_path']); ?>" alt="Listing Image">
          <div class="listing-body">
            <div class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></div>
            <div class="listing-price">₹<?php echo number_format($listing['base_price'], 2); ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p>No listings available in this category.</p>
  <?php endif; ?>
</main>

</body>
</html>
