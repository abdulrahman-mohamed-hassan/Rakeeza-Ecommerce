-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 06, 2026 at 05:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `websitesystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `img` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `img`, `created_at`) VALUES
(1, 'Tables', 'assets/images/img2.avif', '2025-12-18 15:08:08'),
(2, 'Chairs', 'assets/images/seat.avif', '2025-12-18 15:08:08'),
(3, 'Drawers', 'assets/images/Drawers.avif', '2025-12-18 15:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipping_name` varchar(255) DEFAULT NULL,
  `shipping_email` varchar(255) DEFAULT NULL,
  `shipping_phone` varchar(50) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `created_at`, `shipping_name`, `shipping_email`, `shipping_phone`, `shipping_address`, `shipping_city`, `notes`, `payment_method`) VALUES
(1, 5, 144000.00, 'pending', '2025-12-17 14:57:02', 'drmohamed', 'drmohamed@gmail.com', '01010352340', 'واحد طريق سقاره السياحي شبرامنت\r\nالعماره رقم واحد الدور الثالث', 'شبرامنت', 'fu', 'cash'),
(2, 5, 16000.00, 'processing', '2025-12-17 17:16:24', 'drmohamed', 'elshimisultans@gmail.com', '01010352340', 'واحد طريق سقاره السياحي شبرامنت\r\nالعماره رقم واحد الدور الثالث', 'شبرامنت', 'FU', 'cash'),
(3, 6, 3500.00, 'processing', '2025-12-19 11:46:27', 'mohamed', 'mohamedhany05@gmail.com', '01010352345', 'واحد طريق سقاره السياحي شبرامنت', 'شبرامنت', '', 'cash');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 4, 2, 35000.00),
(2, 1, 2, 3, 8000.00),
(3, 1, 1, 2, 25000.00),
(4, 2, 2, 2, 8000.00),
(5, 3, 13, 1, 3500.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `img` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `img`, `description`, `created_at`, `category_id`) VALUES
(1, 'Solid Wood Table', 25000.00, 'assets/images/Solid Wood Table.avif', 'Premium oak dining table, handcrafted with elegant design. Perfect for family dinners.', '2025-12-17 14:36:59', NULL),
(2, 'Comfort Chair', 8000.00, 'assets/images/Comfort Chair.avif', 'Ergonomic office chair with lumbar support and soft cushioning.', '2025-12-17 14:36:59', NULL),
(3, 'Modern Lamp', 4000.00, 'assets/images/Modern Lamp.avif', 'Touch-controlled LED lamp with warm light and sleek design.', '2025-12-17 14:36:59', NULL),
(4, 'Cozy Sofa', 35000.00, 'assets/images/Cozy Sofa.avif', 'Luxurious 3-seater sofa with premium fabric and deep comfort.', '2025-12-17 14:36:59', NULL),
(5, 'Minimal Shelf', 10000.00, 'assets/images/Minimal Shelf.avif', 'Modern floating shelf, perfect for books and decoration.', '2025-12-17 14:36:59', NULL),
(6, 'Bench', 12000.00, 'assets/images/Bench.avif', 'Outdoor wooden bench, weather-resistant and stylish.', '2025-12-17 14:36:59', NULL),
(7, 'Stool', 2500.00, 'assets/images/Stool.avif', 'Minimalist bar stool with metal legs.', '2025-12-17 14:36:59', NULL),
(8, 'Cupboard', 20000.00, 'assets/images/Cupboard.avif', 'Large wardrobe with sliding doors and multiple shelves.', '2025-12-17 14:36:59', 2),
(9, 'Rug', 7000.00, 'assets/images/Rug.avif', 'Soft wool rug 200x300cm - adds warmth to any room.', '2025-12-17 14:36:59', 2),
(10, 'Wooden Dining Table', 3500.00, 'assets/images/1766081754_694444da9731b.jpeg', 'A strong dining table made from natural wood. It is perfect for family meals and adds warmth to any dining space.', '2025-12-18 18:15:54', 1),
(11, 'Modern Coffee Table', 3500.00, 'assets/images/1766082592_69444820987df.jpg', 'A stylish coffee table with a modern design. Ideal for living rooms, it provides space for drinks, books, and decorations.', '2025-12-18 18:29:52', 1),
(12, 'Office Work Table', 3500.00, 'assets/images/1766082595_69444823286c2.jpg', 'A practical work table designed for offices or study rooms. It offers a large surface area and a clean, professional look.', '2025-12-18 18:29:55', 1),
(13, 'Classic Wooden Chair', 3500.00, 'assets/images/1766082801_694448f1da0e5.jpg', 'A sturdy wooden chair designed for everyday use. It features a simple classic style that fits well in dining rooms, kitchens, or cafes.', '2025-12-18 18:33:21', 2);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `img` varchar(500) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `img`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 9, 'assets/images/1766079113_1_TWINNIS_Boho_Area_Rug_Vintage_Rug_Anti_Slip_Persian_Carpet_for_Living_Room_Bedroom_Red_4_x6_0e728c9b_166c_4658_8254_5688e585936a.0764fae229334a747f1f531f18140ae3.avif', 0, 1, '2025-12-18 17:31:53'),
(2, 9, 'assets/images/1766079113_2_imagess.jpg', 0, 2, '2025-12-18 17:31:53'),
(3, 10, 'assets/images/1766081754_694444da9731b.jpeg', 1, 0, '2025-12-18 18:15:54'),
(4, 11, 'assets/images/1766082592_69444820987df.jpg', 1, 0, '2025-12-18 18:29:52'),
(5, 11, 'assets/images/1766082593_1_694448217c852.jpg', 0, 1, '2025-12-18 18:29:53'),
(6, 11, 'assets/images/1766082594_2_6944482223f76.jpg', 0, 2, '2025-12-18 18:29:54'),
(7, 11, 'assets/images/1766082594_3_694448229accd.jpg', 0, 3, '2025-12-18 18:29:54'),
(8, 12, 'assets/images/1766082595_69444823286c2.jpg', 1, 0, '2025-12-18 18:29:55'),
(9, 12, 'assets/images/1766082595_1_694448239d11d.jpg', 0, 1, '2025-12-18 18:29:55'),
(10, 12, 'assets/images/1766082596_2_694448240ba38.jpg', 0, 2, '2025-12-18 18:29:56'),
(11, 12, 'assets/images/1766082596_3_694448248adfa.jpg', 0, 3, '2025-12-18 18:29:56'),
(12, 13, 'assets/images/1766082801_694448f1da0e5.jpg', 1, 0, '2025-12-18 18:33:21'),
(13, 13, 'assets/images/1766082802_1_694448f2543c0.jpg', 0, 1, '2025-12-18 18:33:22'),
(14, 13, 'assets/images/1766082802_2_694448f2c57ec.jpg', 0, 2, '2025-12-18 18:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `gender`, `dob`, `password`, `created_at`, `is_admin`) VALUES
(1, 'ahmed', 'adel', 'admin', 'ahmed@gmail.com', '01558655748', 'male', '2020-02-11', '$2y$10$XylO6UZ3JB9V3utY/ZfZfuMhxLcuhvdtrlSi0I3vao8r5BhBZOfWS', '2025-11-24 23:25:52', 0),
(2, 'to2ani', 'toka', 'to2ani', 'toka@gmail.com', '01558655748', 'female', '2025-11-03', '$2y$10$qkFCbVk5ucATCEvM1kZJl.2cx1f2jNXgMUB.CzDBBSSFA9S7O.5E2', '2025-11-25 15:49:56', 0),
(3, 'noor', 'amr', 'noor', 'amrfathy1010@gmail.com', '01010352340', 'male', '2006-12-03', '$2y$10$J9lAYvQT/J5/d6p4B1K7aeJP6cl26BtOkKOL1Dz4IflF3YOF7LyLm', '2025-12-16 11:22:55', 0),
(4, 'amr', 'noor', 'noor', 'amrfathy010@gmail.com', '01010352340', 'male', '2002-01-01', '$2y$10$.4iHyWT8BPBeH7mOt1aM1ekxxv/T6XkGtYFV8NixnasbTCmeS1vOm', '2025-12-16 12:19:52', 0),
(5, 'dr', 'mohamed', 'drmohamed', 'drmohamed@gmail.com', '01558655748', 'male', '2025-12-18', '$2y$10$H/VkEhgC7v1qu3E9vfgIWuHRkNSYGnGxysfTGRjX2VbZMAgBbz.ju', '2025-12-16 13:29:35', 0),
(6, 'mohamed', 'amr', 'mohamed', 'mohamedhany05@gmail.com', '01010352345', 'male', '2000-11-11', '$2y$10$RVxj13g6SMZkGpRpPRhks.bSY12DPDCeRlFDOX3xCW98N9UnmtH56', '2025-12-19 11:44:37', 0);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 5, 9, '2025-12-18 17:28:56');

--
-- Indexes for dumped tables
--

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
  ADD KEY `user_id` (`user_id`);

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
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
