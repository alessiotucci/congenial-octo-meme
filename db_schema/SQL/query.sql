CREATE TABLE food_order(
 id INT AUTO_INCREMENT PRIMARY KEY,
 customer_id INT NOT NULL COMMENT 'FK to refer to the customer',
 FOREIGN KEY (customer_id) REFERENCES customers(id),
 food_place_id INT NOT NULL COMMENT 'FK to refer to the food_place',
 FOREIGN KEY (food_place_id) REFERENCES food_place(id),
 customer_address_id INT NOT NULL COMMENT 'FK to refer to the customer address',
 FOREIGN KEY (customer_address_id) REFERENCES customer_address(id),
 order_status_id INT NOT NULL COMMENT 'FK to refer to the order status',
 FOREIGN KEY (order_status_id) REFERENCES order_status(id),
 assigned_driver_id INT COMMENT 'FK to refer to the delivery driver',
 FOREIGN KEY(assigned_driver_id) REFERENCES delivery_drivers(id),
 order_datetime DATETIME NOT NULL COMMENT 'Date and time when PLACING the order',
 delivery_fee DECIMAL(10,2) NOT NULL COMMENT 'The fee for the delivery',
 total_amount DECIMAL(10,2) NOT NULL COMMENT 'The total amount for the order',
 requested_delivery_time DATETIME NOT NULL COMMENT 'The requested time to receive the order',
 cust_driver_rating TINYINT,
 cust_food_place_rating TINYINT
 );