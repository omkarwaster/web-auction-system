<?php
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['accountType'])) {
    die("<script>
            alert('You must be logged in to create a listing.');
            window.location.href='login.html';
         </script>");
}

// Get seller ID from users table
$userEmail = $_SESSION['email'];
$stmtUser = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmtUser->bind_param("s", $userEmail);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
if ($rowUser = $resultUser->fetch_assoc()) {
    $seller_id = $rowUser['id'];
} else {
    die("<script>
            alert('User not found.');
            window.location.href='login.html';
         </script>");
}
$stmtUser->close();

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category'];
    $subcategory_id = $_POST['subcategory'];
    $base_price = $_POST['base_price'];
    $bid_increment = $_POST['bid_increment'];
    $end_date = $_POST['end_date'];

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $image_path = $targetFilePath;
        }
    }

    $stmt = $conn->prepare("INSERT INTO listings 
        (title, image_path, category_id, subcategory_id, base_price, bid_increment, description, end_date, seller_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssiidissi",
        $title,
        $image_path,
        $category_id,
        $subcategory_id,
        $base_price,
        $bid_increment,
        $description,
        $end_date,
        $seller_id
    );

    if ($stmt->execute()) {
        echo "<script>
                alert('Listing created successfully!');
                window.location.href='user_profile.php';
              </script>";
    } else {
        echo "<script>
                alert('Database error: " . addslashes($stmt->error) . "');
                window.history.back();
              </script>";
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    echo "<script>
            alert('Invalid request method.');
            window.history.back();
          </script>";
    exit();
}
?>
