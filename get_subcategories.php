<?php
include 'includes/db.php';

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    $stmt = $conn->prepare("SELECT id, name FROM subcategories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subcategories = [];
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($subcategories);
}
