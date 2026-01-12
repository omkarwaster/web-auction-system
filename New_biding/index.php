<?php
session_start();

// Database connection
$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$dbname = "auction_system"; 

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch latest 9 auctions for Featured section
$sql = "SELECT * FROM listings ORDER BY created_at DESC LIMIT 9";
$result = $conn->query($sql);

// Determine login state
$isLoggedIn = isset($_SESSION['email']);
$userEmail = $isLoggedIn ? $_SESSION['email'] : null;
$accountType = $isLoggedIn ? $_SESSION['accountType'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Web Auction System</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
</head>
<body>
 <link rel="stylesheet" href="style.css" />
<!-- ================= HEADER ================= -->
<header class="main-header">
  <div class="container">
    <!-- Logo -->
    <div class="logo">
  <a href="index.php" class="logo-link">
    <!--<img src="logo.png" alt="WebAuction Logo" class="logo-img">-->
    <span>WebAuction</span>
  </a>
  <?php if($isLoggedIn): ?>
    <span class="welcome-text">
      Welcome, <?php echo htmlspecialchars($userEmail); ?>
    </span>
  <?php endif; ?>
</div>

    <!-- Navigation -->
    <nav class="nav-menu">
      <ul>
        <li><a href="explore_items.php<?php echo $isLoggedIn ? '?session=active' : ''; ?>">Explore</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>
    </nav>

    <!-- Right Side (Search + Hamburger) -->
    <div class="header-icons">
      <!-- Search -->
      <div class="search-container">
        <input type="text" id="search-input" placeholder="Search auctions..." autocomplete="off">
        <div id="suggestions" class="suggestions-box"></div>
      </div>

      <!-- Hamburger -->
      <div class="hamburger-container">
        <i class="fas fa-bars" id="hamburger-icon"></i>
        <div class="hamburger-menu" id="hamburger-menu">
          <?php if ($isLoggedIn): ?>
            <div onclick="window.location='user_profile.php'">Profile</div>
            <div onclick="window.location='logout.php'">Logout</div>
          <?php else: ?>
            <div onclick="window.location='login.html'">Login</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- ================= SIDE MENU ================= -->
<div id="sideMenu" class="side-menu">
  <div class="side-menu-header">
    <h3>Menu</h3>
    <button onclick="toggleMenu()" class="close-btn">&times;</button>
  </div>
  <div class="side-menu-content">
    <a href="index.php" class="menu-item">Home</a>
    <a href="explore_items.php" class="menu-item">Auctions</a>
    <a href="create_listing.php" class="menu-item">Sell</a>
    <a href="#" class="menu-item">Watchlist</a>
    <a href="#" class="menu-item">Dashboard</a>
    <hr class="menu-divider">
    <?php if (!$isLoggedIn): ?>
      <a href="login.php" class="menu-item">Login</a>
      <a href="signup.php" class="menu-item">Register</a>
    <?php else: ?>
      <a href="user_profile.php" class="menu-item">Profile</a>
      <a href="account_settings.php" class="menu-item">Settings</a>
      <a href="logout.php" class="menu-item">Logout</a>
    <?php endif; ?>
  </div>
</div>

<!-- ================= BANNER CAROUSEL ================= -->
<section class="banner-carousel">
  <div class="swiper mySwiper">
    <div class="swiper-wrapper">
      <div class="swiper-slide">
        <img src="image.png" alt="Luxury Products">
        <div class="carousel-content">
          <h2>Discover Luxury Products</h2>
          <p>Find exclusive items and rare collectibles</p>
          <button class="cta-btn">Explore Now</button>
        </div>
      </div>
      <div class="swiper-slide">
        <img src="gucci.png" alt="Luxury Watches">
        <div class="carousel-content">
          <h2>Premium Timepieces</h2>
          <p>Bid on luxury watches from top brands</p>
          <button class="cta-btn">Browse Watches</button>
        </div>
      </div>
      <div class="swiper-slide">
        <img src="shoes.jpg" alt="Designer Shoes">
        <div class="carousel-content">
          <h2>Designer Footwear</h2>
          <p>Step into style with premium shoes</p>
          <button class="cta-btn">Shop Shoes</button>
        </div>
      </div>
      <div class="swiper-slide">
        <img src="cloth.jpg" alt="Fashion Clothes">
        <div class="carousel-content">
          <h2>Fashion Collection</h2>
          <p>Haute couture and designer clothing</p>
          <button class="cta-btn">View Fashion</button>
        </div>
      </div>
    </div>
    <!-- Controls -->
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-pagination"></div>
  </div>
</section>

<!-- ================= CATEGORIES ================= -->
<section class="categories-section">
  <div class="container">
    <h2 class="section-title">Browse Categories</h2>
    <div class="categories-grid">
      <a href="category.php?cat=electronics" class="category-card">
        <div class="category-icon">📱</div>
        <h3>Electronics</h3>
        <p>Gadgets & Tech</p>
      </a>
      <a href="category.php?cat=fashion" class="category-card">
        <div class="category-icon">👗</div>
        <h3>Fashion</h3>
        <p>Clothing & Accessories</p>
      </a>
      <a href="category.php?cat=home" class="category-card">
        <div class="category-icon">🏠</div>
        <h3>Home & Living</h3>
        <p>Furniture & Decor</p>
      </a>
      <a href="category.php?cat=books" class="category-card">
        <div class="category-icon">📚</div>
        <h3>Books</h3>
        <p>Literature & Education</p>
      </a>
    </div>
  </div>
</section>

<!-- ================= FEATURED AUCTIONS ================= -->
<section class="featured-auctions">
  <div class="container">
    <h2 class="section-title">Featured Auctions</h2>
    <div class="swiper featuredSwiper">
      <div class="swiper-wrapper">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <div class="swiper-slide">
              <article class="auction-card">
                <div class="auction-image">
                  <img src="<?php echo htmlspecialchars($row['image_path']); ?>" 
                       alt="<?php echo htmlspecialchars($row['title']); ?>">
                  <div class="auction-badge">Live</div>
                </div>
                <div class="auction-info">
                  <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                  <div class="price-info">
                    <span class="current-bid">Starting at</span>
                    <span class="price">₹<?php echo number_format($row['base_price'], 2); ?></span>
                  </div>
                  <a href="auction.php?id=<?php echo $row['id']; ?>" class="bid-btn">Place Bid</a>
                </div>
              </article>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="no-auctions">
            <p>No auctions available at the moment.</p>
            <a href="create_listing.php" class="create-listing-btn">Create First Listing</a>
          </div>
        <?php endif; ?>
      </div>
      <!-- Controls -->
      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
    </div>
  </div>
</section>

<!-- ================= FOOTER ================= -->
<footer>
  <div class="container">
    <div class="footer-content">
      <div class="footer-section">
        <h4>WebAuction</h4>
        <p>Your trusted marketplace for unique auctions</p>
      </div>
      <div class="footer-section">
        <h4>Quick Links</h4>
        <a href="#">How it Works</a>
        <a href="terms.html">Terms of Service</a>
        <a href="privacy.html">Privacy Policy</a>
      </div>
      <div class="footer-section">
        <h4>Support</h4>
        <a href="contact.html">Contact Us</a>
        <a href="faq.html">FAQ</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 WebAuction. All rights reserved.</p>
    </div>
  </div>
</footer>

<!-- ================= SCRIPTS ================= -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
// Toggle side menu
function toggleMenu() {
  document.getElementById("sideMenu").classList.toggle("active");
}

// Initialize banner swiper
const swiper = new Swiper(".mySwiper", {
  loop: true,
  speed: 800,
  autoplay: { delay: 4000, disableOnInteraction: false },
  pagination: { el: ".swiper-pagination", clickable: true },
  navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
  effect: 'fade',
  fadeEffect: { crossFade: true }
});

// Featured auctions swiper (manual)
const featuredSwiper = new Swiper(".featuredSwiper", {
  slidesPerView: 3,
  spaceBetween: 20,
  navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
  breakpoints: {
    320: { slidesPerView: 1 },
    768: { slidesPerView: 2 },
    1024: { slidesPerView: 3 }
  }
});

// Search functionality
const searchInput = document.getElementById('search-input');
const suggestionsBox = document.getElementById('suggestions');
searchInput.addEventListener('input', async () => {
  const query = searchInput.value.trim();
  if (query.length === 0) {
    suggestionsBox.style.display='none'; suggestionsBox.innerHTML=''; return;
  }
  try {
    const res = await fetch(`search_api.php?query=${encodeURIComponent(query)}`);
    const data = await res.json();
    suggestionsBox.innerHTML = data.length === 0 
      ? '<div>No results found</div>'
      : data.map(item=>`<div onclick="window.location='listing.php?id=${item.id}'">${item.title}</div>`).join('');
    suggestionsBox.style.display='block';
  } catch(err){ console.error(err); }
});
document.addEventListener('click', e => {
  if(!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
    suggestionsBox.style.display='none';
  }
});

// Hamburger menu
const hamburgerIcon = document.getElementById('hamburger-icon');
const hamburgerMenu = document.getElementById('hamburger-menu');
hamburgerIcon.addEventListener('click', () => {
  hamburgerMenu.style.display = hamburgerMenu.style.display==='block'?'none':'block';
});
document.addEventListener('click', (e)=>{
  if(!hamburgerMenu.contains(e.target) && !hamburgerIcon.contains(e.target)){
    hamburgerMenu.style.display='none';
  }
});
</script>
</body>
</html>
