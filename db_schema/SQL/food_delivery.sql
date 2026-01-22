-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 22, 2026 at 11:08 PM
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
-- Database: `food_delivery`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `id` int(11) NOT NULL,
  `unit_number` varchar(20) DEFAULT NULL,
  `street_number` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL COMMENT 'a backup address',
  `city` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `postal_code` varchar(100) NOT NULL,
  `country_id` int(11) DEFAULT NULL COMMENT 'match the ID type in the other table'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`id`, `unit_number`, `street_number`, `address_line1`, `address_line2`, `city`, `region`, `postal_code`, `country_id`) VALUES
(1, '5B', '100', 'Via del Postman', '', '', '', '00186', 1);

-- --------------------------------------------------------

--
-- Table structure for table `country`
--

CREATE TABLE `country` (
  `id` int(11) NOT NULL,
  `country_name` varchar(50) NOT NULL COMMENT 'just the name of the country'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `country`
--

INSERT INTO `country` (`id`, `country_name`) VALUES
(1, 'Italy');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `nick_name` varchar(100) DEFAULT NULL,
  `phone_number_original` varchar(50) DEFAULT NULL COMMENT 'User-provider phone number',
  `phone_number_normalized` varchar(50) DEFAULT NULL COMMENT 'Digits only, for indexing',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_address`
--

CREATE TABLE `customer_address` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL COMMENT 'FK to refer to the customer',
  `address_id` int(11) NOT NULL COMMENT 'FK to refer to the address'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_drivers`
--

CREATE TABLE `delivery_drivers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone_number_original` varchar(50) DEFAULT NULL COMMENT 'Driver-provided phone number',
  `phone_number_normalized` varchar(50) DEFAULT NULL COMMENT 'Digits only, for indexing',
  `rating` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Driver rating 1-5 starts',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_order`
--

CREATE TABLE `food_order` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL COMMENT 'FK to refer to the customer',
  `food_place_id` int(11) NOT NULL COMMENT 'FK to refer to the food_place',
  `customer_address_id` int(11) NOT NULL COMMENT 'FK to refer to the customer address',
  `order_status_id` int(11) NOT NULL COMMENT 'FK to refer to the order status',
  `assigned_driver_id` int(11) DEFAULT NULL COMMENT 'FK to refer to the delivery driver',
  `order_datetime` datetime NOT NULL COMMENT 'Date and time when PLACING the order',
  `delivery_fee` decimal(10,2) NOT NULL COMMENT 'The fee for the delivery',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'The total amount for the order',
  `requested_delivery_time` datetime NOT NULL COMMENT 'The requested time to receive the order',
  `cust_driver_rating` tinyint(4) DEFAULT NULL,
  `cust_food_place_rating` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_place`
--

CREATE TABLE `food_place` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address_id` int(11) NOT NULL COMMENT 'FK to the address table',
  `average_rating` decimal(3,1) DEFAULT 0.0,
  `total_reviews` int(11) DEFAULT 0 COMMENT 'The total number of the reviews!',
  `food_type` varchar(50) NOT NULL COMMENT 'The kind of food, kebab, pizza, sushi',
  `description` text NOT NULL COMMENT 'The description of the food place',
  `opening_hours` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_place`
--

INSERT INTO `food_place` (`id`, `name`, `address_id`, `average_rating`, `total_reviews`, `food_type`, `description`, `opening_hours`, `user_id`) VALUES
(2, 'Roma Postman Express', 1, 0.0, 0, 'Italian Postman', 'Best pizza in Postman', '10:00 - 23:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menu_item`
--

CREATE TABLE `menu_item` (
  `id` int(11) NOT NULL,
  `food_place_id` int(11) NOT NULL COMMENT 'FK to refer to the food_place',
  `item_name` varchar(50) NOT NULL COMMENT 'the name of the item',
  `item_description` varchar(100) NOT NULL COMMENT 'the details of the item',
  `price` decimal(10,2) NOT NULL COMMENT 'the price of the food item'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_item`
--

INSERT INTO `menu_item` (`id`, `food_place_id`, `item_name`, `item_description`, `price`) VALUES
(5, 2, 'Bogus Item', 'This is a test item with bogus values.', 9.99),
(6, 2, 'Postman Pizza Margherita', 'Tomato Postman sauces', 8.50);

-- --------------------------------------------------------

--
-- Table structure for table `order_status`
--

CREATE TABLE `order_status` (
  `id` int(11) NOT NULL,
  `status_value` varchar(50) DEFAULT NULL COMMENT 'Pending, Shipped, Delivered and so on...'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status`
--

INSERT INTO `order_status` (`id`, `status_value`) VALUES
(1, 'Pending'),
(2, 'Processing'),
(3, 'Shipped'),
(4, 'Delivered'),
(5, 'Completed'),
(6, 'Cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','merchant','rider') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'testdriver@example.com', '$2y$10$uoGiqNolaQSnWOZTsc3QduF6I4bTnIsdWna.omIgf2Uahc.u.zcGq', 'customer', '2026-01-20 12:58:23'),
(2, 'testRick@example.com', '$2y$10$7TdHYRwojMYBYoND8v132./ufCLBQ7M6nR4/PdFAlxa6I/nifBaua', 'customer', '2026-01-20 13:01:03'),
(3, 'testLuca@example.com', '$2y$10$xcG0omCxfot1qzIyO6xtseCZqB8Uato2E2l7nKIhRRy/YeUxnDT2q', 'customer', '2026-01-20 13:01:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`);

--
-- Indexes for table `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_number_normalized` (`phone_number_normalized`);

--
-- Indexes for table `customer_address`
--
ALTER TABLE `customer_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `delivery_drivers`
--
ALTER TABLE `delivery_drivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `food_order`
--
ALTER TABLE `food_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `food_place_id` (`food_place_id`),
  ADD KEY `customer_address_id` (`customer_address_id`),
  ADD KEY `order_status_id` (`order_status_id`),
  ADD KEY `assigned_driver_id` (`assigned_driver_id`);

--
-- Indexes for table `food_place`
--
ALTER TABLE `food_place`
  ADD PRIMARY KEY (`id`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `menu_item`
--
ALTER TABLE `menu_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_place_id` (`food_place_id`);

--
-- Indexes for table `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `country`
--
ALTER TABLE `country`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_address`
--
ALTER TABLE `customer_address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_drivers`
--
ALTER TABLE `delivery_drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_order`
--
ALTER TABLE `food_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_place`
--
ALTER TABLE `food_place`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menu_item`
--
ALTER TABLE `menu_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_status`
--
ALTER TABLE `order_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`);

--
-- Constraints for table `customer_address`
--
ALTER TABLE `customer_address`
  ADD CONSTRAINT `customer_address_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `customer_address_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`);

--
-- Constraints for table `delivery_drivers`
--
ALTER TABLE `delivery_drivers`
  ADD CONSTRAINT `delivery_drivers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `food_order`
--
ALTER TABLE `food_order`
  ADD CONSTRAINT `food_order_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `food_order_ibfk_2` FOREIGN KEY (`food_place_id`) REFERENCES `food_place` (`id`),
  ADD CONSTRAINT `food_order_ibfk_3` FOREIGN KEY (`customer_address_id`) REFERENCES `customer_address` (`id`),
  ADD CONSTRAINT `food_order_ibfk_4` FOREIGN KEY (`order_status_id`) REFERENCES `order_status` (`id`),
  ADD CONSTRAINT `food_order_ibfk_5` FOREIGN KEY (`assigned_driver_id`) REFERENCES `delivery_drivers` (`id`);

--
-- Constraints for table `food_place`
--
ALTER TABLE `food_place`
  ADD CONSTRAINT `food_place_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`),
  ADD CONSTRAINT `food_place_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `menu_item`
--
ALTER TABLE `menu_item`
  ADD CONSTRAINT `menu_item_ibfk_1` FOREIGN KEY (`food_place_id`) REFERENCES `food_place` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
