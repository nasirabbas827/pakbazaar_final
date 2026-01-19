-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 19, 2026 at 10:12 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pakbazaar_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `customer_id`, `product_id`, `quantity`, `created_at`) VALUES
(6, 2, 22, 1, '2026-01-19 08:51:34');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(2, 'Smartphones & Tablets', '2026-01-19 07:06:42'),
(3, 'Laptops & Computers', '2026-01-19 07:06:42'),
(4, 'Camera & Photography', '2026-01-19 07:06:42'),
(5, 'Audio & Headphones', '2026-01-19 07:06:42'),
(6, 'Men\'s Clothing', '2026-01-19 07:06:42'),
(7, 'Women\'s Clothing', '2026-01-19 07:06:42'),
(8, 'Footwear & Shoes', '2026-01-19 07:06:42'),
(9, 'Watches & Accessories', '2026-01-19 07:06:42'),
(10, 'Home Furniture', '2026-01-19 07:06:42'),
(11, 'Kitchen Appliances', '2026-01-19 07:06:42'),
(12, 'Home Decor', '2026-01-19 07:06:42'),
(13, 'Tools & Hardware', '2026-01-19 07:06:42'),
(14, 'Beauty & Personal Care', '2026-01-19 07:06:42'),
(15, 'Health & Wellness', '2026-01-19 07:06:42'),
(16, 'Toys & Games', '2026-01-19 07:06:42'),
(17, 'Sports & Outdoors', '2026-01-19 07:06:42'),
(18, 'Automotive Accessories', '2026-01-19 07:06:42'),
(19, 'Books & Stationery', '2026-01-19 07:06:42'),
(20, 'Groceries & Essentials', '2026-01-19 07:06:42'),
(21, 'Pet Supplies', '2026-01-19 07:06:42');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` enum('COD') DEFAULT 'COD',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `total_amount`, `order_status`, `payment_method`, `created_at`) VALUES
(1, 2, 73.00, 'pending', 'COD', '2026-01-19 08:03:03'),
(2, 2, 274.00, 'delivered', 'COD', '2026-01-19 08:09:58');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `supplier_id`, `quantity`, `price`) VALUES
(1, 1, 21, 1, 1, 18.00),
(2, 1, 22, 1, 1, 55.00),
(3, 2, 21, 1, 3, 18.00),
(4, 2, 22, 1, 4, 55.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `supplier_id`, `category_id`, `name`, `description`, `price`, `stock`, `low_stock_threshold`, `image`, `created_at`) VALUES
(3, 1, 2, 'iPhone 15 Pro Max', '256GB, Titanium Blue, Latest Model', 1199.00, 10, 2, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(4, 1, 3, 'MacBook Air M2', '8GB RAM, 256GB SSD, Silver', 999.00, 5, 2, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(5, 1, 4, 'Sony Alpha a7 IV', 'Full-frame Mirrorless Camera Body', 2499.00, 3, 1, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(6, 1, 5, 'Bose QuietComfort Ultra', 'Wireless Noise Cancelling Headphones', 429.00, 15, 5, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(7, 1, 6, 'Cotton Slim Fit Shirt', 'Premium white cotton shirt for men', 45.00, 50, 10, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(8, 1, 7, 'Floral Summer Dress', 'Lightweight breathable fabric for women', 35.50, 30, 5, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(9, 1, 8, 'Nike Air Max 270', 'Comfortable running and lifestyle shoes', 150.00, 20, 4, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(10, 1, 9, 'Rolex Submariner Date', 'Luxury diving watch, steel oyster', 9500.00, 2, 1, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(11, 1, 10, 'Modern Velvet Sofa', '3-seater navy blue velvet sofa', 750.00, 4, 2, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(12, 1, 11, 'Air Fryer XL', '6.5L capacity, digital touch screen', 120.00, 25, 5, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(14, 1, 13, 'Power Drill Set', '20V Cordless drill with 2 batteries', 110.00, 18, 5, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(15, 1, 14, 'Organic Face Serum', 'Hyaluronic acid and Vitamin C blend', 28.00, 100, 20, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(16, 1, 15, 'Yoga Mat Pro', 'Non-slip eco-friendly 6mm mat', 40.00, 45, 10, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(17, 1, 16, 'Remote Control Drone', '4K Camera, GPS return home feature', 299.00, 1, 2, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(18, 1, 17, 'Mountain Bike 29\"', 'Aluminum frame, 21-speed gears', 450.00, 6, 2, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(19, 1, 18, 'Car Dash Cam 4K', 'Front and rear dual recording', 89.00, 22, 5, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(20, 1, 19, 'Hardcover Journal', 'Leather bound, 200 ruled pages', 15.00, 150, 30, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(21, 1, 20, 'Extra Virgin Olive Oil', 'Cold pressed, 1 liter bottle', 18.00, 57, 15, '1768806759_App.jpg', '2026-01-19 07:15:05'),
(22, 1, 21, 'Orthopedic Dog Bed', 'Memory foam, washable cover large', 55.00, 6, 3, '1768806759_App.jpg', '2026-01-19 07:15:05');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `role` enum('supplier','customer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `city`, `status`, `role`, `created_at`) VALUES
(1, 'NASIR ABBAS', 'supplier@gmail.com', '$2y$10$nF5F72eKHSTsv9GGaVGePOjX7hcJmnacF/SSxzZLzHRCAxSMaVkpe', '+923176526827', 'Street jeff xxxx\r\nAp04', 'Salvador', 'active', 'supplier', '2026-01-19 06:46:20'),
(2, 'Customer', 'customer@gmail.com', '$2y$10$vGqB1ambDO8ExwAJiYpFWOBDm2PoOAm92SxHSvZnas.Gm9hqjfW2K', '+923176526823', 'P/o Basti Chhina Tehsil jampur District Rajanpur', 'Jampur', 'active', 'customer', '2026-01-19 07:23:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart` (`customer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_id` (`customer_id`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
