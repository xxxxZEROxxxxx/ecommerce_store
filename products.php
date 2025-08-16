<?php
session_start();
include 'includes/db.php';

$q      = $_GET['q']        ?? '';
$cat    = $_GET['category'] ?? '';
$year   = $_GET['year']     ?? '';
$class  = $_GET['class']    ?? '';
$gender = $_GET['gender']   ?? '';
$sort   = $_GET['sort']     ?? 'newest';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';

$sql = "SELECT p.*, c.name AS category, s.name AS sub
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN subcategories s ON p.subcategory_id = s.id";

$where = [];
$params = [];
$types = '';

if ($q !== '') {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $types  .= 'ss';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($cat !== '') {
    $where[] = 'p.category_id = ?';
    $types  .= 'i';
    $params[] = $cat;
}
if ($year !== '') {
    $where[] = 'p.model_year = ?';
    $types  .= 's';
    $params[] = $year;
}
if ($class !== '') {
    $where[] = 'p.classification = ?';
    $types  .= 's';
    $params[] = $class;
}
if ($gender !== '') {
    $where[] = 'p.gender = ?';
    $types  .= 's';
    $params[] = $gender;
}
if ($price_min !== '') {
    $where[] = 'p.price >= ?';
    $types  .= 'd';
    $params[] = floatval($price_min);
}
if ($price_max !== '') {
    $where[] = 'p.price <= ?';
    $types  .= 'd';
    $params[] = floatval($price_max);
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

// Sorting
switch ($sort) {
    case 'price_low':
        $sql .= ' ORDER BY p.price ASC';
        break;
    case 'price_high':
        $sql .= ' ORDER BY p.price DESC';
        break;
    case 'name':
        $sql .= ' ORDER BY p.name ASC';
        break;
    case 'oldest':
        $sql .= ' ORDER BY p.created_at ASC';
        break;
    default:
        $sql .= ' ORDER BY p.created_at DESC';
}

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$total_products = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop - Alaa Fashion Store</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="assets/unified-style.css" rel="stylesheet">
  <style>
    .shop-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 4rem 0 2rem;
    }
    .filter-sidebar {
      background: white;
      border-radius: var(--border-radius);
      padding: 1.5rem;
      box-shadow: var(--box-shadow);
      height: fit-content;
      position: sticky;
      top: 20px;
    }
    .filter-section {
      margin-bottom: 2rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid #eee;
    }
    .filter-section:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }
    .filter-title {
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--primary-color);
    }
    .price-range-inputs {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }
    .price-range-inputs input {
      flex: 1;
    }
    .products-header {
      background: white;
      border-radius: var(--border-radius);
      padding: 1rem 1.5rem;
      box-shadow: var(--box-shadow);
      margin-bottom: 2rem;
    }
    .view-toggle {
      display: flex;
      gap: 0.5rem;
    }
    .view-toggle .btn {
      padding: 0.5rem 0.75rem;
    }
    .grid-view .row {
      --bs-gutter-x: 1.5rem;
      --bs-gutter-y: 1.5rem;
    }
    .list-view .product-card {
      display: flex;
      flex-direction: row;
      height: auto;
    }
    .list-view .product-card img {
      width: 200px;
      height: 150px;
      flex-shrink: 0;
    }
    .list-view .card-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .no-products {
      text-align: center;
      padding: 4rem 2rem;
      color: #666;
    }
    .clear-filters {
      color: var(--accent-color);
      text-decoration: none;
      font-size: 0.9rem;
    }
    .clear-filters:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<!-- Shop Header -->
<div class="shop-header">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h1 class="display-4 fw-bold mb-2">Our Shop</h1>
        <p class="lead mb-0">Discover amazing products tailored just for you</p>
      </div>
      <div class="col-md-4 text-md-end">
        <div class="d-flex align-items-center justify-content-md-end gap-3">
          <i class="fas fa-shopping-bag fa-3x opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container py-4">
  <div class="row">
    <!-- Filters Sidebar -->
    <div class="col-lg-3 mb-4">
      <div class="filter-sidebar">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Filters</h5>
          <?php if ($q || $cat || $year || $class || $gender || $price_min || $price_max): ?>
            <a href="products.php" class="clear-filters">
              <i class="fas fa-times me-1"></i>Clear All
            </a>
          <?php endif; ?>
        </div>

        <form method="get" id="filterForm">
          <!-- Search -->
          <div class="filter-section">
            <h6 class="filter-title">Search</h6>
            <div class="input-group">
              <input type="text" name="q" class="form-control" placeholder="Search products..." 
                     value="<?= htmlspecialchars($q) ?>">
              <button class="btn btn-outline-secondary" type="submit">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>

          <!-- Category -->
          <div class="filter-section">
            <h6 class="filter-title">Category</h6>
            <select name="category" class="form-select">
              <option value="">All Categories</option>
              <?php
              $cats = $conn->query("SELECT id, name FROM categories ORDER BY name");
              while ($row = $cats->fetch_assoc()):
              ?>
                <option value="<?= $row['id'] ?>" <?= $cat == $row['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($row['name']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Gender -->
          <div class="filter-section">
            <h6 class="filter-title">Gender</h6>
            <div class="d-flex flex-column gap-2">
              <?php
              $genders = [
                'men' => 'Men',
                'women' => 'Women', 
                'kids' => 'Kids'
              ];
              foreach ($genders as $value => $label):
              ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="gender" 
                         value="<?= $value ?>" id="gender_<?= $value ?>"
                         <?= $gender === $value ? 'checked' : '' ?>>
                  <label class="form-check-label" for="gender_<?= $value ?>">
                    <?= $label ?>
                  </label>
                </div>
              <?php endforeach; ?>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="gender" 
                       value="" id="gender_all" <?= $gender === '' ? 'checked' : '' ?>>
                <label class="form-check-label" for="gender_all">All</label>
              </div>
            </div>
          </div>

          <!-- Price Range -->
          <div class="filter-section">
            <h6 class="filter-title">Price Range</h6>
            <div class="price-range-inputs">
              <input type="number" name="price_min" class="form-control" 
                     placeholder="Min" value="<?= htmlspecialchars($price_min) ?>">
              <span>-</span>
              <input type="number" name="price_max" class="form-control" 
                     placeholder="Max" value="<?= htmlspecialchars($price_max) ?>">
            </div>
          </div>

          <!-- Classification -->
          <div class="filter-section">
            <h6 class="filter-title">Type</h6>
            <select name="class" class="form-select">
              <option value="">All Types</option>
              <option value="new" <?= $class == 'new' ? 'selected' : '' ?>>New</option>
              <option value="best_seller" <?= $class == 'best_seller' ? 'selected' : '' ?>>Best Seller</option>
            </select>
          </div>

          <!-- Model Year -->
          <div class="filter-section">
            <h6 class="filter-title">Model Year</h6>
            <input type="number" name="year" class="form-control" 
                   placeholder="e.g. 2024" value="<?= htmlspecialchars($year) ?>" 
                   min="2000" max="<?= date('Y') ?>">
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-filter me-2"></i>Apply Filters
          </button>
        </form>
      </div>
    </div>

    <!-- Products Area -->
    <div class="col-lg-9">
      <!-- Products Header -->
      <div class="products-header">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h5 class="mb-0">
              <?= $total_products ?> Product<?= $total_products !== 1 ? 's' : '' ?> Found
              <?php if ($q): ?>
                for "<?= htmlspecialchars($q) ?>"
              <?php endif; ?>
            </h5>
          </div>
          <div class="col-md-6">
            <div class="d-flex justify-content-md-end align-items-center gap-3">
              <!-- Sort -->
              <select name="sort" class="form-select" style="width: auto;" onchange="updateSort(this.value)">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A-Z</option>
              </select>
              
              <!-- View Toggle -->
              <div class="view-toggle">
                <button class="btn btn-outline-secondary active" id="gridViewBtn" onclick="toggleView('grid')">
                  <i class="fas fa-th"></i>
                </button>
                <button class="btn btn-outline-secondary" id="listViewBtn" onclick="toggleView('list')">
                  <i class="fas fa-list"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div id="productsContainer" class="grid-view">
        <?php if ($total_products > 0): ?>
          <div class="row">
            <?php while ($p = $result->fetch_assoc()): ?>
              <div class="col-lg-4 col-md-6 mb-4">
                <div class="product-card">
                  <div class="position-relative">
                    <img src="uploads/<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="card-img-top">
                    <div class="position-absolute top-0 end-0 m-2">
                      <?php if ($p['classification'] === 'best_seller'): ?>
                        <span class="badge bg-warning">Best Seller</span>
                      <?php else: ?>
                        <span class="badge bg-success">New</span>
                      <?php endif; ?>
                    </div>
                    <div class="position-absolute top-0 start-0 m-2">
                      <button class="btn btn-sm btn-light rounded-circle wishlist-btn" data-id="<?= $p['id'] ?>">
                        <i class="fas fa-heart"></i>
                      </button>
                    </div>
                  </div>
                  <div class="card-body">
                    <h6 class="product-title"><?= htmlspecialchars($p['name']) ?></h6>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($p['category'] ?? 'N/A') ?></p>
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
        <?php else: ?>
          <div class="no-products">
            <i class="fas fa-search fa-4x mb-3 text-muted"></i>
            <h4>No products found</h4>
            <p>Try adjusting your filters or search terms</p>
            <a href="products.php" class="btn btn-primary">View All Products</a>
          </div>
        <?php endif; ?>
      </div>
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
// Add to cart functionality
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const productId = this.dataset.id;
    const originalContent = this.innerHTML;
    
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    this.disabled = true;
    
    fetch('cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `product_id=${productId}&ajax=1`
    })
    .then(res => res.text())
    .then(data => {
      const toast = new bootstrap.Toast(document.getElementById('cartToast'));
      toast.show();
      updateCartIcon();
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

// Wishlist functionality
document.querySelectorAll('.wishlist-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    this.classList.toggle('text-danger');
    const icon = this.querySelector('i');
    icon.classList.toggle('fas');
    icon.classList.toggle('far');
  });
});

// View toggle
function toggleView(view) {
  const container = document.getElementById('productsContainer');
  const gridBtn = document.getElementById('gridViewBtn');
  const listBtn = document.getElementById('listViewBtn');
  
  if (view === 'grid') {
    container.className = 'grid-view';
    gridBtn.classList.add('active');
    listBtn.classList.remove('active');
  } else {
    container.className = 'list-view';
    listBtn.classList.add('active');
    gridBtn.classList.remove('active');
  }
}

// Sort functionality
function updateSort(sortValue) {
  const url = new URL(window.location);
  url.searchParams.set('sort', sortValue);
  window.location = url;
}

function updateCartIcon() {
  fetch('cart_count.php')
    .then(res => res.text())
    .then(count => {
      const badge = document.getElementById('cart-count');
      if (badge) badge.textContent = count;
    });
}

// Auto-submit form on filter change
document.querySelectorAll('#filterForm select, #filterForm input[type="radio"]').forEach(element => {
  element.addEventListener('change', function() {
    document.getElementById('filterForm').submit();
  });
});
</script>
</body>
</html>
