<?php
session_start();
include 'includes/db.php';

$pageTitle = 'Order Success - Kaira Fashion Store';
include 'includes/header.php';

// تحقق من وجود معرف الطلب في الرابط
if (!isset($_GET['order_id'])) {
    echo "<div class='alert alert-danger'>Order ID is missing.</div>";
    exit;
}

$order_id = (int) $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// جلب بيانات الطلب من قاعدة البيانات
$order_query = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order_query->bind_param("ii", $order_id, $user_id);
$order_query->execute();
$order_result = $order_query->get_result();

if ($order_result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Order not found or you do not have permission to view it.</div>";
    exit;
}

$order = $order_result->fetch_assoc();

// جلب عناصر الطلب
$items_query = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$items_query->bind_param("i", $order_id);
$items_query->execute();
$items_result = $items_query->get_result();
$order['items'] = $items_result->fetch_all(MYSQLI_ASSOC);
?>



<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="success-icon mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h1 class="h2 text-success mb-3">Order Placed Successfully!</h1>
                <p class="lead">Thank you for your demo order. This was a simulated checkout process.</p>
                <div class="alert alert-success">
                    <strong>Order Number:</strong># <?= htmlspecialchars($order['order_number']) ?>
                </div>
            </div>

            <!-- Order Details -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>Order Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <ul class="list-unstyled">
                                <li><strong>Order Number:</strong> <?= htmlspecialchars($order['order_number']) ?></li>
                                <li><strong>Order Date:</strong> <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></li>
                                <li><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></li>
                                <li><strong>Status:</strong> 
                                    <span class="badge bg-success">Confirmed </span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Demo Information</h6>
                            <ul class="list-unstyled">
                                <li><strong>Processing:</strong> Simulated</li>
                                <li><strong>Payment:</strong> Not charged</li>
                                <li><strong>Shipping:</strong> Demo only</li>
                                <li><strong>Total:</strong> $<?= number_format($order['total'], 2) ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2"></i>Order Items
                    </h5>
                </div>
                <div class="card-body">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <img src="uploads/<?= $item['image'] ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                <p class="text-muted mb-0">Quantity: <?= $item['quantity'] ?></p>
                                <p class="text-muted mb-0">Price: $<?= number_format($item['price'], 2) ?> each</p>
                            </div>
                            <div class="text-end">
                                <strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Demo Notice -->
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Demo Notice</h5>
                <p class="mb-0">
                    This was a demonstration checkout process. No real payment was processed, 
                    no products will be shipped, and no actual order was placed. This is for 
                    testing and demonstration purposes only.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="text-center">
                <a href="index.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
                <a href="products.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
