-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 02, 2025 at 09:15 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jellyscent`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `id` int(11) NOT NULL,
  `cart_user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size` varchar(10) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `is_selected` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_item`
--

INSERT INTO `cart_item` (`id`, `cart_user_id`, `product_id`, `product_name`, `quantity`, `size`, `price`, `total_price`, `is_selected`) VALUES
(1, 3, 20, 'Fresh Citrus', 1, '50ml', 199.00, 199.00, 0),
(2, 3, 20, 'Fresh Citrus', 1, '50ml', 199.00, 199.00, 0),
(3, 3, 18, 'Honey Vanilla', 1, '50ml', 199.00, 199.00, 0),
(4, 3, 20, 'Fresh Citrus', 1, '50ml', 199.00, 199.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(20) DEFAULT NULL,
  `transaction_id` varchar(20) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `barangay` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `region` varchar(50) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `best_selling` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `name`, `description`, `image`, `best_selling`, `created_at`, `updated_at`) VALUES
(2, 'Vanilla Sky', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683a73d6115b86.05967698.png', 1, '2025-05-31 03:13:26', '2025-05-31 04:20:38'),
(3, 'Berry Bliss', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683a75b07a2609.98241811.png', 1, '2025-05-31 03:21:20', '2025-05-31 06:52:56'),
(4, 'Lavender Fields', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683addbc433977.16383403.png', 1, '2025-05-31 10:45:16', '2025-05-31 10:51:04'),
(5, 'Jasmine Bouquet', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ade60c7ea97.31377343.png', 1, '2025-05-31 10:48:00', '2025-05-31 10:51:06'),
(6, 'Rose Blossom', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683adf06b31bc0.66896850.png', 0, '2025-05-31 10:50:46', '2025-05-31 10:50:46'),
(7, 'Lavender Breeze', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683adf945bf578.35668622.png', 0, '2025-05-31 10:53:08', '2025-05-31 10:53:08'),
(8, 'Peach Blossom', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683adfb5746480.57749362.png', 0, '2025-05-31 10:53:41', '2025-05-31 10:53:41'),
(9, 'Soft Cotton', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683adfd88a54d5.89016594.png', 0, '2025-05-31 10:54:16', '2025-05-31 10:54:16'),
(10, 'Ocean Breeze', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae097aad326.59406694.png', 0, '2025-05-31 10:57:27', '2025-05-31 10:57:27'),
(11, 'Wild Berry', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae0b53df203.48228260.png', 0, '2025-05-31 10:57:57', '2025-05-31 10:57:57'),
(12, 'Vanilla and Sandalwood', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae13cc5bef6.84903606.png', 0, '2025-05-31 11:00:12', '2025-05-31 11:00:12'),
(13, 'Lavender Vanilla', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae1698c9792.81825329.png', 0, '2025-05-31 11:00:57', '2025-05-31 11:00:57'),
(14, 'Sandalwood Spice', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae254e8b3e3.58778003.png', 0, '2025-05-31 11:04:52', '2025-05-31 11:04:52'),
(15, 'Tangerine', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae2b6f283a6.34286976.png', 1, '2025-05-31 11:06:30', '2025-05-31 11:12:58'),
(16, 'Lavender', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae2e2111566.93735951.png', 0, '2025-05-31 11:07:14', '2025-05-31 11:07:14'),
(17, 'Rose', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae300937cd4.35597634.png', 0, '2025-05-31 11:07:44', '2025-05-31 11:07:44'),
(18, 'Honey Vanilla', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae320e61720.56926778.png', 1, '2025-05-31 11:08:16', '2025-05-31 11:12:54'),
(19, 'Berry Delight', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae398cc05d1.17171583.png', 0, '2025-05-31 11:10:16', '2025-05-31 11:10:16'),
(20, 'Fresh Citrus', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae3ce8b6ee2.34922428.png', 1, '2025-05-31 11:11:10', '2025-05-31 11:13:01'),
(21, 'Sweet Berry', 'Jellyscent crafts fragrances that evoke serenity and tranquility. Our scents aim to transport you to haven of peace, promoting relaxation and well-being.', 'product_683ae42d60cd36.93594689.png', 0, '2025-05-31 11:12:45', '2025-05-31 11:51:36');

-- --------------------------------------------------------

--
-- Table structure for table `product_size`
--

CREATE TABLE `product_size` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_size`
--

INSERT INTO `product_size` (`id`, `product_id`, `size`, `price`, `stock_quantity`) VALUES
(4, 2, '50ml', 199.00, 100),
(5, 2, '100ml', 299.00, 100),
(6, 2, '150ml', 399.00, 100),
(7, 3, '50ml', 199.00, 100),
(8, 3, '100ml', 299.00, 100),
(9, 3, '150ml', 399.00, 100),
(10, 4, '50ml', 199.00, 100),
(11, 4, '100ml', 299.00, 100),
(12, 4, '150ml', 399.00, 100),
(13, 5, '50ml', 199.00, 100),
(14, 5, '100ml', 299.00, 100),
(15, 5, '150ml', 399.00, 100),
(16, 6, '50ml', 199.00, 100),
(17, 6, '100ml', 299.00, 100),
(18, 6, '150ml', 399.00, 100),
(19, 7, '50ml', 199.00, 100),
(20, 7, '100ml', 299.00, 100),
(21, 7, '150ml', 399.00, 100),
(22, 8, '50ml', 199.00, 100),
(23, 8, '100ml', 299.00, 100),
(24, 8, '150ml', 399.00, 100),
(25, 9, '50ml', 199.00, 100),
(26, 9, '100ml', 299.00, 100),
(27, 9, '150ml', 399.00, 100),
(28, 10, '50ml', 199.00, 100),
(29, 10, '100ml', 299.00, 100),
(30, 10, '150ml', 399.00, 100),
(31, 11, '50ml', 199.00, 100),
(32, 11, '100ml', 299.00, 100),
(33, 11, '150ml', 399.00, 100),
(34, 12, '50ml', 199.00, 100),
(35, 12, '100ml', 299.00, 100),
(36, 12, '150ml', 399.00, 100),
(37, 13, '50ml', 199.00, 100),
(38, 13, '100ml', 299.00, 100),
(39, 13, '150ml', 399.00, 100),
(40, 14, '50ml', 199.00, 100),
(41, 14, '100ml', 299.00, 100),
(42, 14, '150ml', 399.00, 100),
(43, 15, '50ml', 199.00, 100),
(44, 15, '100ml', 299.00, 100),
(45, 15, '150ml', 399.00, 100),
(46, 16, '50ml', 199.00, 100),
(47, 16, '100ml', 299.00, 100),
(48, 16, '150ml', 399.00, 100),
(49, 17, '50ml', 199.00, 100),
(50, 17, '100ml', 299.00, 100),
(51, 17, '150ml', 399.00, 100),
(52, 18, '50ml', 199.00, 100),
(53, 18, '100ml', 299.00, 100),
(54, 18, '150ml', 399.00, 100),
(55, 19, '50ml', 199.00, 100),
(56, 19, '100ml', 299.00, 100),
(57, 19, '150ml', 399.00, 100),
(58, 20, '50ml', 199.00, 100),
(59, 20, '100ml', 299.00, 100),
(60, 20, '150ml', 399.00, 100),
(61, 21, '50ml', 199.00, 100),
(62, 21, '100ml', 299.00, 100),
(63, 21, '150ml', 399.00, 100);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `USER_ID` int(11) NOT NULL,
  `USERNAME` varchar(255) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `ROLE` enum('buyer','admin') NOT NULL DEFAULT 'buyer',
  `FIRST_NAME` varchar(255) NOT NULL,
  `LAST_NAME` varchar(255) NOT NULL,
  `BIRTH_DATE` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `GENDER` enum('MALE','FEMALE','OTHER') NOT NULL,
  `COMP_ADDRESS` text NOT NULL,
  `BRGY` varchar(255) NOT NULL,
  `CITY` varchar(255) NOT NULL,
  `REGION` varchar(255) NOT NULL,
  `ZIPCODE` int(11) NOT NULL,
  `PHONE_NO` bigint(20) NOT NULL,
  `IMAGE_DP` varchar(255) NOT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`USER_ID`, `USERNAME`, `PASSWORD`, `EMAIL`, `ROLE`, `FIRST_NAME`, `LAST_NAME`, `BIRTH_DATE`, `GENDER`, `COMP_ADDRESS`, `BRGY`, `CITY`, `REGION`, `ZIPCODE`, `PHONE_NO`, `IMAGE_DP`, `CREATED_AT`) VALUES
(1, 'admin', '$2y$10$0loMLyy6bjQEO5BDAJSyTezYcdVN560.A2avC867S1sd/Lj/hAfGG', 'jellyscent_official@gmail.com', 'admin', 'Jellyscent', 'Admin', '2025-06-01 00:00:00', 'FEMALE', 'Kaybagal South, Tagaytay City, Cavite, 4100', 'Kaybagal South', 'Tagaytay City', 'Calabarzon', 4100, 9216548666, '', '2025-05-31 03:00:11'),
(3, 'buyer', '$2y$10$sTLArgxkDhJN/LhOgFcjie/j0SrBVgsEsosz9hGPGUa.gBp7KTzgm', 'joy_customer@gmail.com', 'buyer', 'Joy', 'Mirth', '2004-10-30 00:00:00', 'FEMALE', 'Mendez Crossing East, Tagaytay City, Cavite, 4100', 'Mendez Crossing East', 'Tagaytay City', 'Calabarzon', 4100, 9123456789, '', '2025-05-31 13:04:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_user_id` (`cart_user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `product_size`
--
ALTER TABLE `product_size`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`USER_ID`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `product_size`
--
ALTER TABLE `product_size`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `USER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`cart_user_id`) REFERENCES `users` (`USER_ID`),
  ADD CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`USER_ID`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `product_size`
--
ALTER TABLE `product_size`
  ADD CONSTRAINT `product_size_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
