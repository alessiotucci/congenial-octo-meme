-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Feb 01, 2026 alle 17:26
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

--
-- Dump dei dati per la tabella `address`
--

INSERT INTO `address` (`id`, `unit_number`, `street_number`, `address_line1`, `address_line2`, `city`, `region`, `postal_code`, `country_id`) VALUES
(1, 'Penthouse', '124', 'Conch Street (High Rise)', 'Top Floor', 'Bikini Bottom', 'Pacific Ocean', '90210-VIP', 1),
(3, '24-B', '101', 'Bogus street', 'Next to the Patrols', 'Hogman City', 'Nomad State', '00300', 1),
(4, 'Bucket-1', '124', 'Conch Street', 'Next to Patrick&#039;s Rock', 'Bikini Bottom', 'Pacific Ocean', '90210', 1),
(5, 'Lab-B', '12000', 'Candelaria Road NE', 'Albuquerque', 'New Mexico', 'South West', '87112', 1),
(7, 'Apt 4B', '20', 'Ingram Street', 'Forest Hills', 'Queens', 'NY', '11375', 1),
(8, 'Floor 30', '39', 'West 44th Street', 'Office 301', 'New York', 'NY', '10036', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `country`
--

CREATE TABLE `country` (
  `id` int(11) NOT NULL,
  `country_name` varchar(50) NOT NULL COMMENT 'just the name of the country'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `country`
--

INSERT INTO `country` (`id`, `country_name`) VALUES
(1, 'Italy');

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

--
-- Dump dei dati per la tabella `customer`
--

INSERT INTO `customer` (`id`, `first_name`, `last_name`, `nick_name`, `phone_number_original`, `phone_number_normalized`, `is_phone_verified`, `user_id`) VALUES
(1, 'Peter', 'Parker', 'Spider-Man UPDATE POSTMAN (Avenger)', '', '', 0, 2),
(2, '2Peter', '2Parker', '2Spidey', '555-0199', '', 0, 2),
(3, '2Peter', '2Parker', '2Spider-Man UPDATE POSTMAN (fail)', '', '', 0, 2);

-- --------------------------------------------------------

--
-- Struttura della tabella `customer_address`
--

CREATE TABLE `customer_address` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL COMMENT 'FK to refer to the customer',
  `address_id` int(11) NOT NULL COMMENT 'FK to refer to the address'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `customer_address`
--

INSERT INTO `customer_address` (`id`, `customer_id`, `address_id`) VALUES
(1, 1, 7),
(2, 1, 8);

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

--
-- Dump dei dati per la tabella `delivery_drivers`
--

INSERT INTO `delivery_drivers` (`id`, `first_name`, `last_name`, `phone_number_original`, `phone_number_normalized`, `is_phone_verified`, `rating`, `user_id`) VALUES
(1, 'Oussema', 'Fadhel', '+3935533668343', '', 0, 0, 1),
(3, 'Rick', 'Terun', '+3935533668343', '', 0, 4, 3),
(4, 'Mohammed', 'Abdallah', '+3935533668343', '', 0, 5, 3);

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

--
-- Dump dei dati per la tabella `food_order`
--

INSERT INTO `food_order` (`id`, `customer_id`, `food_place_id`, `customer_address_id`, `order_status_id`, `assigned_driver_id`, `order_datetime`, `delivery_fee`, `total_amount`, `requested_delivery_time`, `cust_driver_rating`, `cust_food_place_rating`) VALUES
(1, 1, 2, 1, 1, NULL, '2026-01-31 18:11:23', 3.50, 23.48, '2026-02-01 20:00:00', NULL, NULL),
(2, 1, 2, 1, 1, NULL, '2026-01-31 18:13:50', 0.00, 19.98, '2026-02-01 20:00:00', NULL, NULL),
(3, 1, 2, 1, 1, NULL, '2026-01-31 18:15:01', 0.00, 93.50, '2026-02-01 20:00:00', NULL, NULL);

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

--
-- Dump dei dati per la tabella `food_place`
--

INSERT INTO `food_place` (`id`, `name`, `address_id`, `average_rating`, `total_reviews`, `food_type`, `description`, `opening_hours`, `phone_number`, `is_phone_verified`, `user_id`) VALUES
(2, 'The Krusty Towers', 1, 0.0, 0, 'Luxury Hotel / Fine Dining', 'We shall never deny a guest, even the most ridiculous request.', '24/7', NULL, 0, NULL),
(3, 'The Krusty Krab', 4, 0.0, 0, 'Fast Food / Burgers', 'Home of the world-famous Krabby Patty. Secret formula included. Money back guarantee (not really).', 'Mon-Sun: 10:00 - 22:00', NULL, 0, 2),
(4, 'Los Pollos Hermanos', 5, 0.0, 0, 'Fried Chicken', 'The finest ingredients are brought together with love and care, then slow cooked to perfection. Nothing suspicious here.', 'Mon-Fri: 08:00 - 20:00', NULL, 0, 3);

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

--
-- Dump dei dati per la tabella `menu_item`
--

INSERT INTO `menu_item` (`id`, `food_place_id`, `item_name`, `item_description`, `price`, `is_available`, `is_deleted`, `category`) VALUES
(5, 2, 'Bogus Item', 'This is a test item with bogus values.', 9.99, 1, 0, 'Main'),
(6, 2, 'Postman Pizza Margherita', 'Tomato Postman sauces', 8.50, 1, 0, 'Main'),
(8, 2, 'Garlic Knots (6 pcs)', 'Dough tied in a knot, baked, and smothered in garlic butter, parsley and parmesan. Basically a heart', 4.50, 1, 1, 'Starters'),
(9, 2, 'The Wise Guy Pizza', 'Large 18-inch pie with pepperoni, sausage, meatballs, and extra mootz-arell. Fuhgeddaboudit.', 22.00, 1, 0, 'Pizza'),
(10, 2, 'Penne alla Vodka', 'Penne pasta tossed in our creamy tomato vodka sauce with prosciutto and chili flakes.', 16.50, 1, 0, 'Pasta'),
(11, 2, 'Mama&#039;s Meatball Sub', 'Homemade meatballs, marinara sauce, and melted provolone on toasted italian bread.', 12.00, 1, 0, 'Sandwiches'),
(12, 2, 'Holy Cannoli', 'Crispy pastry shell filled with sweet ricotta cream and chocolate chips.', 6.50, 0, 0, 'Dessert');

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

--
-- Dump dei dati per la tabella `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `quantity`, `price`) VALUES
(1, 1, 5, 2, 9.99),
(2, 2, 5, 2, 9.99),
(3, 3, 9, 2, 22.00),
(4, 3, 10, 3, 16.50);

-- --------------------------------------------------------

--
-- Struttura della tabella `order_status`
--

CREATE TABLE `order_status` (
  `id` int(11) NOT NULL,
  `status_value` varchar(50) DEFAULT NULL COMMENT 'Pending, Shipped, Delivered and so on...'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `order_status`
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
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','merchant','rider') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'testdriver@example.com', '$2y$10$uoGiqNolaQSnWOZTsc3QduF6I4bTnIsdWna.omIgf2Uahc.u.zcGq', 'customer', '2026-01-20 12:58:23'),
(2, 'testRick@example.com', '$2y$10$7TdHYRwojMYBYoND8v132./ufCLBQ7M6nR4/PdFAlxa6I/nifBaua', 'customer', '2026-01-20 13:01:03'),
(3, 'testLuca@example.com', '$2y$10$xcG0omCxfot1qzIyO6xtseCZqB8Uato2E2l7nKIhRRy/YeUxnDT2q', 'customer', '2026-01-20 13:01:53');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `country`
--
ALTER TABLE `country`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `customer_address`
--
ALTER TABLE `customer_address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `delivery_drivers`
--
ALTER TABLE `delivery_drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `food_order`
--
ALTER TABLE `food_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `food_place`
--
ALTER TABLE `food_place`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `menu_item`
--
ALTER TABLE `menu_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `order_status`
--
ALTER TABLE `order_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
