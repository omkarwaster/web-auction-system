<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['accountType'])) {
    die("Please log in to create a listing.");
}

$userEmail = $_SESSION['email'];

// Fetch user id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $userId = $row['id'];
} else {
    die("User not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Listing</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fff;
            color: #2d3748;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            transition: background 0.3s, color 0.3s;
        }

        /* Header */
        header {
            width: 100%;
            background-color: #007BFF;
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        header .logo {
            font-size: 20px;
            font-weight: bold;
        }

        header .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-right: 20px;
        }

        header a.profile-btn {
            background: white;
            color: #007BFF;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s ease, color 0.3s ease;
            white-space: nowrap;
        }

        header a.profile-btn:hover {
            background: #0056b3;
            color: white;
        }

        /* Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch input { opacity: 0; width: 0; height: 0; }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 26px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px; width: 20px;
            left: 3px; bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider { background-color: #0056b3; }
        input:checked + .slider:before { transform: translateX(24px); }

        /* Dark Theme */
        body.dark {
            background: #1a202c;
            color: #edf2f7;
        }
        body.dark .form-container {
            background: #2d3748;
            color: #edf2f7;
        }
        body.dark input,
        body.dark select,
        body.dark textarea {
            background: #4a5568;
            color: #edf2f7;
            border: 1.5px solid #718096;
        }
        body.dark button {
            background: #3182ce;
        }
        body.dark button:hover {
            background: #2b6cb0;
        }

        /* Form */
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            transition: background 0.3s, color 0.3s;
        }

        h2 {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 25px;
        }

        label { display: block; font-weight: 600; margin-bottom: 6px; }

        input[type="text"],
        input[type="file"],
        input[type="number"],
        input[type="datetime-local"],
        select,
        textarea {
            width: 100%;
            padding: 10px 14px;
            margin-bottom: 20px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #007bff;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        textarea { resize: vertical; min-height: 100px; }

        button {
            display: block;
            width: 100%;
            background: #007bff;
            color: white;
            border: none;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        button:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
    </style>
</head>
<body>

<header>
    <div class="logo">WebAuction</div>
    <div class="header-actions">
        <!-- Toggle Switch -->
        <label class="switch">
            <input type="checkbox" id="theme-toggle">
            <span class="slider"></span>
        </label>
        <!-- Back to Profile -->
        <a href="user_profile.php" class="profile-btn">Back to Profile</a>
    </div>
</header>

<div class="form-container">
    <h2>Create New Listing</h2>
    <form action="save_listing.php" method="POST" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Image:</label>
        <input type="file" name="image" accept="image/*">

        <label>Category:</label>
        <select name="category" id="category" required>
            <option value="">-- Select Category --</option>
            <?php
            $result = $conn->query("SELECT category_id, category_name FROM categories");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['category_id'] . "'>" . $row['category_name'] . "</option>";
            }
            ?>
        </select>

        <label>Subcategory:</label>
        <select name="subcategory" id="subcategory" required>
            <option value="">-- Select Subcategory --</option>
        </select>

        <label>Base Price:</label>
        <input type="number" step="0.01" name="base_price" required>

        <label>Bid Increment:</label>
        <input type="number" step="0.01" name="bid_increment" required>

        <label>Description:</label>
        <textarea name="description" required></textarea>

        <label>End Date:</label>
        <input type="datetime-local" name="end_date" required>

        <!-- Hidden seller id -->
        <input type="hidden" name="seller_id" value="<?php echo htmlspecialchars($userId); ?>">

        <button type="submit">Create Listing</button>
    </form>
</div>

<script>
// Subcategories loader
document.getElementById("category").addEventListener("change", function() {
    var categoryId = this.value;
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "get_subcategories.php?category_id=" + categoryId, true);
    xhr.onload = function() {
        if (this.status === 200) {
            console.log("Response from PHP:", this.responseText);
            let subSelect = document.getElementById("subcategory");
            subSelect.innerHTML = this.responseText;
            subSelect.focus(); //  automatically focuses dropdown
        }
    };
    xhr.send();
});


// Apply saved theme from localStorage (shared with user_profile.php)
const savedTheme = localStorage.getItem("theme") || "light";
if (savedTheme === "dark") {
    document.body.classList.add("dark");
    document.getElementById("theme-toggle").checked = true;
}

// Toggle theme and save preference
const toggleSwitch = document.getElementById("theme-toggle");
toggleSwitch.addEventListener("change", () => {
    if (toggleSwitch.checked) {
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
