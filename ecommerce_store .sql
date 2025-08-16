-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 30, 2025 at 01:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(4, 'Accessories'),
(3, 'Kids'),
(1, 'Men'),
(2, 'Women');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'processing',
  `payment_status` varchar(50) DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'cash',
  `shipping_method` varchar(50) DEFAULT 'standard',
  `shipping_address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `total`, `status`, `payment_status`, `payment_method`, `shipping_method`, `shipping_address`, `created_at`) VALUES
(1, 'ORD-2025-00001', 1, 90.00, 'delivered', 'completed', 'cash', 'standard', NULL, '2025-07-19 15:03:04'),
(2, 'ORD-2025-00002', 1, 100.00, 'delivered', 'completed', 'cash', 'standard', NULL, '2025-07-19 16:05:30'),
(3, 'ORD-2025-00003', 1, 120.00, 'delivered', 'completed', 'cash', 'standard', NULL, '2025-07-20 13:48:31'),
(4, 'ORD-2025-93286', 1, 30.00, 'delivered', 'completed', 'cash', 'standard', 'Demo Address\n123 Demo Street\nDemo City, Demo State 12345', '2025-07-21 17:54:17'),
(5, 'ORD-2025-85073', 1, 570.00, 'delivered', 'completed', 'cash', 'standard', 'Demo Address\n123 Demo Street\nDemo City, Demo State 12345', '2025-07-21 17:57:25'),
(6, 'ORD-2025-67904', 1, 120.00, 'delivered', 'completed', 'cash', 'standard', 'Demo Address\n123 Demo Street\nDemo City, Demo State 12345', '2025-07-21 17:59:26'),
(7, 'ORD-2025-60498', 1, 40.00, 'delivered', 'completed', 'cash', 'standard', 'Demo Address\n123 Demo Street\nDemo City, Demo State 12345', '2025-07-21 18:00:27'),
(8, 'ORD-2025-69015', 1, 40.00, 'delivered', 'completed', 'cash', 'standard', 'Demo Address\n123 Demo Street\nDemo City, Demo State 12345', '2025-07-21 18:01:47'),
(9, 'ORD-2025-61679', 1, 32.40, 'delivered', 'completed', 'cash', 'standard', 'Default Address', '2025-07-21 18:27:41'),
(10, 'ORD-2025-52407', 1, 32.40, 'delivered', 'completed', 'cash', 'standard', 'Default Address', '2025-07-21 18:34:22'),
(11, 'ORD-2025-65366', 1, 86.40, 'delivered', 'completed', 'cash', 'standard', 'Default Address', '2025-07-22 13:13:48'),
(12, 'ORD-2025-17719', 1, 97.20, 'delivered', 'completed', 'card', 'standard', 'Default Address', '2025-07-22 16:13:44');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `quantity`) VALUES
(1, 1, 1, 30.00, 3),
(2, 2, 1, 30.00, 2),
(3, 2, 2, 40.00, 1),
(4, 3, 2, 40.00, 3),
(5, 4, 1, 30.00, 1),
(6, 5, 1, 30.00, 19),
(7, 6, 1, 30.00, 4),
(8, 7, 2, 40.00, 1),
(9, 8, 2, 40.00, 1),
(10, 9, 1, 30.00, 1),
(11, 10, 1, 30.00, 1),
(12, 11, 2, 40.00, 2),
(13, 12, 3, 10.00, 4),
(14, 12, 4, 50.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `classification` enum('new','used','best_seller') DEFAULT NULL,
  `model_year` year(4) DEFAULT NULL,
  `gender` enum('men','women','kids','') NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stock_status` enum('in_stock','low_stock','out_of_stock') NOT NULL DEFAULT 'in_stock',
  `stock_quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category_id`, `subcategory_id`, `classification`, `model_year`, `gender`, `created_at`, `stock_status`, `stock_quantity`) VALUES
(1, 't-shirt', 'dsaklfjasdklafv;lkdkjlf;vasdi; jvkasdklj;cvk;ladfg hj;edfajklhbfdbdf', 30.00, '1753014721_image.png', 1, 1, 'new', '2025', 'men', '2025-07-19 12:01:58', 'in_stock', 100),
(2, 't-shirt-white', 'fdsgdf', 40.00, '1753179898_banner-image-4.jpg', 1, 1, 'new', '2025', 'men', '2025-07-19 12:10:49', 'in_stock', 200),
(3, 'test', 'test test', 10.00, '1753179859_banner-image-1.jpg', 2, 3, 'best_seller', '2025', 'women', '2025-07-22 10:24:19', 'in_stock', 100),
(4, 'dress', 'testtesttesttesttesttesttesttest', 50.00, '1753180149_banner-image-5.jpg', 2, 3, 'best_seller', '2025', 'women', '2025-07-22 10:29:09', 'in_stock', 100);

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `product_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`product_id`, `size_id`) VALUES
(1, 1),
(1, 3),
(1, 4),
(1, 6),
(1, 9),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(4, 2),
(4, 3),
(4, 4),
(4, 5);

-- --------------------------------------------------------

--
-- Table structure for table `sizes`
--

CREATE TABLE `sizes` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sizes`
--

INSERT INTO `sizes` (`id`, `name`) VALUES
(7, '2-3Y'),
(8, '4-5Y'),
(4, 'L'),
(3, 'M'),
(9, 'One Size'),
(2, 'S'),
(5, 'XL'),
(1, 'XS'),
(6, 'XXL');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `category_id`, `name`) VALUES
(1, 1, 'T-Shirts'),
(2, 1, 'Jackets'),
(3, 2, 'Dresses'),
(4, 2, 'Skirts'),
(5, 3, 'Baby Wear'),
(6, 3, 'School Uniforms'),
(7, 4, 'Bags'),
(8, 4, 'Sunglasses'),
(9, 1, 'Shirts'),
(10, 1, 'Pants'),
(11, 1, 'Shorts'),
(12, 1, 'Hoodies'),
(13, 2, 'Blouses'),
(14, 2, 'Jeans'),
(15, 2, 'Coats'),
(16, 2, 'Activewear'),
(17, 3, 'Kids T-Shirts'),
(18, 3, 'Kids Pants'),
(19, 3, 'Kids Outerwear'),
(20, 4, 'Hats'),
(21, 4, 'Scarves'),
(22, 4, 'Belts');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'alaa', 'sjamal.2020@gmail.com', '$2y$10$aCs8Xpa4MuKsqaYwHyrN9O9raZ23GD8bhZ./kbvOCJeRmSEU4tQQq', 'admin', '2025-07-22 10:43:30', '2025-07-22 10:43:30'),
(2, 'alaa', 'gmail@gmail.com', '$2y$10$jia69dHiEm.B9NqqioWanOyhCS0j1oUoCrhoAiDYYFoB.0Skz9HyW', 'user', '2025-07-22 10:43:30', '2025-07-22 10:43:30'),
(3, 'admin', 'admin@demo.com', '$2y$10$/YO8wgKTkKpT.dPlo0qQde7cw.Dd8krTodamsuYSpgqJBqWuAzOJy', 'admin', '2025-07-22 10:43:30', '2025-07-22 10:43:30'),
(4, 'user', 'user@demo.com', '$2y$10$H2UX0vfmGnO/nUPcaMaSYucjErN2WtRkN1AOYsHSZQyADT9LmuU.u', 'user', '2025-07-22 10:43:30', '2025-07-22 10:43:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`product_id`,`size_id`),
  ADD KEY `size_id` (`size_id`);

--
-- Indexes for table `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_sizes_ibfk_2` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
