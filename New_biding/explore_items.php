<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auction_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Consistent session handling (same as index.php)
$isLoggedIn = isset($_SESSION['email']);
$userEmail = $isLoggedIn ? $_SESSION['email'] : null;
$accountType = $isLoggedIn ? $_SESSION['accountType'] : null;

// Fetch categories
$categories = [];
$catResult = $conn->query("SELECT category_id, category_name FROM categories");
if ($catResult && $catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}


// Fetch listings for each category
$categoryListings = [];
foreach ($categories as $category) {
    $stmt = $conn->prepare("
        SELECT l.id, l.title, l.description, l.image_path, l.base_price
        FROM listings l
        JOIN categories c ON l.category_id = c.category_id
        WHERE c.category_name = ?
        ORDER BY l.created_at DESC
        LIMIT 30
    ");
    $stmt->bind_param("s", $category['category_name']);
    $stmt->execute();
    $result = $stmt->get_result();
    $categoryListings[$category['category_name']] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Explore Items - WebAuction</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"/>
  <style>
    body { margin:0; font-family:Segoe UI,Arial,sans-serif; background:#f4f6f9; color:#333; }
    main { padding:30px; max-width:1300px; margin:0 auto; }
    h1 { font-size:28px; margin-bottom:30px; text-align:center; color:#222; }

    .category { margin-bottom:60px; padding:25px; background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
    .category h2 { font-size:20px; margin-bottom:20px; border-left:4px solid #007BFF; padding-left:12px; color:#007BFF; }

    /* Listing Cards (Amazon/eBay style) */
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
      width:200px;
      margin:auto;
    }
    .listing-card:hover { transform:translateY(-5px); box-shadow:0 6px 15px rgba(0,0,0,0.15); }

    .listing-card img {
      width:100%;
      height:160px;
      object-fit:cover;
    }

    .listing-body {
      padding:10px;
      display:flex;
      flex-direction:column;
      justify-content:space-between;
      flex:1;
    }

    .listing-title {
      font-size:14px;
      font-weight:500;
      color:#222;
      margin-bottom:8px;
      line-height:1.3em;
      height:36px;
      overflow:hidden;
    }
    .listing-price {
      color:#007BFF;
      font-size:15px;
      font-weight:bold;
    }

    /* Swiper custom */
    .swiper {
      padding:20px 10px;
    }
    .swiper-button-next, .swiper-button-prev {
      color:#007BFF;
      font-weight:bold;
    }
  </style>
</head>
<body>

<!-- ✅ Header same as index.php -->
<header style="background:#fff; color:#333; padding:15px 25px; box-shadow:0 2px 5px rgba(0,0,0,0.1); position:sticky; top:0; z-index:1000;">
  <div style="display:flex; align-items:center; justify-content:space-between; max-width:1200px; margin:0 auto;">
    <div style="font-size:22px; font-weight:bold; color:#007BFF;">
      <a href="index.php" style="color:#007BFF; text-decoration:none;">WebAuction</a>
    </div>
    <nav style="display:flex; gap:25px; font-size:16px;">
      <a href="explore_items.php" style="color:#007BFF; text-decoration:none; border-bottom:2px solid #007BFF; padding-bottom:2px;">Explore</a>
      <a href="about.html" style="color:#333; text-decoration:none;">About</a>
      <a href="contact.html" style="color:#333; text-decoration:none;">Contact</a>
    </nav>
    <div style="display:flex; align-items:center; gap:30px; position:relative;">
      <div style="position:relative; flex:0 0 300px; margin-right:20px;">
        <input type="text" id="search-input" placeholder="Search auctions..." autocomplete="off"
          style="width:100%; padding:8px 12px; border-radius:20px; border:1px solid #ccc; outline:none;">
        <div id="suggestions"
          style="position:absolute; background:#fff; color:#333; width:100%; border:1px solid #ddd; border-radius:6px; display:none; margin-top:5px; z-index:2000;"></div>
      </div>
      <div style="position:relative;">
        <i class="fas fa-bars" id="hamburger-icon" style="font-size:22px; cursor:pointer; color:#333;"></i>
        <div id="hamburger-menu"
          style="display:none; position:absolute; top:35px; right:0; background:#fff; color:#333; border:1px solid #ddd; border-radius:8px; min-width:180px; box-shadow:0 4px 10px rgba(0,0,0,0.1); overflow:hidden;">
          <?php if ($isLoggedIn): ?>
            <div onclick="window.location='index.php'" style="padding:12px 15px; cursor:pointer;">Home</div>
            <div onclick="window.location='explore_items.php'" style="padding:12px 15px; cursor:pointer; background:#f0f0f0;">Explore</div>
            <div onclick="window.location='logout.php'" style="padding:12px 15px; cursor:pointer;">Logout</div>
          <?php else: ?>
            <div onclick="window.location='login.html'" style="padding:12px 15px; cursor:pointer;">Login</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</header>

<main>
  <h1>Explore Auction Categories</h1>

  <?php foreach ($categories as $category): ?>
    <div class="category">
      <h2><?php echo htmlspecialchars($category['category_name']); ?></h2>

      <?php if (!empty($categoryListings[$category['category_name']])): ?>
        <div class="swiper mySwiper-<?php echo $category['category_id']; ?>">
          <div class="swiper-wrapper">
            <?php foreach ($categoryListings[$category['category_name']] as $listing): ?>
              <div class="swiper-slide">
                <div class="listing-card" onclick="window.location='auction.php?id=<?php echo $listing['id']; ?>'">
                  <img src="<?php echo htmlspecialchars($listing['image_path']); ?>" alt="Listing Image">
                  <div class="listing-body">
                    <div class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></div>
                    <div class="listing-price">₹<?php echo number_format($listing['base_price'], 2); ?></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="swiper-button-next"></div>
          <div class="swiper-button-prev"></div>
        </div>
        <!-- ✅ See all link -->
        <div style="text-align:right; margin-top:10px;">
          <a href="category.php?id=<?php echo $category['category_id']; ?>"
             style="color:#007BFF; text-decoration:none; font-size:14px; font-weight:bold;">
             See all in <?php echo htmlspecialchars($category['category_name']); ?> →
          </a>
        </div>
      <?php else: ?>
        <p>No listings available in this category.</p>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
  // Initialize swipers per category
  <?php foreach ($categories as $category): ?>
  new Swiper(".mySwiper-<?php echo $category['category_id']; ?>", {
    slidesPerView: 3,
    spaceBetween: 25,
    slidesPerGroup: 3,
    navigation: {
      nextEl: ".mySwiper-<?php echo $category['category_id']; ?> .swiper-button-next",
      prevEl: ".mySwiper-<?php echo $category['category_id']; ?> .swiper-button-prev",
    },
    breakpoints: {
      0: { slidesPerView: 1, slidesPerGroup: 1 },
      600: { slidesPerView: 2, slidesPerGroup: 2 },
      900: { slidesPerView: 3, slidesPerGroup: 3 }
    }
  });
  <?php endforeach; ?>

  // Hamburger toggle
  const hamburgerIcon = document.getElementById('hamburger-icon');
  const hamburgerMenu = document.getElementById('hamburger-menu');
  hamburgerIcon.addEventListener('click', () => {
    hamburgerMenu.style.display = (hamburgerMenu.style.display === 'block') ? 'none' : 'block';
  });
</script>

</body>
</html>
