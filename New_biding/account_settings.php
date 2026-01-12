<?php
session_start();
require 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

// Fetch user data from users table
$userEmail = $_SESSION['email'];

$stmt = $conn->prepare("SELECT id, name, email, accountType, created_at FROM users WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $userId = $row['id'];
    $userName = $row['name'];
    $userEmail = $row['email'];
    $accountType = $row['accountType'];
    $createdAt = $row['created_at'];
} else {
    die("User not found.");
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Account Settings - Auction System</title>
<style>
/* Same CSS, just cleaned a little */
body { font-family: Arial, sans-serif; margin: 0; background-color: #f9f9f9; color: #333; transition: background 0.3s, color 0.3s;}
body.dark { background-color: #181818; color: #f5f5f5;}
header { background: #007bff; color: #fff; padding: 15px 20px; display:flex; justify-content:space-between; align-items:center;}
header h1 { margin:0; font-size:22px;}
header a { color:#fff; text-decoration:none; font-weight:bold;}
.container { width:90%; max-width:800px; margin:30px auto; background:#fff; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.1); padding:25px; transition: background 0.3s;}
body.dark .container { background:#242424; }
h2 { margin-bottom:25px; font-size:22px; color:#007bff; text-align:center; }
.setting-card { border:1px solid #ddd; border-radius:8px; padding:18px 20px; margin:18px 0; display:flex; justify-content:space-between; align-items:center; background:#fafafa; cursor:pointer; transition: background 0.3s, border 0.3s;}
body.dark .setting-card { background:#2a2a2a; border:1px solid #444;}
.setting-card:hover { background:#f0f0f0; }
body.dark .setting-card:hover { background:#333; }
.setting-card h4 { margin:0; font-size:17px; }
.setting-card span { color:#666; font-size:14px;}
body.dark .setting-card span { color:#aaa; }
/* Toggle Switch */
.switch { position:relative; display:inline-block; width:50px; height:26px; }
.switch input { opacity:0; width:0; height:0;}
.slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#ccc; transition:.4s; border-radius:26px;}
.slider:before { position:absolute; content:""; height:20px; width:20px; left:3px; bottom:3px; background-color:white; transition:.4s; border-radius:50%; }
input:checked + .slider { background-color:#007bff; }
input:checked + .slider:before { transform: translateX(24px); }
.user-info { margin-bottom:20px; padding:15px; background:#f0f0f0; border-radius:8px; }
body.dark .user-info { background:#333; }
.user-info p { margin:5px 0; }
</style>
</head>
<body>
<header>
  <h1>WebAuction</h1>
  <a href="user_profile.php">Back to Profile</a>
</header>

<div class="container">
  <h2>Account Settings</h2>

  <div class="user-info">
    <p><strong>Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
    <p><strong>Account Type:</strong> <?php echo ucfirst($accountType); ?></p>
    <p><strong>Joined On:</strong> <?php echo $createdAt; ?></p>
  </div>

  <!-- Theme Toggle -->
  <div class="setting-card">
    <h4>Theme</h4>
    <label class="switch">
      <input type="checkbox" id="theme-toggle">
      <span class="slider"></span>
    </label>
  </div>

  <div class="setting-card" onclick="location.href='edit_profile.php'">
    <h4>Edit Profile</h4>
    <span>Update your personal details</span>
  </div>

  <div class="setting-card" onclick="location.href='comingsoon.php'">
    <h4>Change Password</h4>
    <span>Keep your account secure</span>
  </div>

  <div class="setting-card" onclick="location.href='logout.php'">
    <h4>Logout</h4>
    <span>Sign out of your account</span>
  </div>
</div>

<script>
const toggle = document.getElementById("theme-toggle");

// Apply theme from localStorage on load
document.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("theme") || "light";
  if (savedTheme === "dark") {
    document.body.classList.add("dark");
    toggle.checked = true;
  }
});

// Save theme preference on toggle
toggle.addEventListener("change", () => {
  if (toggle.checked) {
    document.body.classList.add("dark");
    localStorage.setItem("theme", "dark");
  } else {
    document.body.classList.remove("dark");
    localStorage.setItem("theme", "light");
  }
});
</script>
</body>
</html>
