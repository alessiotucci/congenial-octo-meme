-- ============================================================================
-- TABLE: order_items
-- ============================================================================
-- PURPOSE: Bridge table that links Orders to Menu Items
-- This is a junction table that stores the items purchased in each order
--
-- WHY THIS DESIGN?
-- - An order can have multiple items (e.g., 2 Pizzas + 1 Coke)
-- - A menu item can be ordered many times across different orders
-- - This creates a Many-to-Many relationship between food_order and menu_item
--
-- KEY CONCEPT: "Snapshot Price"
-- The price is stored HERE, not fetched from menu_item.price
-- Why? If a pizza costs $10 today but costs $12 next month, 
-- old orders should still show $10 (the actual price customer paid)
-- ============================================================================

CREATE TABLE order_items (
    -- PRIMARY KEY
    id INT(11) AUTO_INCREMENT PRIMARY KEY
        COMMENT 'Unique identifier for each line item in an order',
    
    -- FOREIGN KEYS (The Links)
    order_id INT(11) NOT NULL
        COMMENT 'FK: References food_order(id). Links this item to an order. CASCADE delete means if order is deleted, this item is too.',
    
    menu_item_id INT(11) NOT NULL
        COMMENT 'FK: References menu_item(id). Links to the product that was ordered (but see note about price below)',
    
    -- CORE DATA
    quantity INT(11) NOT NULL DEFAULT 1
        COMMENT 'How many of this item were ordered. E.g., 2 means customer ordered 2 Margarita pizzas',
    
    price DECIMAL(10, 2) NOT NULL
        COMMENT 'SNAPSHOT PRICE: The price customer paid for ONE item at time of order. Not a live price from menu_item table. If menu_item.price changes, this stays the same so order history is accurate.',
    
    -- CONSTRAINTS
    FOREIGN KEY (order_id) 
        REFERENCES food_order(id) 
        ON DELETE CASCADE
        COMMENT 'If an order is deleted, all its items are deleted too (cascade)',
    
    FOREIGN KEY (menu_item_id) 
        REFERENCES menu_item(id)
        -- NOTE: This has NO ON DELETE clause
        COMMENT 'Links to the original menu item. If menu item is deleted, this breaks. SOLUTION: Use soft delete (is_deleted flag) in menu_item table instead'

) 
COMMENT='Stores individual items within each order. Each row = 1 product type in 1 order. Links orders to menu items with a snapshot price.';


-- ============================================================================
-- EXAMPLE DATA
-- ============================================================================
-- Example 1: Order #5 has 2 items
-- INSERT INTO order_items VALUES
-- (NULL, 5, 12, 2, 8.50),    -- Line 1: Customer ordered 2 Margaritas at $8.50 each
-- (NULL, 5, 18, 1, 2.50);    -- Line 2: Customer ordered 1 Coke at $2.50

-- Example 2: Order #6 has 3 items
-- INSERT INTO order_items VALUES
-- (NULL, 6, 12, 1, 8.50),    -- 1 Margarita at $8.50
-- (NULL, 6, 15, 1, 9.99),    -- 1 Quattro Formaggi at $9.99
-- (NULL, 6, 20, 2, 4.99);    -- 2 Garlic Breads at $4.99 each


-- ============================================================================
-- QUERY EXAMPLES
-- ============================================================================

-- Example 1: Get all items for a specific order
-- SELECT 
--     oi.id,
--     oi.menu_item_id,
--     mi.name,
--     mi.category_id,
--     oi.quantity,
--     oi.price,
--     (oi.quantity * oi.price) AS line_total
-- FROM order_items oi
-- JOIN menu_item mi ON oi.menu_item_id = mi.id
-- WHERE oi.order_id = 5
-- ORDER BY oi.id;


-- Example 2: Calculate total price for an order
-- SELECT 
--     oi.order_id,
--     SUM(oi.quantity * oi.price) AS order_total
-- FROM order_items oi
-- WHERE oi.order_id = 5
-- GROUP BY oi.order_id;


-- Example 3: Get most popular items across all orders
-- SELECT 
--     mi.id,
--     mi.name,
--     COUNT(oi.id) AS times_ordered,
--     SUM(oi.quantity) AS total_quantity_sold,
--     AVG(oi.price) AS avg_price_paid
-- FROM order_items oi
-- JOIN menu_item mi ON oi.menu_item_id = mi.id
-- GROUP BY mi.id
-- ORDER BY times_ordered DESC;


-- ============================================================================
-- IMPORTANT DESIGN NOTES
-- ============================================================================

-- [1] WHY NO ON DELETE CASCADE FOR menu_item_id?
--     Because if a menu item is deleted, we lose the order history.
--     SOLUTION: Add is_deleted flag to menu_item table (soft delete pattern)
--
-- [2] WHY STORE PRICE IN THIS TABLE?
--     Menu items change price over time. If we only stored menu_item_id,
--     we'd have to fetch current price from menu_item table.
--     But then old orders would show WRONG prices.
--     By storing the snapshot, order history is always accurate.
--
-- [3] IS THERE A UNIQUE CONSTRAINT?
--     No. A customer can order the same item twice in different orders.
--     They could even order the same item multiple times in ONE order
--     (though that's handled by quantity, not multiple rows).
--
-- [4] SHOULD WE ADD created_at TIMESTAMP?
--     Optional but recommended: CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
--     Useful for: order fulfillment tracking, audit logs, analytics