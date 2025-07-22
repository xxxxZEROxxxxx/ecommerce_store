<?php
session_start();
require_once __DIR__ . '/includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clothing Store - Fashion Forward</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/unified-style.css" rel="stylesheet">
  <style>
    body {
      background-color: #fefefe;
      font-family: 'Inter', sans-serif;
    }
    .hero-section {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 100px 0;
      position: relative;
      overflow: hidden;
    }
    .hero-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('./images/bg-newsletter.jpg') center/cover !important;
      opacity: 0.1;
    }
    .hero-content {
      position: relative;
      z-index: 2;
    }
    .collection-badge {
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 50px;
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      margin-bottom: 1rem;
      display: inline-block;
    }
    .section-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
      position: relative;
    }
    .section-title::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 60px;
      height: 4px;
      background: var(--accent-color);
      border-radius: 2px;
    }
    .product-showcase {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      height: 100%;
    }
    .product-showcase:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    .product-showcase img {
      width: 100%;
      height: 300px;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    .product-showcase:hover img {
      transform: scale(1.05);
    }
    .stats-section {
      background: var(--light-bg);
      padding: 60px 0;
    }
    .stat-item {
      text-align: center;
      padding: 2rem;
    }
    .stat-number {
      font-size: 3rem;
      font-weight: 700;
      color: var(--primary-color);
      display: block;
    }
    .filter-card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: var(--box-shadow);
      margin-bottom: 2rem;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <div class="hero-content">
          <div class="collection-badge">
            <i class="fas fa-star me-2"></i>Summer Collection 2024
          </div>
          <h1 class="display-3 fw-bold mb-4">Fashion That Speaks Your Style</h1>
          <p class="lead mb-4">Discover our latest collection of premium clothing designed for the modern lifestyle. Quality, comfort, and style in every piece.</p>
          <div class="d-flex gap-3">
            <a href="products.php" class="btn btn-light btn-lg px-4">
              <i class="fas fa-shopping-bag me-2"></i>Shop Now
            </a>
            <a href="#collections" class="btn btn-outline-light btn-lg px-4">
              Explore Collections
            </a>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="text-center">
          <img src="./images/banner-image-1.jpg?height=500&width=400" 
               alt="Fashion Model" class="img-fluid rounded-3" style="max-height: 500px;">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
  <div class="container">
    <div class="row">
      <div class="col-md-3">
        <div class="stat-item">
          <span class="stat-number"><?= $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'] ?>+</span>
          <p class="mb-0">Products</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-item">
          <span class="stat-number"><?= $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'] ?>+</span>
          <p class="mb-0">Happy Customers</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-item">
          <span class="stat-number">5</span>
          <p class="mb-0">Years Experience</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-item">
          <span class="stat-number">24/7</span>
          <p class="mb-0">Support</p>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="container py-5">
  <!-- New Collection -->
  <section id="collections" class="mb-5">
    <div class="text-center mb-5">
      <h2 class="section-title">New Collection</h2>
      <p class="text-muted">Discover our latest arrivals and trending styles</p>
    </div>
    
    <div class="row g-4 mb-4">
      <?php
      $res = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 2");
      while ($p = $res->fetch_assoc()): ?>
        <div class="col-lg-6">
          <div class="product-showcase">
            <img src="uploads/<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <div class="p-4">
              <h4 class="mb-2"><?= htmlspecialchars($p['name']) ?></h4>
              <p class="text-muted mb-3"><?= htmlspecialchars(substr($p['description'], 0, 100)) ?>...</p>
              <div class="d-flex justify-content-between align-items-center">
                <span class="h5 text-primary mb-0">$<?= number_format($p['price'], 2) ?></span>
                <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary">
                  View Details <i class="fas fa-arrow-right ms-1"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    
    <div class="text-center">
      <a href="products.php" class="btn btn-primary btn-lg px-5">
        <i class="fas fa-store me-2"></i>Visit Our Shop
      </a>
    </div>
  </section>

  <!-- New This Week -->
  <section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="section-title">New This Week</h2>
        <span class="badge bg-primary">Fresh Arrivals</span>
      </div>
      <a href="products.php?class=new" class="btn btn-outline-primary">View All</a>
    </div>
    
    <div class="row g-4">
      <?php
      $res = $conn->query("SELECT * FROM products WHERE created_at >= NOW() - INTERVAL 7 DAY ORDER BY created_at DESC LIMIT 4");
      while ($p = $res->fetch_assoc()): ?>
        <div class="col-lg-3 col-md-6">
          <div class="product-card">
            <div class="position-relative">
              <img src="uploads/<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="card-img-top">
              <div class="position-absolute top-0 end-0 m-2">
                <span class="badge bg-success">New</span>
              </div>
            </div>
            <div class="card-body">
              <h6 class="product-title"><?= htmlspecialchars($p['name']) ?></h6>
              <p class="product-price">$<?= number_format($p['price'], 2) ?></p>
              <div class="d-flex gap-2">
                <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm flex-fill">
                  <i class="fas fa-eye me-1"></i>View
                </a>
                <button class="btn btn-primary btn-sm add-to-cart-btn" data-id="<?= $p['id'] ?>">
                  <i class="fas fa-cart-plus"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- Featured Collections with Filters -->
  <section class="mb-5">
    <div class="text-center mb-4">
      <h2 class="section-title">Featured Collections</h2>
      <p class="text-muted">Explore our curated selection of best-selling items</p>
    </div>

    <div class="filter-card">
      <form method="get" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-select">
            <option value="">All Categories</option>
            <?php
            $cats = $conn->query("SELECT id, name FROM categories");
            while ($cat = $cats->fetch_assoc()): ?>
              <option value="<?= $cat['id'] ?>" <?= ($_GET['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Gender</label>
          <select name="gender" class="form-select">
            <option value="">All Genders</option>
            <option value="men" <?= ($_GET['gender'] ?? '') === 'men' ? 'selected' : '' ?>>Men</option>
            <option value="women" <?= ($_GET['gender'] ?? '') === 'women' ? 'selected' : '' ?>>Women</option>
            <option value="kids" <?= ($_GET['gender'] ?? '') === 'kids' ? 'selected' : '' ?>>Kids</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Price Range</label>
          <select name="price_range" class="form-select">
            <option value="">All Prices</option>
            <option value="0-50">$0 - $50</option>
            <option value="50-100">$50 - $100</option>
            <option value="100-200">$100 - $200</option>
            <option value="200+">$200+</option>
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-filter me-2"></i>Apply Filters
          </button>
        </div>
      </form>
    </div>

    <div class="row g-4">
      <?php
      $where = ["classification = 'best_seller'"];
      if (!empty($_GET['category_id'])) $where[] = "category_id = " . intval($_GET['category_id']);
      if (!empty($_GET['gender'])) $where[] = "gender = '" . $conn->real_escape_string($_GET['gender']) . "'";
      
      // Price range filter
      if (!empty($_GET['price_range'])) {
        $range = $_GET['price_range'];
        if ($range === '0-50') $where[] = "price BETWEEN 0 AND 50";
        elseif ($range === '50-100') $where[] = "price BETWEEN 50 AND 100";
        elseif ($range === '100-200') $where[] = "price BETWEEN 100 AND 200";
        elseif ($range === '200+') $where[] = "price > 200";
      }
      
      $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
      $res = $conn->query("SELECT * FROM products $whereClause ORDER BY id DESC LIMIT 8");
      while ($p = $res->fetch_assoc()): ?>
        <div class="col-lg-3 col-md-6">
          <div class="product-card">
            <div class="position-relative">
              <img src="uploads/<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="card-img-top">
              <div class="position-absolute top-0 end-0 m-2">
                <span class="badge bg-warning">Best Seller</span>
              </div>
            </div>
            <div class="card-body">
              <h6 class="product-title"><?= htmlspecialchars($p['name']) ?></h6>
              <p class="product-price">$<?= number_format($p['price'], 2) ?></p>
              <div class="d-flex gap-2">
                <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm flex-fill">
                  <i class="fas fa-eye me-1"></i>View
                </a>
                <button class="btn btn-primary btn-sm add-to-cart-btn" data-id="<?= $p['id'] ?>">
                  <i class="fas fa-cart-plus"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
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
// Add to cart functionality
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const productId = this.dataset.id;
    const originalContent = this.innerHTML;
    
    // Loading state
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    this.disabled = true;
    
    fetch('cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `product_id=${productId}&ajax=1`
    })
    .then(res => res.text())
    .then(data => {
      // Show toast
      const toast = new bootstrap.Toast(document.getElementById('cartToast'));
      toast.show();
      
      // Update cart icon
      updateCartIcon();
      
      // Reset button
      this.innerHTML = originalContent;
      this.disabled = false;
    })
    .catch(error => {
      console.error('Error:', error);
      this.innerHTML = originalContent;
      this.disabled = false;
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

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});
</script>
</body>
</html>
