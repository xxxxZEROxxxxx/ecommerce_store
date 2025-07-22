<?php
session_start();
include 'includes/db.php';

// Handle redirects BEFORE any output
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $conn->prepare("
    SELECT cart.id AS cart_id, products.id as product_id, products.name, products.price, 
           products.image, cart.quantity, categories.name as category_name
    FROM cart
    JOIN products ON cart.product_id = products.id
    LEFT JOIN categories ON products.category_id = categories.id
    WHERE cart.user_id = ?
    ORDER BY cart.id DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    header("Location: cart.php");
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax_rate = 0.08;
$tax = $subtotal * $tax_rate;
$shipping = $subtotal > 100 ? 0 : 15; // Free shipping over $100
$total = $subtotal + $tax + $shipping;

// Get user information
$user_stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_info = $user_stmt->get_result()->fetch_assoc();

// Set page title and include header AFTER processing
$pageTitle = 'Checkout - Alaa Fashion Store';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
                    <li class="breadcrumb-item active">Checkout</li>
                </ol>
            </nav>
            
            <h1 class="h2 mb-4">
                <i class="fas fa-credit-card me-3"></i>Checkout
            </h1>
        </div>
    </div>

    <form id="fake-checkout-form">
        <div class="row">
            <!-- Left Column - Simple Forms -->
            <div class="col-lg-8">
                <!-- Shipping Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-shipping-fast me-2"></i>
                            Shipping Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user_info['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address *</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="state" class="form-label">State *</label>
                                <input type="text" class="form-control" id="state" name="state" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="zip_code" class="form-label">ZIP Code *</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Method -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-truck me-2"></i>
                            Shipping Method
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="shipping_method" 
                                   value="standard" id="standard_shipping" checked>
                            <label class="form-check-label d-flex justify-content-between w-100" for="standard_shipping">
                                <div>
                                    <strong>Standard Shipping</strong>
                                    <br><small class="text-muted">5-7 business days</small>
                                </div>
                                <div class="text-end">
                                    <?php if ($subtotal > 100): ?>
                                        <span class="text-success">FREE</span>
                                    <?php else: ?>
                                        <span>$15.00</span>
                                    <?php endif; ?>
                                </div>
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="shipping_method" 
                                   value="express" id="express_shipping">
                            <label class="form-check-label d-flex justify-content-between w-100" for="express_shipping">
                                <div>
                                    <strong>Express Shipping</strong>
                                    <br><small class="text-muted">2-3 business days</small>
                                </div>
                                <div class="text-end">
                                    <span>$25.00</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Payment Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Demo Mode:</strong> This is a fake checkout for demonstration purposes. No real payment will be processed.
                        </div>
                        
                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number *</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" 
                                   placeholder="1234 5678 9012 3456" maxlength="19" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expiry" class="form-label">Expiry Date *</label>
                                <input type="text" class="form-control" id="expiry" name="expiry" 
                                       placeholder="MM/YY" maxlength="5" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvv" class="form-label">CVV *</label>
                                <input type="text" class="form-control" id="cvv" name="cvv" 
                                       placeholder="123" maxlength="4" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="card_name" class="form-label">Name on Card *</label>
                            <input type="text" class="form-control" id="card_name" name="card_name" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="col-lg-4">
                <div class="card position-sticky" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cart Items -->
                        <div class="order-items mb-3">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <img src="uploads/<?= $item['image'] ?>" 
                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                         class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                        <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                                    </div>
                                    <div class="text-end">
                                        <strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr>

                        <!-- Order Totals -->
                        <div class="order-totals">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>$<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>
                                    <?php if ($shipping == 0): ?>
                                        <span class="text-success">FREE</span>
                                    <?php else: ?>
                                        $<?= number_format($shipping, 2) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax:</span>
                                <span>$<?= number_format($tax, 2) ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="text-primary">$<?= number_format($total, 2) ?></strong>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="place-order-btn">
                            <i class="fas fa-shopping-bag me-2"></i>
                            <span>Place Order - $<?= number_format($total, 2) ?></span>
                        </button>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                This is a demo checkout - no real payment will be processed
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Format card number input
document.getElementById('card_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formattedValue;
});

// Format expiry date input
document.getElementById('expiry').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
});

// Only allow numbers for CVV
document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});

// Handle form submission
document.getElementById('fake-checkout-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const button = document.getElementById('place-order-btn');
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    button.disabled = true;
    
    // Simulate processing time
    setTimeout(() => {
        // Clear cart and redirect to success page
        fetch('fake_process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                total: <?= $total ?>,
                items: <?= json_encode($cart_items) ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'order_success.php?order_id=' + data.order_id;
            } else {
                alert('Order failed: ' + data.message);
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your order');
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }, 2000); // 2 second delay to simulate processing
});
</script>

<style>
.order-items {
    max-height: 300px;
    overflow-y: auto;
}

.form-check {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.3s ease;
}

.form-check:hover {
    border-color: var(--primary-color);
    background-color: #f8f9fa;
}

.form-check-input:checked + .form-check-label {
    color: var(--primary-color);
}
</style>

</body>
</html>
