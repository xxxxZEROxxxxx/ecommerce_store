<?php 
session_start();
include 'includes/db.php';

// Handle redirects BEFORE any output
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle cart operations
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    $delete = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $delete->bind_param("ii", $remove_id, $user_id);
    $delete->execute();
    $success_message = "Product removed from cart successfully!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $cart_id = intval($_POST['cart_id']);
    $new_qty = max(1, intval($_POST['quantity']));
    $updateQty = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $updateQty->bind_param("iii", $new_qty, $cart_id, $user_id);
    $updateQty->execute();
    $success_message = "Cart updated successfully!";
}

if (isset($_POST['clear_cart'])) {
    $clear = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear->bind_param("i", $user_id);
    $clear->execute();
    $success_message = "Cart cleared successfully!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    $check = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $newQty = $row['quantity'] + $quantity;
        $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update->bind_param("ii", $newQty, $row['id']);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $user_id, $product_id, $quantity);
        $insert->execute();
    }
    exit;
}

// Check if created_at column exists in cart table
$check_column = $conn->query("SHOW COLUMNS FROM cart LIKE 'created_at'");
$has_created_at = $check_column->num_rows > 0;

// Get cart items with conditional ORDER BY
$order_clause = $has_created_at ? "ORDER BY cart.created_at DESC" : "ORDER BY cart.id DESC";

$stmt = $conn->prepare("
    SELECT cart.id AS cart_id, products.id as product_id, products.name, products.price, 
           products.image, cart.quantity, categories.name as category_name
    FROM cart
    JOIN products ON cart.product_id = products.id
    LEFT JOIN categories ON products.category_id = categories.id
    WHERE cart.user_id = ?
    $order_clause
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

// Set page title and include header AFTER processing
$pageTitle = 'Shopping Cart - Alaa Fashion Store';
include 'includes/header.php';
?>

<div class="container py-5">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0">
                    <i class="fas fa-shopping-cart me-3"></i>Shopping Cart
                </h1>
                <a href="products.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($cart_items)): ?>
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                    <h3>Your cart is empty</h3>
                    <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
                    <a href="products.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-store me-2"></i>Start Shopping
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0">Cart Items (<?= count($cart_items) ?>)</h5>
                            </div>
                            <div class="col-auto">
                                <form method="post" class="d-inline">
                                    <button type="submit" name="clear_cart" class="btn btn-outline-danger btn-sm" 
                                            onclick="return confirm('Are you sure you want to clear your cart?')">
                                        <i class="fas fa-trash me-1"></i>Clear Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php 
                        $total = 0;
                        foreach ($cart_items as $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                        ?>
                            <div class="border-bottom p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img src="uploads/<?= $item['image'] ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             class="img-fluid rounded" style="height: 80px; object-fit: cover;">
                                    </div>
                                    <div class="col-md-2">
                                        <h6 class="mb-1">
                                            <a href="product.php?id=<?= $item['product_id'] ?>" 
                                               class="text-decoration-none">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted"><?= htmlspecialchars($item['category_name'] ?? 'N/A') ?></small>
                                    </div>
                                    <div class="col-md-2">
                                        <strong class="text-primary">$<?= number_format($item['price'], 2) ?></strong>
                                    </div>
                                    <div class="col-md-3">
                                        <form method="post" class="d-flex align-items-center">
                                            <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                            <div class="input-group input-group-md  " >
                                                <button type="button" class="btn  btn-outline-secondary" 
                                                        onclick="decreaseQuantity(this)">-</button>
                                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" 
                                                       min="1" max="99" class="form-control text-center quantity-input">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="increaseQuantity(this)">+</button>
                                            </div>
                                            <button type="submit" name="update_qty" class="btn btn-sm btn-primary ms-2">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <strong>$<?= number_format($subtotal, 2) ?></strong>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <a href="cart.php?remove=<?= $item['cart_id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Remove this item from cart?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal:</span>
                            <strong>$<?= number_format($total, 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping:</span>
                            <span class="text-success">Free</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Tax:</span>
                            <span>$<?= number_format($total * 0.08, 2) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total:</strong>
                            <strong class="text-primary">$<?= number_format($total * 1.08, 2) ?></strong>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="checkout.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                            </a>
                            <a href="products.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Promo Code -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">Have a promo code?</h6>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Enter code">
                            <button class="btn btn-outline-secondary" type="button">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function increaseQuantity(button) {
    const input = button.parentElement.querySelector('.quantity-input');
    input.value = parseInt(input.value) + 1;
}

function decreaseQuantity(button) {
    const input = button.parentElement.querySelector('.quantity-input');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}
</script>
</body>
</html>
