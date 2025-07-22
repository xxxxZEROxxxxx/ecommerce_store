<?php
session_start();
require 'includes/db.php';
if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}
$id = intval($_GET['id']);

$stmt = $conn->prepare('
SELECT p.*, c.name AS category_name, s.name AS sub_name
FROM products p
LEFT JOIN categories   c ON p.category_id   = c.id
LEFT JOIN subcategories s ON p.subcategory_id = s.id
WHERE p.id = ? LIMIT 1
');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: products.php');
    exit;
}
$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Clothing Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/unified-style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php">Products</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Image -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <img src="uploads/<?php echo $product['image']; ?>"
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     class="card-img-top"
                     style="height: 500px; object-fit: cover;">
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h1 class="card-title h2 mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="mb-3">
                        <span class="h3 text-primary fw-bold">$<?php echo number_format($product['price'], 2); ?></span>
                        <?php if ($product['classification'] === 'best_seller'): ?>
                            <span class="badge bg-warning ms-2">Best Seller</span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <h5>Description</h5>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <!-- Product Meta -->
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <ul class="list-unstyled">
                                <li><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?></li>
                                <li><strong>Type:</strong> <?php echo htmlspecialchars($product['sub_name']); ?></li>
                            </ul>
                        </div>
                        <div class="col-sm-6">
                            <ul class="list-unstyled">
                                <li><strong>Gender:</strong> <?php echo ucfirst(htmlspecialchars($product['gender'])); ?></li>
                                <li><strong>Model Year:</strong> <?php echo htmlspecialchars($product['model_year']); ?></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Add to Cart Form -->
                    <form class="add-to-cart-form mb-3" data-id="<?= $product['id']; ?>">
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-2">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" value="1" min="1" max="10">
                            </div>
                            <div class="col-md-8 mb-2">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Additional Actions -->
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <div class="mt-5">
        <h3 class="mb-4">Related Products</h3>
        <div class="row">
            <?php
            $related = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
            $related->bind_param("ii", $product['category_id'], $product['id']);
            $related->execute();
            $related_result = $related->get_result();
            
            while ($related_product = $related_result->fetch_assoc()):
            ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card">
                        <img src="uploads/<?= $related_product['image'] ?>" 
                             alt="<?= htmlspecialchars($related_product['name']) ?>"
                             class="card-img-top">
                        <div class="card-body">
                            <h6 class="product-title"><?= htmlspecialchars($related_product['name']) ?></h6>
                            <p class="product-price">$<?= number_format($related_product['price'], 2) ?></p>
                            <a href="product.php?id=<?= $related_product['id'] ?>" class="btn btn-outline-primary btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="cartToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="fas fa-check-circle text-success me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Product added to cart successfully!
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const productId = this.dataset.id;
        const quantity = this.querySelector('#quantity').value;
        const button = this.querySelector('button[type="submit"]');
        
        // Loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
        button.disabled = true;

        fetch('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `product_id=${productId}&quantity=${quantity}&ajax=1`
        })
        .then(res => res.text())
        .then(data => {
            // Show toast
            const toast = new bootstrap.Toast(document.getElementById('cartToast'));
            toast.show();
            
            // Update cart icon
            updateCartIcon();
            
            // Reset button
            button.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Add to Cart';
            button.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            button.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Add to Cart';
            button.disabled = false;
        });
    });
});

function updateCartIcon() {
    fetch('cart_count.php')
        .then(res => res.text())
        .then(count => {
            const badge = document.getElementById('cart-count');
            if (badge) badge.textContent = count;
        });
}
</script>
</body>
</html>
