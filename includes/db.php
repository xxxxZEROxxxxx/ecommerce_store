<?php
$host = 'localhost';
$dbname = 'ecommerce_store';
$user = 'root';
$pass = '';

// الاتصال بقاعدة البيانات
$conn = new mysqli($host, $user, $pass, $dbname);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);   
// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// mysqli_set_charset لضبط الترميز العربي
$conn->set_charset("utf8");
?>
