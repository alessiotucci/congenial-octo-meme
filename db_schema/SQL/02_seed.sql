-- =========================================================
-- FOOD DELIVERY – SEED DATA (FINAL FIX)
-- =========================================================

START TRANSACTION;

-- 1. Order Status (MUST BE FIRST because Orders depend on it!)
INSERT INTO order_status (id, status_value) VALUES
(1, 'Pending'),
(2, 'Processing'),
(3, 'Shipped'),
(4, 'Delivered'),
(5, 'Completed'),
(6, 'Cancelled');

-- 2. Country
INSERT INTO country (id, country_name) VALUES
(1, 'Italy'),
(2, 'United States'),
(3, 'Japan');

-- 3. Address
INSERT INTO address (id, unit_number, street_number, address_line1, address_line2, city, region, postal_code, country_id) VALUES
(9, 'Suite 12', '500', 'Market Street', NULL, 'San Francisco', 'CA', '94105', 2),
(10, 'Apt 7A', '1-2-3', 'Shibuya Crossing', 'Near Station', 'Tokyo', 'Kanto', '150-0002', 3);

-- 4. Users (Password is 'password123' hashed)
INSERT INTO users (id, email, password, role, created_at) VALUES
(5, 'merchant@example.com', '$2y$10$7TdHYRwojMYBYoND8v132./ufCLBQ7M6nR4/PdFAlxa6I/nifBaua', 'food_place', NOW()),
(6, 'rider@example.com', '$2y$10$7TdHYRwojMYBYoND8v132./ufCLBQ7M6nR4/PdFAlxa6I/nifBaua', 'rider', NOW());

-- 5. Customer
INSERT INTO customer (id, first_name, last_name, nick_name, phone_number_original, phone_number_normalized, is_phone_verified, user_id) VALUES
(4, 'Jane', 'Doe', 'JD', '+15551012020', '15551012020', 1, 5);

-- 6. Customer Address Link
INSERT INTO customer_address (id, customer_id, address_id) VALUES
(3, 4, 9);

-- 7. Delivery Drivers
INSERT INTO delivery_drivers (id, first_name, last_name, phone_number_original, phone_number_normalized, is_phone_verified, rating, user_id) VALUES
(5, 'Alex', 'Rider', '+15553034040', '15553034040', 1, 5, 6);

-- 8. Food Place
INSERT INTO food_place (id, name, address_id, average_rating, total_reviews, food_type, description, opening_hours, phone_number, is_phone_verified, user_id) VALUES
(5, 'Tokyo Ramen Lab', 10, 4.6, 128, 'Ramen', 'Authentic tonkotsu ramen crafted by obsessive perfectionists.', 'Mon-Sun: 11-23', '+81312345678', 1, 5);

-- 9. Menu Items
INSERT INTO menu_item (id, food_place_id, item_name, item_description, price, is_available, is_deleted, category) VALUES
(13, 5, 'Tonkotsu Ramen', 'Rich pork broth with chashu.', 14.00, 1, 0, 'Main'),
(14, 5, 'Gyoza (6 pcs)', 'Pan-fried pork dumplings.', 6.00, 1, 0, 'Starters');

-- 10. Food Order (NOW SAFE because order_status_id=2 exists)
INSERT INTO food_order (id, customer_id, food_place_id, customer_address_id, order_status_id, assigned_driver_id, order_datetime, delivery_fee, total_amount, requested_delivery_time) VALUES
(4, 4, 5, 3, 2, 5, NOW(), 4.50, 24.50, DATE_ADD(NOW(), INTERVAL 45 MINUTE));

-- 11. Order Items
INSERT INTO order_items (id, order_id, menu_item_id, quantity, price) VALUES
(5, 4, 13, 1, 14.00),
(6, 4, 14, 1, 6.00);

COMMIT;
