<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// Handle redirects BEFORE any output
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = trim($_POST['name'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $price          = floatval($_POST['price'] ?? 0);
    $category_id    = intval($_POST['category_id'] ?? 0);
    $subcategory_id = intval($_POST['subcategory_id'] ?? 0);
    $classification = $_POST['classification'] ?? 'new';
    $model_year     = intval($_POST['model_year'] ?? 0);
    $gender         = $_POST['gender'] ?? 'men';
    $stock_status   = $_POST['stock_status'] ?? 'in_stock';
    $stock_qty      = intval($_POST['stock_quantity'] ?? 0);
    $sizes_selected = $_POST['sizes'] ?? [];
    $image          = null;

    // Check for duplicate product name
    $chk = $conn->prepare("SELECT id FROM products WHERE name = ? LIMIT 1");
    $chk->bind_param('s', $name);
    $chk->execute();
    if ($chk->get_result()->num_rows) {
        $errors[] = 'A product with this name already exists.';
    }

    if ($name === '' || $price <= 0 || !$category_id || !$subcategory_id) {
        $errors[] = 'Name, price, category, and subcategory are required.';
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $dir = __DIR__ . '/uploads/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $basename   = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $dir . $basename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $basename;
        } else {
            $errors[] = 'Failed to upload image.';
        }
    }

    if (!$errors) {
        $stmt = $conn->prepare(
            "INSERT INTO products
             (name, description, price, image,
              category_id, subcategory_id, classification,
              model_year, gender, stock_quantity)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param(
            'ssdsiisssi',
            $name, $description, $price, $image,
            $category_id, $subcategory_id, $classification,
            $model_year, $gender, $stock_qty
        );

        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;

            // Add sizes if available
            if (!empty($sizes_selected)) {
                $ps = $conn->prepare("INSERT INTO product_sizes (product_id, size_id) VALUES (?,?)");
                foreach ($sizes_selected as $sid) {
                    $ps->bind_param('ii', $product_id, $sid);
                    $ps->execute();
                }
                $ps->close();
            }

            $success = 'Product added successfully!';
            
            // Clear form data after successful submission
            $_POST = [];
        } else {
            $errors[] = 'Error while saving: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Set page title and include header AFTER processing
$pageTitle = 'Add Product - Admin Panel';
include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="admin_panel.php">Admin Panel</a></li>
            <li class="breadcrumb-item active">Add Product</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0">
                    <i class="fas fa-plus-circle me-3"></i>Add New Product
                </h1>
                <a href="admin_panel.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price ($) *</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                       value="<?= $_POST['price'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                       value="<?= $_POST['stock_quantity'] ?? 0 ?>" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-tags me-2"></i>Categories
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Main Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php
                                    $cats = $conn->query("SELECT id,name FROM categories ORDER BY name");
                                    while ($c = $cats->fetch_assoc()):
                                        $selected = ($_POST['category_id'] ?? '') == $c['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $c['id'] ?>" <?= $selected ?>><?= htmlspecialchars($c['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subcategory_id" class="form-label">Subcategory *</label>
                                <select class="form-select" id="subcategory_id" name="subcategory_id" required>
                                    <option value="">Select Subcategory</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>Product Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="classification" class="form-label">Classification</label>
                                <select class="form-select" id="classification" name="classification">
                                    <option value="new" <?= ($_POST['classification'] ?? 'new') == 'new' ? 'selected' : '' ?>>New</option>
                                    <option value="best_seller" <?= ($_POST['classification'] ?? '') == 'best_seller' ? 'selected' : '' ?>>Best Seller</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="model_year" class="form-label">Model Year</label>
                                <input type="number" class="form-control" id="model_year" name="model_year" 
                                       value="<?= $_POST['model_year'] ?? date('Y') ?>" min="2000" max="<?= date('Y') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="men" <?= ($_POST['gender'] ?? 'men') == 'men' ? 'selected' : '' ?>>Men</option>
                                    <option value="women" <?= ($_POST['gender'] ?? '') == 'women' ? 'selected' : '' ?>>Women</option>
                                    <option value="kids" <?= ($_POST['gender'] ?? '') == 'kids' ? 'selected' : '' ?>>Kids</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sizes -->
                        <div class="mb-3">
                            <label class="form-label">Available Sizes</label>
                            <div class="row">
                                <?php
                                $sizes = $conn->query("SELECT id,name FROM sizes ORDER BY name");
                                if ($sizes):
                                    while ($s = $sizes->fetch_assoc()):
                                        $checked = in_array($s['id'], $_POST['sizes'] ?? []) ? 'checked' : '';
                                ?>
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sizes[]" 
                                                   value="<?= $s['id'] ?>" id="size_<?= $s['id'] ?>" <?= $checked ?>>
                                            <label class="form-check-label" for="size_<?= $s['id'] ?>">
                                                <?= htmlspecialchars($s['name']) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php 
                                    endwhile;
                                endif; 
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Product Image -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-image me-2"></i>Product Image
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="image" class="form-label">Upload Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Recommended: 800x800px, JPG or PNG</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Product
                            </button>
                            <a href="admin_panel.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help -->
                <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Help
                        </h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Required fields:</strong> Name, Price, Category, and Subcategory are required to save the product.
                            <br><br>
                            <strong>Images:</strong> Upload high-quality images for better presentation.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const catSel = document.querySelector('[name="category_id"]');
    const subSel = document.querySelector('[name="subcategory_id"]');

    catSel.addEventListener('change', () => {
        const id = catSel.value;
        subSel.innerHTML = '<option value="">Loading...</option>';
        
        if (id) {
            fetch('get_subcategories.php?category_id=' + id)
                .then(r => r.json())
                .then(arr => {
                    subSel.innerHTML = '<option value="">Select Subcategory</option>';
                    arr.forEach(s => {
                        subSel.insertAdjacentHTML('beforeend', `<option value="${s.id}">${s.name}</option>`);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    subSel.innerHTML = '<option value="">Error loading subcategories</option>';
                });
        } else {
            subSel.innerHTML = '<option value="">Select Subcategory</option>';
        }
    });
});
</script>
</body>
</html>
