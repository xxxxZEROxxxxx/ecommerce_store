<?php
session_start();
include 'includes/db.php';

// السماح فقط للمسؤول
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// حذف منتج
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $deleteSuccess = true;
}

// جلب إحصائيات
$stats = [];
$stats['products'] = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$stats['orders'] = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'] ?? 0;
$stats['users'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$stats['revenue'] = $conn->query("SELECT SUM(total) as total FROM orders")->fetch_assoc()['total'] ?? 0;

// جلب المنتجات
$result = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Clothing Store</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/unified-styles.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .table-actions {
            white-space: nowrap;
        }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>



<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-cog me-2"></i>Admin Panel
                    </h5>
                    <ul class="list-unstyled">
                        <li><a href="admin_panel.php" class="text-decoration-none"><i class="fas fa-chart-bar me-2"></i>Dashboard</a></li>
                        <li><a href="add_product.php" class="text-decoration-none"><i class="fas fa-plus me-2"></i>Add Product</a></li>
                        <li><a href="admin_orders.php" class="text-decoration-none"><i class="fas fa-shopping-cart me-2"></i>Orders</a></li>
                        <li><a href="admin_users.php" class="text-decoration-none"><i class="fas fa-users me-2"></i>Users</a></li>
                        <li><a href="index.php" class="text-decoration-none"><i class="fas fa-home me-2"></i>Store Front</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card dashboard-card text-center">
                        <div class="card-body">
                            <i class="fas fa-box stat-icon text-primary"></i>
                            <h3 class="card-title"><?= $stats['products'] ?></h3>
                            <p class="card-text text-muted">Total Products</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card dashboard-card text-center">
                        <div class="card-body">
                            <i class="fas fa-shopping-cart stat-icon text-success"></i>
                            <h3 class="card-title"><?= $stats['orders'] ?></h3>
                            <p class="card-text text-muted">Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card dashboard-card text-center">
                        <div class="card-body">
                            <i class="fas fa-users stat-icon text-info"></i>
                            <h3 class="card-title"><?= $stats['users'] ?></h3>
                            <p class="card-text text-muted">Users</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card dashboard-card text-center">
                        <div class="card-body">
                            <i class="fas fa-dollar-sign stat-icon text-warning"></i>
                            <h3 class="card-title">$<?= number_format($stats['revenue'], 2) ?></h3>
                            <p class="card-text text-muted">Revenue</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Products Management
                    </h5>
                    <a href="add_product.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Add New Product
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($deleteSuccess)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Product deleted successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($row['image'])): ?>
                                                <img src="uploads/<?= $row['image']; ?>" class="product-img" alt="Product">
                                            <?php else: ?>
                                                <div class="product-img bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars(substr($row['description'], 0, 50)); ?>...</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($row['category_name'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($row['price'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($row['stock_status'] == 'in_stock'): ?>
                                                <span class="badge bg-success">In Stock</span>
                                            <?php elseif ($row['stock_status'] == 'low_stock'): ?>
                                                <span class="badge bg-warning">Low Stock</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="table-actions">
                                            <a href="product.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_product.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="admin_panel.php?delete=<?= $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this product?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>