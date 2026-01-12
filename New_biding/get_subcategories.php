<?php
include 'db_connect.php';

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    $query = $conn->prepare("SELECT subcategory_id, subcategory_name FROM subcategories WHERE category_id = ?");
    $query->bind_param("i", $category_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        echo "<option value=''>-- Select Subcategory --</option>";
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . htmlspecialchars($row['subcategory_id']) . "'>" . htmlspecialchars($row['subcategory_name']) . "</option>";
        }
    } else {
        // Handle empty result
        echo "<option value=''>No subcategories available</option>";
    }

    $query->close();
}
?>
