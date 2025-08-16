<?php

$baseUrl = '/ecommerce_store';
if (!isset($pageTitle)) {
    $pageTitle = 'Alaa Fashion Store';
}
if (!isset($bodyClass)) {
    $bodyClass = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


    <!-- Vendor + template styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="./css/unified-styles.css" rel="stylesheet">

    
    <style>
        :root {
            --navbar-height: 80px;
        }
        
        .main-navbar {
            background: white;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 0;
            min-height: var(--navbar-height);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color) !important;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .navbar-brand:hover {
            color: var(--secondary-color) !important;
        }
        
        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--primary-color) !important;
            padding: 0.75rem 1rem !important;
            transition: all 0.3s ease;
            position: relative;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--secondary-color) !important;
        }
        
        .navbar-nav .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 2px;
        }
        
        .cart-link {
            position: relative;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
        
        .user-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: var(--border-radius);
            padding: 0.5rem 0;
            margin-top: 0.5rem;
        }
        
        .user-dropdown .dropdown-item {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .user-dropdown .dropdown-item:hover {
            background: var(--light-bg);
            color: var(--primary-color);
        }
        
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        .offcanvas-header {
            border-bottom: 1px solid #eee;
        }
        
        .offcanvas-body .navbar-nav {
            gap: 0;
        }
        
        .offcanvas-body .nav-link {
            border-bottom: 1px solid #f8f9fa;
            margin: 0;
            padding: 1rem !important;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        @media (max-width: 991px) {
            .navbar-nav .nav-link.active::after {
                display: none;
            }
        }

        .user-dropdown .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            z-index: 1000;
            min-width: 200px;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            font-size: 1rem;
            color: #212529;
            text-align: left;
            list-style: none;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
        }

        .user-dropdown .dropdown-menu.show {
            display: block;
        }

        .user-dropdown .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.255em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }
    </style>
</head>
<body class="<?= $bodyClass ?>">

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg main-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= $baseUrl ?>/index.php">
            <div class="logo-icon">A</div>
            <span>Alaa Fashion</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">
                    <div class="d-flex align-items-center gap-2">
                        <div class="logo-icon">K</div>
                        <span>Menu</span>
                    </div>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? ' active' : '' ?>" href="<?= $baseUrl ?>/index.php">
                            <i class="fas fa-home me-2 d-lg-none"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?= basename($_SERVER['PHP_SELF']) === 'products.php' ? ' active' : '' ?>" href="<?= $baseUrl ?>/products.php">
                            <i class="fas fa-store me-2 d-lg-none"></i>Shop
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link cart-link<?= basename($_SERVER['PHP_SELF']) === 'cart.php' ? ' active' : '' ?>" href="<?= $baseUrl ?>/cart.php">
                            <i class="fas fa-shopping-cart me-2"></i>
                            <span class="d-lg-none">Cart</span>
                            <span id="cart-count" class="cart-badge">0</span>
                        </a>
                    </li>
                    
                    <?php if (isset($_SESSION['username'])): ?>
                        <li class="nav-item dropdown user-dropdown">
                            <a class="nav-link d-flex align-items-center" 
                               href="#" 
                               onclick="toggleUserDropdown(event)"
                               id="userDropdownLink">
                                <i class="fas fa-user-circle me-2"></i>
                                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                                <i class="fas fa-chevron-down ms-2"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" id="userDropdownMenu">
                                <li><a class="dropdown-item" href="<?= $baseUrl ?>/orders.php">
                                    <i class="fas fa-box me-2"></i>My Orders
                                </a></li>
                               
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link<?= basename($_SERVER['PHP_SELF']) === 'login.php' ? ' active' : '' ?>" href="<?= $baseUrl ?>/login.php">
                                <i class="fas fa-sign-in-alt me-2 d-lg-none"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= basename($_SERVER['PHP_SELF']) === 'register.php' ? ' active' : '' ?>" href="<?= $baseUrl ?>/register.php">
                                <i class="fas fa-user-plus me-2 d-lg-none"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $baseUrl ?>/admin_panel.php">
                                <i class="fas fa-cog me-2"></i>Dashboard

                                <span class="d-lg-none">Admin Panel</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid px-0">
<!-- main content starts -->

<script>
// Initialize Bootstrap dropdowns
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    // Handle cart count
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        fetch('cart_count.php?rand=' + Math.random())
            .then(res => res.text())
            .then(count => {
                cartCountElement.textContent = count;
                if (count >0) {
                    cartCountElement.style.display = 'flex';
                } else {
                    cartCountElement.style.display = 'none';
                }
            })
            .catch(err => console.error("Error fetching cart count:", err));
    }
});

function toggleUserDropdown(event) {
    event.preventDefault();
    const menu = document.getElementById('userDropdownMenu');
    const isVisible = menu.classList.contains('show');
    
    // Close all other dropdowns
    document.querySelectorAll('.dropdown-menu.show').forEach(dropdown => {
        dropdown.classList.remove('show');
    });
    
    // Toggle current dropdown
    if (!isVisible) {
        menu.classList.add('show');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.user-dropdown');
    if (dropdown && !dropdown.contains(event.target)) {
        document.getElementById('userDropdownMenu').classList.remove('show');
    }
});
</script>
