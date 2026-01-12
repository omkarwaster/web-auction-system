<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "auction_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $accountType = $_POST["accountType"] ?? '';
  $name = $_POST["name"];
  $email = $_POST["email"];
  $password = $_POST["password"]; // plain text password

  // Handle file upload
  if ($_FILES["file"]["error"] === 0) {
    $fileName = basename($_FILES["file"]["name"]);
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
      mkdir($targetDir, 0777, true);
    }
    $targetFile = $targetDir . time() . "_" . $fileName;
    move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile);
  } else {
    die("File upload error: " . $_FILES["file"]["error"]);
  }

  // Insert into account-specific table
  if ($accountType === "personal") {
    $stmt = $conn->prepare("INSERT INTO personal_users (name, email, password, legal_id_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $targetFile);
    $stmt->execute();
    $stmt->close();
  } elseif ($accountType === "business") {
    $stmt = $conn->prepare("INSERT INTO business_users (business_name, email, password, license_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $targetFile);
    $stmt->execute();
    $stmt->close();
  } else {
    die("Invalid account type.");
  }

  //  Insert into main users table
  $stmt = $conn->prepare("INSERT INTO users (name, email, password, accountType, created_at) 
                          VALUES (?, ?, ?, ?, NOW())");
  $stmt->bind_param("ssss", $name, $email, $password, $accountType);

  if ($stmt->execute()) {
    echo "<script>
            alert('Signup successful!');
            window.location.href = 'login.html';
          </script>";
  } else {
    echo "<script>
            alert('Error: " . addslashes($stmt->error) . "');
            window.history.back();
          </script>";
  }

  $stmt->close();
} else {
  echo "Invalid request.";
}

$conn->close();
?>
