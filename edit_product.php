<?php
session_start();
include 'includes/db.php';

// Handle redirects BEFORE any output
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_panel.php");
    exit;
}

$product_id = intval($_GET['id']);

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: admin_panel.php");
    exit;
}

$product = $result->fetch_assoc();
$success = "";
$errors = [];

// Fetch existing sizes for the product
$existing_sizes = [];
$sizeRes = $conn->query("SELECT size_id FROM product_sizes WHERE product_id = $product_id");
if ($sizeRes) {
    while ($s = $sizeRes->fetch_assoc()) {
        $existing_sizes[] = $s['size_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = trim($_POST['name']);
    $description    = trim($_POST['description']);
    $price          = floatval($_POST['price']);
    $category_id    = intval($_POST['category_id']);
    $subcategory_id = intval($_POST['subcategory_id']);
    $classification = $_POST['classification'];
    $model_year     = $_POST['model_year'];
    $gender         = $_POST['gender'];
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $sizes          = $_POST['sizes'] ?? [];
    $image          = $product['image'];

    if (empty($name) || empty($price)) {
        $errors[] = "Name and price are required.";
    } else {
        // Upload new image if exists
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $imageName = basename($_FILES['image']['name']);
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir);
            $targetFile = $targetDir . time() . "_" . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image = basename($targetFile);
            } else {
                $errors[] = "Image upload failed.";
            }
        }

        // Perform update
        if (empty($errors)) {
            $update = $conn->prepare("UPDATE products SET 
                name = ?, description = ?, price = ?, image = ?, category_id = ?, subcategory_id = ?, 
                classification = ?, model_year = ?, gender = ?, stock_quantity = ?
                WHERE id = ?");
            $update->bind_param("ssdsiissiii", $name, $description, $price, $image, $category_id, 
                $subcategory_id, $classification, $model_year, $gender, $stock_quantity, $product_id);

            if ($update->execute()) {
                // Update sizes if product_sizes table exists
                $conn->query("DELETE FROM product_sizes WHERE product_id = $product_id");
                
                if (!empty($sizes)) {
                    $sizeStmt = $conn->prepare("INSERT INTO product_sizes (product_id, size_id) VALUES (?, ?)");
                    foreach ($sizes as $size_id) {
                        $sizeStmt->bind_param("ii", $product_id, $size_id);
                        $sizeStmt->execute();
                    }
                    $sizeStmt->close();
                }

                $success = "Product updated successfully!";
            } else {
                $errors[] = "Error occurred while updating.";
            }
            $update->close();
        }
    }
}

// Set page title and include header AFTER processing
$pageTitle = 'Edit Product - Admin Panel';
include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="admin_panel.php">Admin Panel</a></li>
            <li class="breadcrumb-item active">Edit Product</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0">
                    <i class="fas fa-edit me-3"></i>Edit Product
                </h1>
                <a href="admin_panel.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
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
                                   value="<?= htmlspecialchars($product['name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price ($) *</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                       value="<?= $product['price']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                       value="<?= $product['stock_quantity'] ?? 0; ?>" min="0">
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
                                    $cats = $conn->query('SELECT id,name FROM categories ORDER BY name');
                                    while ($c = $cats->fetch_assoc()):
                                    ?>
                                        <option value="<?=$c['id']?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subcategory_id" class="form-label">Subcategory *</label>
                                <select class="form-select" id="subcategory_id" name="subcategory_id" required>
                                    <option value="">Select Subcategory</option>
                                    <?php
                                    $subs = $conn->query('SELECT id,name FROM subcategories ORDER BY name');
                                    while ($s = $subs->fetch_assoc()):
                                    ?>
                                        <option value="<?=$s['id']?>" <?= $s['id'] == $product['subcategory_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
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
                                    <option value="new" <?= $product['classification'] == 'new' ? 'selected' : '' ?>>New</option>
                                    <option value="best_seller" <?= $product['classification'] == 'best_seller' ? 'selected' : '' ?>>Best Seller</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="model_year" class="form-label">Model Year</label>
                                <input type="number" class="form-control" id="model_year" name="model_year" 
                                       value="<?= $product['model_year']; ?>" min="2000" max="<?= date('Y'); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="men" <?= $product['gender'] == 'men' ? 'selected' : '' ?>>Men</option>
                                    <option value="women" <?= $product['gender'] == 'women' ? 'selected' : '' ?>>Women</option>
                                    <option value="kids" <?= $product['gender'] == 'kids' ? 'selected' : '' ?>>Kids</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sizes -->
                        <div class="mb-3">
                            <label class="form-label">Available Sizes</label>
                            <div class="row">
                                <?php
                                $sizeQuery = $conn->query("SELECT id, name FROM sizes ORDER BY name");
                                if ($sizeQuery):
                                    while ($s = $sizeQuery->fetch_assoc()):
                                        $checked = in_array($s['id'], $existing_sizes) ? 'checked' : '';
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
                <!-- Current Image -->
                <?php if (!empty($product['image'])): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Current Image</h6>
                        </div>
                        <div class="card-body text-center">
                            <img src="uploads/<?= $product['image']; ?>" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Upload New Image -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-image me-2"></i>Product Image
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="image" class="form-label">Upload New Image (Optional)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Leave empty to keep current image</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="admin_panel.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
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
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.querySelector('select[name="category_id"]');
    const subcategorySelect = document.querySelector('select[name="subcategory_id"]');

    categorySelect.addEventListener('change', function () {
        const categoryId = this.value;
        subcategorySelect.innerHTML = '<option value="">Loading...</option>';

        if (categoryId) {
            fetch(`get_subcategories.php?category_id=${categoryId}`)
                .then(res => res.json())
                .then(data => {
                    subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                    data.forEach(sub => {
                        const opt = document.createElement('option');
                        opt.value = sub.id;
                        opt.textContent = sub.name;
                        subcategorySelect.appendChild(opt);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    subcategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
                });
        } else {
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
        }
    });
});
</script>
</body>
</html>
