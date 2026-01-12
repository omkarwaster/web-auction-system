<?php
session_start();
require 'db_connect.php'; // DB connection file

// If not logged in, redirect to login page
if (!isset($_SESSION['email']) || !isset($_SESSION['accountType'])) {
    header("Location: login.html");
    exit();
}

$userEmail = $_SESSION['email'];

// Fetch actual user details from `users` table
$stmt = $conn->prepare("SELECT name, accountType FROM users WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $userName = $row['name'];  
    $accountType = $row['accountType'];  
} else {
    $userName = "User"; 
    $accountType = "Unknown";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Profile - Auction System</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f2f3f5;
      color: #111;
      transition: background 0.3s, color 0.3s;
    }
    body.dark {
      background-color: #181818;
      color: #f5f5f5;
    }

    header {
      background: #007bff;
      color: #fff;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    header h1 { margin: 0; font-size: 22px; }
    header a {
      color: #fff;
      text-decoration: none;
      font-weight: bold;
      font-size: 14px;
      background: #0056b3;
      padding: 6px 12px;
      border-radius: 6px;
      transition: 0.3s;
    }
    header a:hover { background: #00408f; }

    .container {
      width: 95%;
      max-width: 1200px;
      margin: 20px auto;
    }

    .greeting {
      margin-bottom: 20px;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      transition: background 0.3s, color 0.3s;
    }
    body.dark .greeting {
      background: #242424;
      color: #f5f5f5;
    }

    .greeting h2 { margin: 0 0 5px; font-size: 20px; color: #333; }
    body.dark .greeting h2 { color: #f5f5f5; }
    .greeting p { font-size: 14px; color: #666; }
    body.dark .greeting p { color: #aaa; }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
    }

    .card {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      transition: transform 0.2s, box-shadow 0.2s, background 0.3s, color 0.3s;
    }
    body.dark .card {
      background: #242424;
      color: #f5f5f5;
      border: 1px solid #444;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .card h3 {
      margin-top: 0;
      font-size: 18px;
      color: #007bff;
    }
    body.dark .card h3 { color: #4da3ff; }

    .card p { font-size: 14px; color: #555; margin: 8px 0 16px; }
    body.dark .card p { color: #ccc; }

    .card button {
      background: #0056b3;
      border: none;
      border-radius: 8px;
      padding: 8px 15px;
      cursor: pointer;
      font-weight: bold;
      font-size: 14px;
      color: #fff;
      transition: background 0.3s;
    }
    .card button:hover { background: #00408f; }
  </style>
</head>
<body>
  <header>
    <h1><a href='index.php'>WebAuction</a></h1>
    <a href="logout.php">Logout</a>
  </header>

  <div class="container">
    <div class="greeting">
      <h2>Hello, <?php echo htmlspecialchars($userName); ?></h2>
      <p>Welcome back to your Auction profile (<?php echo htmlspecialchars($accountType); ?> account).</p>
    </div>

    <div class="cards">
      <!-- Orders -->
      <div class="card">
        <h3>Your Orders</h3>
        <p>Track, or buy things again</p>
        <button onclick="location.href='orders.php'">View Orders</button>
      </div>

      <!-- Your Listings -->
      <div class="card">
        <h3>Your Listings</h3>
        <p>Check your active and sold products</p>
        <button onclick="location.href='my_listings.php'">Manage Listings</button>
      </div>

      <!-- Sell Product -->
      <div class="card">
        <h3>Sell More Products</h3>
        <p>Create new listings and sell easily</p>
        <button onclick="location.href='create_listing.php'">Start Selling</button>
      </div>

      <!-- Account Settings -->
      <div class="card">
        <h3>Account Settings</h3>
        <p>Change your login details or profile info</p>
        <button onclick="location.href='account_settings.php'">Edit Settings</button>
      </div>
    </div>
  </div>

<script>
// Apply theme from localStorage
document.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("theme") || "light";
  if (savedTheme === "dark") {
    document.body.classList.add("dark");
  }
});
</script>
</body>
</html>
