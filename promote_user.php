<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // لا تقدر ترقي نفسك
    if ($_SESSION['user_id'] == $id) {
        header("Location: admin_users.php");
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: admin_users.php");
exit;
?>