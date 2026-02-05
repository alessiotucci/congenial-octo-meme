<?php
/* ************************************************************************** */
/*     File: src/config/db_params.php                                         */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/05 16:46:16                                           */
/*     Updated: 2026/02/05 16:46:26                                           */
/*     System: Linux [atucci-Surface-Laptop-3]                                */
/*     Hardware: Intel Core i5-1035G7 | RAM: 7GB                              */
/* ************************************************************************** */

// src/config/db_params.php

// 1. Host: Use Docker Environment variable, fallback to 'db'
$host = getenv('MYSQL_HOST') ?: 'db'; 

// 2. Database Name
$db_name = getenv('MYSQL_DATABASE') ?: 'food_delivery';

// 3. User
$username = getenv('MYSQL_USER') ?: 'food_app_user';

// 4. Password
$password = getenv('MYSQL_PASSWORD') ?: 'secure_user_pass';

// 5. Charset (Optional but recommended)
$charset = 'utf8mb4';
?>
