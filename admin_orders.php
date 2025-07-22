<?php
session_start();
include 'includes/db.php';

// Handle redirects BEFORE any output
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Handle order status updates
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    
    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $order_id);
    $update_stmt->execute();
    $success_message = "Order status updated successfully!";
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$user_filter = $_GET['user'] ?? '';

// Build query with filters
$where_conditions = [];
$params = [];
$types = '';

if ($status_filter) {
    $where_conditions[] = "orders.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($date_filter) {
    $where_conditions[] = "DATE(orders.created_at) = ?";
    $params[] = $date_filter;
    $types .= 's';
}

if ($user_filter) {
    $where_conditions[] = "users.username LIKE ?";
    $params[] = "%$user_filter%";
    $types .= 's';
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$query = "
    SELECT orders.*, users.username, users.email
    FROM orders 
    JOIN users ON orders.user_id = users.id 
    $where_clause
    ORDER BY orders.created_at DESC
";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result();

// Get statistics
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'")->fetch_assoc()['count'];
$completed_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total) as revenue FROM orders WHERE payment_status = 'completed'")->fetch_assoc()['revenue'] ?? 0;

// Set page title and include header AFTER processing
$pageTitle = 'Order Management - Admin Panel';
include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="admin_panel.php">Admin Panel</a></li>
            <li class="breadcrumb-item active">Order Management</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0">
                    <i class="fas fa-clipboard-list me-3"></i>Order Management
                </h1>
                <a href="admin_panel.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                <h3><?= $total_orders ?></h3>
                <p class="mb-0">Total Orders</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h3><?= $pending_orders ?></h3>
                <p class="mb-0">Pending Orders</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <h3><?= $completed_orders ?></h3>
                <p class="mb-0">Completed Orders</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                <h3>$<?= number_format($total_revenue, 2) ?></h3>
                <p class="mb-0">Total Revenue</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Date</label>
                      <label for="date" class="form-label">Date</label>
            <input type="text" class="form-control" id="date" name="date"
                   value="<?= htmlspecialchars($date_filter) ?>" placeholder="Select date">
                </div>
                <div class="col-md-3">
                    <label for="user" class="form-label">Customer</label>
                    <input type="text" class="form-control" id="user" name="user" 
                           placeholder="Search by username" value="<?= htmlspecialchars($user_filter) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>All Orders
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if ($orders->num_rows === 0): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4>No Orders Found</h4>
                    <p class="text-muted">No orders match your current filters.</p>
                </div>
            <?php else: ?>
                <?php while($order = $orders->fetch_assoc()): ?>
                    <div class="border-bottom p-4">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <h5 class="mb-1">
                                    Order #<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?>
                                </h5>
                                <div class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    <strong><?= htmlspecialchars($order['username']) ?></strong>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-envelope me-1"></i>
                                    <?= htmlspecialchars($order['email']) ?>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="mb-2">
                                    <?php
                                    $status_classes = [
                                        'processing' => 'bg-info',
                                        'shipped' => 'bg-warning',
                                        'delivered' => 'bg-success',
                                        'cancelled' => 'bg-danger'
                                    ];
                                    $status_class = $status_classes[$order['status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $status_class ?> me-2">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                    
                                    <?php
                                    $payment_classes = [
                                        'completed' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'failed' => 'bg-danger'
                                    ];
                                    $payment_class = $payment_classes[$order['payment_status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $payment_class ?>">
                                        Payment: <?= ucfirst($order['payment_status']) ?>
                                    </span>
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php
                                    // Force English date display
                                    $months = [
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                    ];
                                    $date = new DateTime($order['created_at']);
                                    $month = $months[(int)$date->format('n')];
                                    echo $month . ' ' . $date->format('j, Y g:i A');
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Order Items -->
                                <?php
                                $items_stmt = $conn->prepare("
                                    SELECT products.name, products.image, order_items.quantity, order_items.price
                                    FROM order_items
                                    JOIN products ON products.id = order_items.product_id
                                    WHERE order_items.order_id = ?
                                ");
                                $items_stmt->bind_param("i", $order['id']);
                                $items_stmt->execute();
                                $items = $items_stmt->get_result();
                                ?>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($item = $items->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="uploads/<?= $item['image'] ?>" 
                                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                                 class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                            <span><?= htmlspecialchars($item['name']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td><?= $item['quantity'] ?></td>
                                                    <td>$<?= number_format($item['price'], 2) ?></td>
                                                    <td><strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Order Summary</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total:</span>
                                            <strong class="text-primary">$<?= number_format($order['total'], 2) ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span>Payment:</span>
                                            <span><?= ucfirst($order['payment_method']) ?></span>
                                        </div>
                                        
                                        <!-- Status Update Form -->
                                        <form method="post" class="d-flex gap-2">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
       flatpickr("#date", {
        dateFormat: "Y-m-d",
        locale: "en"
    });
// Force English locale for date inputs
document.addEventListener('DOMContentLoaded', function() {
    // Set HTML lang attribute to English
    document.documentElement.lang = 'en';
    
    // Force date input to use English format
    const dateInput = document.getElementById('date');
    if (dateInput) {
        dateInput.setAttribute('lang', 'en');
        dateInput.setAttribute('data-date-format', 'yyyy-mm-dd');
        
        // Override browser locale for this input
        dateInput.addEventListener('focus', function() {
            this.setAttribute('lang', 'en');
        });
    }
});

// Auto-submit filters on change
document.querySelectorAll('#status, #date').forEach(element => {
    element.addEventListener('change', function() {
        this.form.submit();
    });
});

// Add fade-in animation to cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 100);
    });
});
</script>

<style>
/* Force English date picker */
input[type="date"] {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="%23666" d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>');
}

/* Override any Arabic styling */
input[type="date"]:lang(ar) {
    direction: ltr !important;
    text-align: left !important;
}
</style>

</body>
</html>
