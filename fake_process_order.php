<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // جلب عناصر السلة
    $stmt = $conn->prepare("
        SELECT cart.id AS cart_id, products.id as product_id, products.name, products.price, 
               products.image, cart.quantity
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);

    if (empty($cart_items)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }

    // حساب المجموع الكلي
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $total = $total * 1.08; // ضريبة 8%

    // توليد رقم الطلب
    $order_number = 'ORD-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    // إضافة الطلب إلى جدول orders
    $order_stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, order_number, total, payment_method, payment_status, shipping_address, shipping_method, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $payment_method = 'card';
    $payment_status = 'completed';
    $shipping_address = 'Default Address';
    $shipping_method = 'standard';
    $status = 'delivered';

    $order_stmt->bind_param(
        "isdsssss",
        $user_id,
        $order_number,
        $total,
        $payment_method,
        $payment_status,
        $shipping_address,
        $shipping_method,
        $status
    );
    $order_stmt->execute();

    // الحصول على order_id الجديد
    $order_id = $conn->insert_id;

    // إدخال عناصر الطلب إلى جدول order_items
    $item_stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, price, quantity)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($cart_items as $item) {
        $product_id = $item['product_id'];
        $price = $item['price'];
        $quantity = $item['quantity'];
        $item_stmt->bind_param("iidi", $order_id, $product_id, $price, $quantity);
        $item_stmt->execute();
    }

    // تفريغ السلة
    $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear_cart->bind_param("i", $user_id);
    $clear_cart->execute();

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Order placed successfully!'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process order: ' . $e->getMessage()
    ]);
}
?>
