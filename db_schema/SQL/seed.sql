-- =========================================================
-- FOOD DELIVERY – GPT GENERATED SEED DATA
-- =========================================================

START TRANSACTION;

--***********************
-- COUNTRY
--***********************
-- GPT SEED DATA (LLM GENERATED)
INSERT INTO country (id, country_name) VALUES
(2, 'United States'),
(3, 'Japan');

--***********************
-- ADDRESS
--***********************
-- GPT SEED DATA (LLM GENERATED)
INSERT INTO address (
  id, unit_number, street_number, address_line1, address_line2,
  city, region, postal_code, country_id
) VALUES
(9, 'Suite 12', '500', 'Market Street', NULL, 'San Francisco', 'CA', '94105', 2),
(10, 'Apt 7A', '1-2-3', 'Shibuya Crossing', 'Near Station', 'Tokyo', 'Kanto', '150-0002', 3);

--***********************
-- USERS
--***********************
-- GPT SEED DATA (LLM GENERATED)
INSERT INTO users (id, email, password, role, created_at) VALUES
(5, 'merchant@example.com', '$2y$10$examplehashmerchant', 'merchant', NOW()),
(6, 'rider@example.com', '$2y$10$examplehashrider', 'rider', NOW());

--***********************
-- CUSTOMER
--***********************
-- GPT SEED DATA (LLM GENERATED)
INSERT INTO customer (
  id, first_name, last_name, nick_name,
  phone_number_original, phone_number_normalized,
  is_phone_verified, user_id
) VALUES
(4, 'Jane', 'Doe', 'JD', '+1 (555) 101-2020', '15551012020', 1, 5);

--***********************
-- CUSTOMER_ADDRESS
--***********************
-- GPT SEED DATA (LLM GENERATED)
INSERT INTO customer_address (id, customer_id, address_id) VALUES
(3, 4, 9);

--***********************
-- DELIVERY_DRIVERS
--***********************
-- GPT SEED DATA (LLM GENERATED)
INSERT INTO delivery_drivers (
  id, first_name, last_name,
  phone_number_original, phone_number_normalized,
  is_phone_verified, rating, user_id
) VALUES
(5, 'Alex', 'Rider', '+1 555 303 4040', '15553034040', 1, 5, 6);

--***********************
-- FOOD_PLACE
--***********************
-- GPT SEED DATA (LLM GENERATED)
INSERT INTO food_place (
  id, name, address_id, average_rating, total_reviews,
  food_type, description, opening_hours,
  phone_number, is_phone_verified, user_id
) VALUES
(5, 'Tokyo Ramen Lab', 10, 4.6, 128,
 'Ramen',
 'Authentic tonkotsu ramen crafted by obsessive perfectionists.',
 'Mon-Sun: 11:00 - 23:00',
 '+81 3-1234-5678', 1, 5);

--***********************
-- MENU_ITEM
--***********************
-- GPT SEED DATA (LLM GENERATED)
INSERT INTO menu_item (
  id, food_place_id, item_name, item_description,
  price, is_available, is_deleted, category
) VALUES
(13, 5, 'Tonkotsu Ramen',
 'Rich pork broth with chashu, egg, and scallions.',
 14.00, 1, 0, 'Main'),
(14, 5, 'Gyoza (6 pcs)',
 'Pan-fried pork dumplings with dipping sauce.',
 6.00, 1, 0, 'Starters');

--***********************
-- FOOD_ORDER
--***********************
-- GPT SEED DATA (LLM GENERATED)
INSERT INTO food_order (
  id, customer_id, food_place_id, customer_address_id,
  order_status_id, assigned_driver_id,
  order_datetime, delivery_fee, total_amount,
  requested_delivery_time, cust_driver_rating, cust_food_place_rating
) VALUES
(4, 4, 5, 3, 2, 5,
 NOW(), 4.50, 24.50,
 DATE_ADD(NOW(), INTERVAL 45 MINUTE),
 NULL, NULL);

--***********************
-- ORDER_ITEMS
--***********************
-- GPT SEED DATA (LLM GENERATED)
INSERT INTO order_items (
  id, order_id, menu_item_id, quantity, price
) VALUES
(5, 4, 13, 1, 14.00),
(6, 4, 14, 1, 6.00);

COMMIT;
