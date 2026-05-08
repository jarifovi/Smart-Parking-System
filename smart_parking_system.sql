-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 10:24 PM
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
-- Database: `smart_parking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `spot_id` int(11) NOT NULL,
  `vehicle_label` varchar(50) NOT NULL,
  `vehicle_type` enum('Car','Bike','VIP') NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `spot_id`, `vehicle_label`, `vehicle_type`, `start_time`, `end_time`, `status`, `amount`, `created_at`) VALUES
(1, 2, 7, '125601', 'Car', '2025-12-07 11:30:00', '2025-12-08 03:30:00', 'active', 960.00, '2025-12-06 03:40:08'),
(3, 2, 1, '125601-2', 'Car', '2025-12-10 09:00:00', '2025-12-10 17:11:00', 'completed', 409.17, '2025-12-09 01:12:06'),
(4, 2, 2, '125601-28', 'Car', '2025-12-10 08:00:00', '2025-12-10 16:30:00', 'completed', 425.00, '2025-12-09 01:24:46'),
(5, 2, 6, '125601-29', 'Bike', '2025-12-11 13:26:00', '2025-12-11 15:26:00', 'completed', 120.00, '2025-12-09 01:26:57'),
(6, 2, 6, '125601-30', 'Car', '2025-12-10 11:00:00', '2025-12-10 17:00:00', 'completed', 360.00, '2025-12-09 02:55:49'),
(7, 2, 5, '125601-211', 'Car', '2025-12-11 10:00:00', '2025-12-11 18:00:00', 'completed', 800.00, '2025-12-09 02:57:02'),
(8, 2, 4, '3209', 'Bike', '2025-12-12 15:00:00', '2025-12-12 16:00:00', 'completed', 20.00, '2025-12-09 03:13:53'),
(9, 2, 8, '58383', 'Bike', '2025-12-12 15:15:00', '2025-12-12 19:20:00', 'completed', 102.08, '2025-12-09 03:18:00'),
(10, 2, 3, '88494', 'Bike', '2025-12-14 10:25:00', '2025-12-14 20:19:00', 'completed', 198.00, '2025-12-09 03:20:02');

-- --------------------------------------------------------

--
-- Table structure for table `parking_spots`
--

CREATE TABLE `parking_spots` (
  `id` int(11) NOT NULL,
  `spot_number` varchar(10) NOT NULL,
  `floor_number` int(11) NOT NULL DEFAULT 0,
  `spot_type` enum('Car','Bike','VIP') NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parking_spots`
--

INSERT INTO `parking_spots` (`id`, `spot_number`, `floor_number`, `spot_type`, `hourly_rate`, `is_active`) VALUES
(1, 'A1', 0, 'Car', 50.00, 1),
(2, 'A2', 0, 'Car', 50.00, 1),
(3, 'B1', 0, 'Bike', 20.00, 1),
(4, 'B2', 0, 'Bike', 20.00, 1),
(5, 'V1', 0, 'VIP', 100.00, 1),
(6, 'A3', 1, 'Car', 60.00, 1),
(7, 'A4', 1, 'Car', 60.00, 1),
(8, 'B3', 1, 'Bike', 25.00, 1),
(9, 'B4', 1, 'Bike', 25.00, 1),
(10, 'V2', 1, 'VIP', 120.00, 1),
(18, 'V3', 1, 'VIP', 100.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 1, '571f302597a9a2ba6ea6e1c9622f4a0f663725b6f27ad986ff3725558825b620', '2025-12-06 00:13:07', '2025-12-06 04:13:32', '2025-12-06 04:13:07');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Bkash','Nagad','Rocket','Cash','Card','PayPal','BankTransfer') NOT NULL,
  `payment_status` enum('paid','pending') NOT NULL DEFAULT 'paid',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `user_id`, `transaction_id`, `amount`, `payment_method`, `payment_status`, `created_at`) VALUES
(3, 4, 2, 'TX17652219601412', 425.00, 'Bkash', 'paid', '2025-12-09 01:26:00'),
(4, 5, 2, 'TX17652220404470', 120.00, 'Nagad', 'paid', '2025-12-09 01:27:20'),
(5, 3, 2, 'TX17652229944024', 409.17, 'Nagad', 'paid', '2025-12-09 01:43:14'),
(6, 6, 2, 'TX17652273723568', 360.00, 'Bkash', 'paid', '2025-12-09 02:56:12'),
(7, 7, 2, 'TX17652274439029', 800.00, 'Nagad', 'paid', '2025-12-09 02:57:23'),
(8, 8, 2, 'TX17652284506412', 20.00, 'Rocket', 'paid', '2025-12-09 03:14:10'),
(9, 9, 2, 'TX17652287574213', 102.08, 'Rocket', 'paid', '2025-12-09 03:19:17'),
(10, 10, 2, 'TX17652288218285', 198.00, 'Bkash', 'paid', '2025-12-09 03:20:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `is_admin`, `created_at`) VALUES
(1, 'Admin User', 'admin@gmail.com', '$2y$10$K5rYwE8DRJHPPkKNxYBUcuekRSVh7fGIz.NhfzlpnQb0oeMfjSEka', 1, '2025-12-06 02:24:22'),
(2, 'Riyad Hasan', 'hassanriyad666@gmail.com', '$2y$10$InR2uPoPaNqapWVDx8njLuME6uHWukXQx5SKMYAmAuLhkbgbxyrwi', 0, '2025-12-06 02:26:02');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_notifications`
--

INSERT INTO `user_notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'Payment of $425.00 via Bkash for booking #4 was successful.', 0, '2025-12-09 01:26:00'),
(2, 2, 'Payment of $120.00 via Nagad for booking #5 was successful.', 0, '2025-12-09 01:27:20'),
(3, 2, 'Payment of $409.17 via Nagad for booking #3 was successful.', 0, '2025-12-09 01:43:14'),
(4, 2, 'Payment of $360.00 via Bkash for booking #6 was successful.', 0, '2025-12-09 02:56:12'),
(5, 2, 'Payment of $800.00 via Nagad for booking #7 was successful.', 0, '2025-12-09 02:57:23'),
(6, 2, 'Payment of $20.00 via Rocket for booking #8 was successful.', 0, '2025-12-09 03:14:10'),
(7, 2, 'Payment of $102.08 via Rocket for booking #9 was successful.', 0, '2025-12-09 03:19:17'),
(8, 2, 'Payment of $198.00 via Bkash for booking #10 was successful.', 0, '2025-12-09 03:20:21');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `user_id` int(11) NOT NULL,
  `confirm_booking` tinyint(1) NOT NULL DEFAULT 1,
  `reminder_booking` tinyint(1) NOT NULL DEFAULT 1,
  `expiry_alert` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `spot_id` (`spot_id`);

--
-- Indexes for table `parking_spots`
--
ALTER TABLE `parking_spots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `spot_number` (`spot_number`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token` (`token`),
  ADD KEY `fk_password_resets_user` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `parking_spots`
--
ALTER TABLE `parking_spots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`spot_id`) REFERENCES `parking_spots` (`id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
