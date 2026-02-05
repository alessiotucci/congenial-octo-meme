-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Feb 05, 2026 alle 17:59
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

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
-- Struttura della tabella `address`
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

-- --------------------------------------------------------

--
-- Struttura della tabella `country`
--

CREATE TABLE `country` (
  `id` int(11) NOT NULL,
  `country_name` varchar(50) NOT NULL COMMENT 'just the name of the country'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `customer`
--

CREATE TABLE `customer` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `nick_name` varchar(100) DEFAULT NULL,
  `phone_number_original` varchar(50) DEFAULT NULL COMMENT 'User-provider phone number',
  `phone_number_normalized` varchar(50) DEFAULT NULL COMMENT 'Digits only, for indexing',
  `is_phone_verified` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `customer_address`
--

CREATE TABLE `customer_address` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL COMMENT 'FK to refer to the customer',
  `address_id` int(11) NOT NULL COMMENT 'FK to refer to the address'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `delivery_drivers`
--

CREATE TABLE `delivery_drivers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone_number_original` varchar(50) DEFAULT NULL COMMENT 'Driver-provided phone number',
  `phone_number_normalized` varchar(50) DEFAULT NULL COMMENT 'Digits only, for indexing',
  `is_phone_verified` tinyint(1) NOT NULL DEFAULT 0,
  `rating` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Driver rating 1-5 starts',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `food_order`
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
-- Struttura della tabella `food_place`
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
  `phone_number` varchar(20) DEFAULT NULL,
  `is_phone_verified` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `menu_item`
--

CREATE TABLE `menu_item` (
  `id` int(11) NOT NULL,
  `food_place_id` int(11) NOT NULL COMMENT 'FK to refer to the food_place',
  `item_name` varchar(50) NOT NULL COMMENT 'the name of the item',
  `item_description` varchar(100) NOT NULL COMMENT 'the details of the item',
  `price` decimal(10,2) NOT NULL COMMENT 'the price of the food item',
  `is_available` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Default: Available',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Default: not delete',
  `category` varchar(100) NOT NULL DEFAULT 'Main' COMMENT 'Default: main dish'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `order_status`
--

CREATE TABLE `order_status` (
  `id` int(11) NOT NULL,
  `status_value` varchar(50) DEFAULT NULL COMMENT 'Pending, Shipped, Delivered and so on...'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','rider','food_place') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`);

--
-- Indici per le tabelle `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_number_normalized` (`phone_number_normalized`);

--
-- Indici per le tabelle `customer_address`
--
ALTER TABLE `customer_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indici per le tabelle `delivery_drivers`
--
ALTER TABLE `delivery_drivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `food_order`
--
ALTER TABLE `food_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `food_place_id` (`food_place_id`),
  ADD KEY `customer_address_id` (`customer_address_id`),
  ADD KEY `order_status_id` (`order_status_id`),
  ADD KEY `assigned_driver_id` (`assigned_driver_id`);

--
-- Indici per le tabelle `food_place`
--
ALTER TABLE `food_place`
  ADD PRIMARY KEY (`id`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `menu_item`
--
ALTER TABLE `menu_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_place_id` (`food_place_id`);

--
-- Indici per le tabelle `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indici per le tabelle `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `address`
--
ALTER TABLE `address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `country`
--
ALTER TABLE `country`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `customer_address`
--
ALTER TABLE `customer_address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `delivery_drivers`
--
ALTER TABLE `delivery_drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `food_order`
--
ALTER TABLE `food_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `food_place`
--
ALTER TABLE `food_place`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `menu_item`
--
ALTER TABLE `menu_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `order_status`
--
ALTER TABLE `order_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`);

--
-- Limiti per la tabella `customer_address`
--
ALTER TABLE `customer_address`
  ADD CONSTRAINT `customer_address_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `customer_address_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`);

--
-- Limiti per la tabella `delivery_drivers`
--
ALTER TABLE `delivery_drivers`
  ADD CONSTRAINT `delivery_drivers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limiti per la tabella `food_order`
--
ALTER TABLE `food_order`
  ADD CONSTRAINT `food_order_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `food_order_ibfk_2` FOREIGN KEY (`food_place_id`) REFERENCES `food_place` (`id`),
  ADD CONSTRAINT `food_order_ibfk_3` FOREIGN KEY (`customer_address_id`) REFERENCES `customer_address` (`id`),
  ADD CONSTRAINT `food_order_ibfk_4` FOREIGN KEY (`order_status_id`) REFERENCES `order_status` (`id`),
  ADD CONSTRAINT `food_order_ibfk_5` FOREIGN KEY (`assigned_driver_id`) REFERENCES `delivery_drivers` (`id`);

--
-- Limiti per la tabella `food_place`
--
ALTER TABLE `food_place`
  ADD CONSTRAINT `food_place_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`),
  ADD CONSTRAINT `food_place_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limiti per la tabella `menu_item`
--
ALTER TABLE `menu_item`
  ADD CONSTRAINT `menu_item_ibfk_1` FOREIGN KEY (`food_place_id`) REFERENCES `food_place` (`id`);

--
-- Limiti per la tabella `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `food_order` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_item` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
