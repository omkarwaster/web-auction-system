<?php
session_start();
require 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

// Fetch current user info
$userEmail = $_SESSION['email'];
$stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $userId = $row['id'];
    $userName = $row['name'];
    $userEmail = $row['email'];
} else {
    die("User not found.");
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = $_POST['name'];
    $newEmail = $_POST['email'];

    $updateStmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $updateStmt->bind_param("ssi", $newName, $newEmail, $userId);
    if ($updateStmt->execute()) {
        $_SESSION['email'] = $newEmail; // update session email
        echo "<script>alert('Profile updated successfully!'); window.location.href='account_settings.php';</script>";
        exit();
    } else {
        $error = "Error updating profile: " . $updateStmt->error;
    }
    $updateStmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile - Auction System</title>
<style>
body { font-family: Arial, sans-serif; margin:0; background:#f9f9f9; color:#333; transition: background 0.3s, color 0.3s;}
body.dark { background:#181818; color:#f5f5f5; }
header { background:#007bff; color:#fff; padding:15px 20px; display:flex; justify-content:space-between; align-items:center;}
header h1 { margin:0; font-size:22px;}
header a { color:#fff; text-decoration:none; font-weight:bold;}
.container { width:90%; max-width:500px; margin:30px auto; background:#fff; border-radius:10px; padding:25px; box-shadow:0 4px 20px rgba(0,0,0,0.1); transition: background 0.3s;}
body.dark .container { background:#242424; }
h2 { text-align:center; color:#007bff; margin-bottom:20px; }
form label { display:block; margin:10px 0 5px; font-weight:600; }
form input { width:100%; padding:10px; margin-bottom:15px; border-radius:6px; border:1px solid #ccc; background:#fff; color:#333; transition: background 0.3s, color 0.3s;}
body.dark form input { background:#333; color:#f5f5f5; border:1px solid #555; }
form button { background:#007bff; color:#fff; padding:10px 15px; border:none; border-radius:6px; cursor:pointer; }
form button:hover { background:#0056b3; }
.error { color:red; margin-bottom:15px; }
</style>
</head>
<body>
<header>
  <h1>WebAuction</h1>
  <a href="account_settings.php">Back</a>
</header>

<div class="container">
  <h2>Edit Profile</h2>

  <?php if(isset($error)) echo "<p class='error'>".$error."</p>"; ?>

  <form method="POST">
    <label for="name">Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($userName); ?>" required>

    <label for="email">Email</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required>

    <button type="submit">Update Profile</button>
  </form>
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
