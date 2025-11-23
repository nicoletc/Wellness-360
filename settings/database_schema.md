# Wellness 360 Database Schema

## Tables and Columns

### 1. customer
- customer_id (PK)
- customer_name
- customer_email
- customer_pass
- customer_contact
- date_joined
- customer_image
- user_role (TINYINT: 1 = Admin, 2 = Customer)

### 2. cart
- p_id: INT, NOT NULL (FK to products)
- ip_add: VARCHAR(50), NOT NULL
- c_id: INT, NULL (FK to customer)
- qty: INT, NOT NULL
- PRIMARY KEY (p_id, ip_add) - Composite primary key
- FOREIGN KEY (p_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
- FOREIGN KEY (c_id) REFERENCES customer(customer_id) ON DELETE SET NULL ON UPDATE CASCADE

### 3. products
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

### 6. orders
- order_id (PK)
- customer_id (FK to customer)
- invoice_no
- order_date
- order_status

### 7. orderdetails
- order_id (FK to orders)
- product_id (FK to products)
- qty

### 8. product_likes
- like_id: int(11), NOT NULL, AUTO_INCREMENT, PRIMARY KEY
- customer_id: int(11), NOT NULL (FK to customer.customer_id)
- product_id: int(11), NOT NULL (FK to products.product_id)
- created_at: timestamp, NOT NULL, DEFAULT current_timestamp()

### 9. articles
- article_id (PK)
- article_title
- article_author
- article_cat
- article_body
- date_added
- article_views

### 10. article_views
- view_id (PK)
- article_id (FK to articles)
- user_id (FK to customer, NULL for guest)
- ip_address
- viewed_at

### 11. payment
- pay_id (PK)
- amt
- customer_id (FK to customer)
- order_id (FK to orders)
- currency
- payment_date

### 12. community
- comm_id: int(11), NOT NULL, AUTO_INCREMENT, PRIMARY KEY
- customer_id: int(11), NOT NULL, INDEX (FK to customer.customer_id)
- comm_cat: varchar(255), NULL (free-form text category name, NOT a FK to product category table)
- comm_title: varchar(255), NOT NULL
- comm_desc: text, NOT NULL
- created_at: timestamp, NOT NULL, DEFAULT current_timestamp()

### 13. community_replies
- reply_id (PK)
- comm_id (FK to community)
- customer_id (FK to customer)
- reply_content
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

### 14. workshops
- workshop_id (PK)
- customer_id (FK to customer)
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
- customer_id (FK to customer)
- registered_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- status (ENUM: 'registered', 'cancelled', DEFAULT 'registered')
- UNIQUE KEY unique_registration (workshop_id, customer_id)

## Relationships
- cart.p_id → products.product_id (ON DELETE CASCADE, ON UPDATE CASCADE)
- cart.c_id → customer.customer_id (ON DELETE SET NULL, ON UPDATE CASCADE)
- products.product_cat → category.cat_id
- products.product_vendor → vendors.vendor_id
- orders.customer_id → customer.customer_id
- orderdetails.order_id → orders.order_id
- orderdetails.product_id → products.product_id
- product_likes.customer_id → customer.customer_id (ON DELETE CASCADE)
- product_likes.product_id → products.product_id (ON DELETE CASCADE)
- article_views.article_id → articles.article_id
- article_views.user_id → customer.customer_id (nullable)
- payment.customer_id → customer.customer_id
- payment.order_id → orders.order_id
- community.customer_id → customer.customer_id
- Note: community.comm_cat is free-form text, NOT a foreign key to any table
- community_replies.comm_id → community.comm_id (ON DELETE CASCADE)
- community_replies.customer_id → customer.customer_id (ON DELETE CASCADE)
- workshops.customer_id → customer.customer_id
- workshop_registrations.workshop_id → workshops.workshop_id (ON DELETE CASCADE)
- workshop_registrations.customer_id → customer.customer_id (ON DELETE CASCADE)

