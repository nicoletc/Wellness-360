-- User Activity Tracking - Database Migration
-- Tracks user behavior for personalized recommendations
-- Run this SQL script in your database to add the required tables

-- User Activity Tracking Table
-- Tracks time spent on pages, page views, and user interactions
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
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Interests Table
-- Aggregates user interests based on activity
CREATE TABLE IF NOT EXISTS user_interests (
    interest_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    category_id INT NOT NULL COMMENT 'Category they're interested in',
    interest_score DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Score based on views, time spent, etc.',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_category (customer_id, category_id),
    INDEX idx_customer (customer_id),
    INDEX idx_score (interest_score),
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES category(cat_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Daily Reminders Table
-- Stores daily reminders and motivational quotes for users
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
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES category(cat_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Motivational Quotes Table
-- Stores motivational quotes by category
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

