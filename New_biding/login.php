<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "auction_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle POST login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');
    $accountType = $_POST["accountType"] ?? 'personal';

    // Step 1: Find user in the users table
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();

        // Step 2: Validate password from subtable (personal_users or business_users)
        if ($accountType === "personal") {
            $subStmt = $conn->prepare("SELECT id, password FROM personal_users WHERE email = ?");
        } elseif ($accountType === "business") {
            $subStmt = $conn->prepare("SELECT id, password FROM business_users WHERE email = ?");
        } else {
            die("Invalid account type.");
        }

        $subStmt->bind_param("s", $email);
        $subStmt->execute();
        $subRes = $subStmt->get_result();

        if ($subRes && $subRes->num_rows === 1) {
            $subUser = $subRes->fetch_assoc();

            // Check password (replace with password_verify if hashed)
            if ($subUser["password"] === $password) {
                session_regenerate_id(true);

                // Store central user ID for FK relations
                $_SESSION["user_id"] = $user["id"];

                // Store role ID for reference
                if ($accountType === "personal") {
                    $_SESSION["personal_id"] = $subUser["id"];
                } else {
                    $_SESSION["business_id"] = $subUser["id"];
                }

                $_SESSION["email"]       = $email;
                $_SESSION["accountType"] = $accountType;

                // Debug log
                error_log("Login successful: " . print_r($_SESSION, true));

                header("Location: index.php");
                exit();
            } else {
                echo "<script>alert('Incorrect password.'); window.location.href = 'login.html';</script>";
            }
        } else {
            echo "<script>alert('User not found in subtable.'); window.location.href = 'login.html';</script>";
        }

        $subStmt->close();
    } else {
        echo "<script>alert('User not found.'); window.location.href = 'login.html';</script>";
    }

    $stmt->close();
}

$conn->close();
?>
