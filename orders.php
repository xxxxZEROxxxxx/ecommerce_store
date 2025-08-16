<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check and add missing columns to orders table
$required_columns = [
    'order_number' => "VARCHAR(50) UNIQUE AFTER id",
    'status' => "VARCHAR(50) DEFAULT 'processing' AFTER total", 
    'payment_status' => "VARCHAR(50) DEFAULT 'pending' AFTER status",
    'payment_method' => "VARCHAR(50) DEFAULT 'cash' AFTER payment_status",
    'shipping_method' => "VARCHAR(50) DEFAULT 'standard' AFTER payment_method",
    'shipping_address' => "TEXT AFTER shipping_method"
];

foreach ($required_columns as $column => $definition) {
    $check = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN $column $definition");
    }
}

// Update existing orders with missing data
$conn->query("
    UPDATE orders 
    SET 
        order_number = CONCAT('ORD-', YEAR(created_at), '-', LPAD(id, 5, '0')),
        status = COALESCE(status, 'processing'),
        payment_status = COALESCE(payment_status, 'completed'),
        payment_method = COALESCE(payment_method, 'demo_card'),
        shipping_method = COALESCE(shipping_method, 'standard'),
        shipping_address = COALESCE(shipping_address, '{\"full_name\":\"Demo User\",\"address\":\"123 Demo St\"}')
    WHERE order_number IS NULL OR order_number = ''
");

// Get all user orders
$stmt = $conn->prepare("
    SELECT id, order_number, created_at, total, status, payment_status, payment_method
    FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'My Orders - Alaa Fashion Store';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0">
                    <i class="fas fa-box me-3"></i>My Orders
                </h1>
                <a href="products.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                    <h3>No Orders Yet</h3>
                    <p class="text-muted mb-4">You haven't placed any orders yet</p>
                    <a href="products.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-store me-2"></i>Start Shopping
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($orders as $order): ?>
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0">
                                        Order #<?= htmlspecialchars($order['order_number'] ?? 'ORD-' . $order['id']) ?>
                                    </h5>
                                    <small class="text-muted">
                                        Placed on <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?>
                                    </small>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="mb-2">
                                        <?php
                                        $status_class = [
                                            'processing' => 'bg-info',
                                            'shipped' => 'bg-warning', 
                                            'delivered' => 'bg-success',
                                            'cancelled' => 'bg-danger'
                                        ];
                                        $status = $order['status'] ?? 'processing';
                                        $class = $status_class[$status] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $class ?> me-2"><?= ucfirst($status) ?></span>
                                        
                                       
                                        <span class="badge bg-success">completed</span>
                                    </div>
                                    <strong class="text-primary">Total: $<?= number_format($order['total'], 2) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get order items
                            $items_stmt = $conn->prepare("
                                SELECT products.name, products.image, order_items.quantity, order_items.price
                                FROM order_items
                                JOIN products ON products.id = order_items.product_id
                                WHERE order_items.order_id = ?
                            ");
                            $items_stmt->bind_param("i", $order['id']);
                            $items_stmt->execute();
                            $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="mb-3">Order Items (<?= count($items) ?> items)</h6>
                                    <?php if (empty($items)): ?>
                                        <p class="text-muted">No items found for this order.</p>
                                    <?php else: ?>
                                        <?php foreach ($items as $item): ?>
                                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                                <img src="uploads/<?= $item['image'] ?>" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                                     class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                                    <small class="text-muted">
                                                        Quantity: <?= $item['quantity'] ?> × $<?= number_format($item['price'], 2) ?>
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                               
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        // Here you would implement order cancellation
        alert('Order cancellation feature would be implemented here');
    }
}
</script>
</body>
</html>
