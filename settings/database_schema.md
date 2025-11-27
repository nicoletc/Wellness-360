# Wellness 360 Database Schema

## Tables and Columns

### 1. customers
- customer_id (PK)
- customer_name
- customer_email
- customer_pass
- customer_contact
- date_joined
- customer_image
- user_role (TINYINT: 1 = Admin, 2 = Customer)

### 2. carts
- p_id: INT, NOT NULL (FK to customer_products)
- ip_add: VARCHAR(50), NOT NULL
- c_id: INT, NULL (FK to customers)
- qty: INT, NOT NULL
- PRIMARY KEY (p_id, ip_add) - Composite primary key
- FOREIGN KEY (p_id) REFERENCES customer_products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
- FOREIGN KEY (c_id) REFERENCES customers(customer_id) ON DELETE SET NULL ON UPDATE CASCADE

### 3. customer_products
- product_id (PK)
- product_cat (FK to category)
- product_vendor (FK to vendors)
- product_title
- product_price
- product_desc
- product_image
- date_added
- product_keywords
- stock

### 4. vendors
- vendor_id (PK)
- vendor_name
- vendor_email
- vendor_contact
- product_stock

### 5. category (for product categories)
- cat_id (PK)
- cat_name

### 6. customer_orders
- order_id (PK)
- customer_id (FK to customers)
- invoice_no
- order_date
- order_status

### 7. order_details
- order_id (FK to customer_orders)
- product_id (FK to customer_products)
- qty

### 8. product_likes
- like_id: int(11), NOT NULL, AUTO_INCREMENT, PRIMARY KEY
- customer_id: int(11), NOT NULL (FK to customers.customer_id)
- product_id: int(11), NOT NULL (FK to customer_products.product_id)
- created_at: timestamp, NOT NULL, DEFAULT current_timestamp()

### 9. articles
- article_id (PK)
- article_title
- article_author
- article_cat
- article_body
- article_image (VARCHAR(255), NULL) - Path to article image (e.g., ../../uploads/u1/a1/image_1.jpg)
- date_added
- article_views

### 10. article_views
- view_id (PK)
- article_id (FK to articles)
- user_id (FK to customers, NULL for guest)
- ip_address
- viewed_at

### 11. payments
- pay_id (PK)
- amt
- customer_id (FK to customers)
- order_id (FK to customer_orders)
- currency
- payment_date
- payment_method (ENUM: 'paystack', 'bank_transfer', 'mobile_money', NULL) - Payment method selected by user
- transaction_ref (VARCHAR(100), NULL) - Transaction reference (e.g., Paystack ref: W360-5-1699876543)
- authorization_code (VARCHAR(100), NULL) - Authorization code from payment gateway (for Paystack)
- payment_channel (ENUM: 'card', 'mobile_money', 'bank', NULL) - Payment channel selected by user
CREATE INDEX idx_transaction_ref ON payments(transaction_ref);


### 12. community
- comm_id: int(11), NOT NULL, AUTO_INCREMENT, PRIMARY KEY
- customer_id: int(11), NOT NULL, INDEX (FK to customers.customer_id)
- comm_cat: varchar(255), NULL (free-form text category name, NOT a FK to product category table)
- comm_title: varchar(255), NOT NULL
- comm_desc: text, NOT NULL
- created_at: timestamp, NOT NULL, DEFAULT current_timestamp()

### 13. community_replies
- reply_id (PK)
- comm_id (FK to community)
- customer_id (FK to customers)
- reply_content
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

### 14. workshops
- workshop_id (PK)
- customer_id (FK to customers)
- workshop_title
- workshop_image
- workshop_desc
- workshop_leader
- workshop_date
- workshop_time
- workshop_type (in-person, virtual)
- location
- max_participants

### 15. workshop_registrations
- registration_id (PK)
- workshop_id (FK to workshops)
- customer_id (FK to customers)
- registered_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- status (ENUM: 'registered', 'cancelled', DEFAULT 'registered')
- UNIQUE KEY unique_registration (workshop_id, customer_id)

### 16. user_activity
- activity_id (PK)
- customer_id (INT, NULL, FK to customers) - User ID (NULL for guests)
- ip_address (VARCHAR(50), NOT NULL) - IP address for guest tracking
- activity_type (ENUM: 'article_view', 'product_view', 'page_view', 'time_spent', NOT NULL)
- content_type (ENUM: 'article', 'product', 'page', NOT NULL)
- content_id (INT, NOT NULL) - Article ID, Product ID, or page identifier
- category_id (INT, NULL) - Category ID for content
- time_spent_seconds (INT, DEFAULT 0) - Time spent in seconds
- viewed_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- INDEX idx_customer (customer_id)
- INDEX idx_content (content_type, content_id)
- INDEX idx_category (category_id)
- INDEX idx_viewed_at (viewed_at)
- FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL

### 17. user_interests
- interest_id (PK)
- customer_id (INT, NOT NULL, FK to customers)
- category_id (INT, NOT NULL, FK to category) - Category they are interested in
- interest_score (DECIMAL(5,2), DEFAULT 0.00) - Score based on views, time spent, etc.
- last_updated (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
- UNIQUE KEY unique_user_category (customer_id, category_id)
- INDEX idx_customer (customer_id)
- INDEX idx_score (interest_score)
- FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
- FOREIGN KEY (category_id) REFERENCES category(cat_id) ON DELETE CASCADE

### 18. daily_reminders
- reminder_id (PK)
- customer_id (INT, NOT NULL, FK to customers)
- reminder_type (ENUM: 'motivational_quote', 'product_reminder', 'article_reminder', 'wellness_tip', NOT NULL)
- category_id (INT, NULL, FK to category) - Related category
- content_id (INT, NULL) - Related article/product ID
- title (VARCHAR(255), NOT NULL)
- message (TEXT, NOT NULL)
- sent_date (DATE, NULL) - Date when reminder was sent
- is_read (TINYINT(1), DEFAULT 0) - Whether user has read the reminder
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- INDEX idx_customer (customer_id)
- INDEX idx_sent_date (sent_date)
- INDEX idx_is_read (is_read)
- INDEX idx_customer_date (customer_id, sent_date)
- FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
- FOREIGN KEY (category_id) REFERENCES category(cat_id) ON DELETE SET NULL

### 19. motivational_quotes
- quote_id (PK)
- category_id (INT, NULL, FK to category) - Category this quote relates to
- quote_text (TEXT, NOT NULL)
- author (VARCHAR(100), NULL)
- is_active (TINYINT(1), DEFAULT 1)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- INDEX idx_category (category_id)
- INDEX idx_active (is_active)
- FOREIGN KEY (category_id) REFERENCES category(cat_id) ON DELETE SET NULL

### 20. reminder_preferences
- preference_id (PK)
- customer_id (INT, NOT NULL, UNIQUE, FK to customers)
- reminder_frequency (ENUM: 'daily', 'weekly', 'never', NOT NULL, DEFAULT 'daily')
- preferred_categories (JSON, NULL) - Array of category IDs user wants reminders for (NULL = all categories)
- push_notifications_enabled (TINYINT(1), DEFAULT 0) - Whether user wants push notifications (deprecated)
- email_reminders_enabled (TINYINT(1), DEFAULT 0) - Whether user wants email reminders
- reminder_time (TIME, NULL) - Preferred time of day for reminders (e.g., 09:00:00)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
- FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE

### 21. push_subscriptions
- subscription_id (PK)
- customer_id (INT, NOT NULL, FK to customers)
- endpoint (TEXT, NOT NULL) - Push service endpoint URL
- p256dh_key (VARCHAR(255), NOT NULL) - User public key
- auth_key (VARCHAR(255), NOT NULL) - User auth secret
- user_agent (TEXT, NULL)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
- INDEX idx_customer (customer_id)
- FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
- Note: This table is deprecated (push notifications removed, but table structure kept for reference)

### 22. contact_messages
- message_id (PK)
- first_name (VARCHAR(100), NOT NULL)
- last_name (VARCHAR(100), NOT NULL)
- email (VARCHAR(255), NOT NULL)
- phone (VARCHAR(20), NULL)
- subject (VARCHAR(255), NOT NULL)
- message (TEXT, NOT NULL)
- customer_id (INT, NULL, FK to customers) - If user is logged in, link to customers table
- status (ENUM: 'new', 'read', 'replied', 'archived', DEFAULT 'new')
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
- INDEX idx_status (status)
- INDEX idx_created_at (created_at)
- INDEX idx_customer (customer_id)
- FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL

## Relationships
- carts.p_id → customer_products.product_id (ON DELETE CASCADE, ON UPDATE CASCADE)
- carts.c_id → customers.customer_id (ON DELETE SET NULL, ON UPDATE CASCADE)
- customer_products.product_cat → category.cat_id
- customer_products.product_vendor → vendors.vendor_id
- customer_orders.customer_id → customers.customer_id
- order_details.order_id → customer_orders.order_id
- order_details.product_id → customer_products.product_id
- product_likes.customer_id → customers.customer_id (ON DELETE CASCADE)
- product_likes.product_id → customer_products.product_id (ON DELETE CASCADE)
- article_views.article_id → articles.article_id
- article_views.user_id → customers.customer_id (nullable)
- payments.customer_id → customers.customer_id
- payments.order_id → customer_orders.order_id
- community.customer_id → customers.customer_id
- Note: community.comm_cat is free-form text, NOT a foreign key to any table
- community_replies.comm_id → community.comm_id (ON DELETE CASCADE)
- community_replies.customer_id → customers.customer_id (ON DELETE CASCADE)
- workshops.customer_id → customers.customer_id
- workshop_registrations.workshop_id → workshops.workshop_id (ON DELETE CASCADE)
- workshop_registrations.customer_id → customers(customer_id) ON DELETE CASCADE
- user_activity.customer_id → customers(customer_id) ON DELETE SET NULL
- user_interests.customer_id → customers(customer_id) ON DELETE CASCADE
- user_interests.category_id → category(cat_id) ON DELETE CASCADE
- daily_reminders.customer_id → customers(customer_id) ON DELETE CASCADE
- daily_reminders.category_id → category(cat_id) ON DELETE SET NULL
- motivational_quotes.category_id → category(cat_id) ON DELETE SET NULL
- reminder_preferences.customer_id → customers(customer_id) ON DELETE CASCADE
- push_subscriptions.customer_id → customers(customer_id) ON DELETE CASCADE
- contact_messages.customer_id → customers(customer_id) ON DELETE SET NULL

## SQL Migration Scripts

### User Activity Tracking Migration
```sql
-- User Activity Tracking Table
CREATE TABLE IF NOT EXISTS user_activity (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NULL COMMENT 'User ID (NULL for guests)',
    ip_address VARCHAR(50) NOT NULL COMMENT 'IP address for guest tracking',
    activity_type ENUM('article_view', 'product_view', 'page_view', 'time_spent') NOT NULL,
    content_type ENUM('article', 'product', 'page') NOT NULL,
    content_id INT NOT NULL COMMENT 'Article ID, Product ID, or page identifier',
    category_id INT NULL COMMENT 'Category ID for content',
    time_spent_seconds INT DEFAULT 0 COMMENT 'Time spent in seconds',
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_content (content_type, content_id),
    INDEX idx_category (category_id),
    INDEX idx_viewed_at (viewed_at),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Interests Table
CREATE TABLE IF NOT EXISTS user_interests (
    interest_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    category_id INT NOT NULL COMMENT 'Category they are interested in',
    interest_score DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Score based on views, time spent, etc.',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_category (customer_id, category_id),
    INDEX idx_customer (customer_id),
    INDEX idx_score (interest_score),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES category(cat_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Daily Reminders Table
CREATE TABLE IF NOT EXISTS daily_reminders (
    reminder_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    reminder_type ENUM('motivational_quote', 'product_reminder', 'article_reminder', 'wellness_tip') NOT NULL,
    category_id INT NULL COMMENT 'Related category',
    content_id INT NULL COMMENT 'Related article/product ID',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    sent_date DATE NULL COMMENT 'Date when reminder was sent',
    is_read TINYINT(1) DEFAULT 0 COMMENT 'Whether user has read the reminder',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_sent_date (sent_date),
    INDEX idx_is_read (is_read),
    INDEX idx_customer_date (customer_id, sent_date),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES category(cat_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Motivational Quotes Table
CREATE TABLE IF NOT EXISTS motivational_quotes (
    quote_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NULL COMMENT 'Category this quote relates to',
    quote_text TEXT NOT NULL,
    author VARCHAR(100) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category_id),
    INDEX idx_active (is_active),
    FOREIGN KEY (category_id) REFERENCES category(cat_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Reminder Preferences Migration
```sql
-- Reminder Preferences Table
CREATE TABLE IF NOT EXISTS reminder_preferences (
    preference_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL UNIQUE,
    reminder_frequency ENUM('daily', 'weekly', 'never') NOT NULL DEFAULT 'daily',
    preferred_categories JSON NULL COMMENT 'Array of category IDs user wants reminders for (NULL = all categories)',
    push_notifications_enabled TINYINT(1) DEFAULT 0 COMMENT 'Whether user wants push notifications (deprecated)',
    email_reminders_enabled TINYINT(1) DEFAULT 0 COMMENT 'Whether user wants email reminders',
    reminder_time TIME NULL COMMENT 'Preferred time of day for reminders (e.g., 09:00:00)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Push Notification Subscriptions Table (deprecated - kept for reference)
CREATE TABLE IF NOT EXISTS push_subscriptions (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    endpoint TEXT NOT NULL COMMENT 'Push service endpoint URL',
    p256dh_key VARCHAR(255) NOT NULL COMMENT 'User public key',
    auth_key VARCHAR(255) NOT NULL COMMENT 'User auth secret',
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Contact Messages Migration
```sql
-- Contact Messages Table
CREATE TABLE IF NOT EXISTS contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    customer_id INT NULL COMMENT 'If user is logged in, link to customers table',
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

