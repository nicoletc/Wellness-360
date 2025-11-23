<?php
/**
 * Order Class
 * Extends database connection and contains order and payment methods
 */

require_once __DIR__ . '/../settings/db_class.php';

class order_class extends db_connection
{
    /**
     * Generate a unique order reference/invoice number
     * @return string Unique invoice number
     */
    private function generateInvoiceNumber()
    {
        // Format: INV-YYYYMMDD-HHMMSS-RANDOM
        $date = date('Ymd');
        $time = date('His');
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        return "INV-{$date}-{$time}-{$random}";
    }
    
    /**
     * Create a new order in the orders table
     * @param array $data Order data (customer_id, order_status)
     * @return array Result with status, message, and order_id
     */
    public function createOrder($data)
    {
        if (empty($data['customer_id'])) {
            return [
                'status' => false,
                'message' => 'Customer ID is required.',
                'order_id' => null
            ];
        }
        
        $customer_id = (int)$data['customer_id'];
        $order_status = isset($data['order_status']) ? $this->escape_string($data['order_status']) : 'pending';
        $invoice_no = $this->generateInvoiceNumber();
        
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.',
                'order_id' => null
            ];
        }
        
        $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, order_status) 
                VALUES ($customer_id, '$invoice_no', NOW(), '$order_status')";
        
        if ($this->db_query($sql)) {
            $order_id = mysqli_insert_id($this->db);
            return [
                'status' => true,
                'message' => 'Order created successfully.',
                'order_id' => $order_id,
                'invoice_no' => $invoice_no
            ];
        } else {
            $error = mysqli_error($this->db);
            return [
                'status' => false,
                'message' => 'Failed to create order: ' . $error,
                'order_id' => null
            ];
        }
    }
    
    /**
     * Add order details (product ID, quantity) to the orderdetails table
     * @param array $data Order details data (order_id, product_id, qty, price)
     * @return array Result with status and message
     */
    public function addOrderDetails($data)
    {
        if (empty($data['order_id']) || empty($data['product_id']) || empty($data['qty'])) {
            return [
                'status' => false,
                'message' => 'Order ID, Product ID, and Quantity are required.'
            ];
        }
        
        $order_id = (int)$data['order_id'];
        $product_id = (int)$data['product_id'];
        $qty = (int)$data['qty'];
        
        if ($qty <= 0) {
            return [
                'status' => false,
                'message' => 'Quantity must be greater than 0.'
            ];
        }
        
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }
        
        $sql = "INSERT INTO orderdetails (order_id, product_id, qty) 
                VALUES ($order_id, $product_id, $qty)";
        
        if ($this->db_query($sql)) {
            return [
                'status' => true,
                'message' => 'Order detail added successfully.'
            ];
        } else {
            $error = mysqli_error($this->db);
            return [
                'status' => false,
                'message' => 'Failed to add order detail: ' . $error
            ];
        }
    }
    
    /**
     * Record payment entry in the payments table
     * @param array $data Payment data (customer_id, order_id, amt, currency)
     * @return array Result with status, message, and payment_id
     */
    public function recordPayment($data)
    {
        if (empty($data['customer_id']) || empty($data['order_id']) || empty($data['amt'])) {
            return [
                'status' => false,
                'message' => 'Customer ID, Order ID, and Amount are required.',
                'payment_id' => null
            ];
        }
        
        $customer_id = (int)$data['customer_id'];
        $order_id = (int)$data['order_id'];
        $amt = floatval($data['amt']);
        $currency = isset($data['currency']) ? $this->escape_string($data['currency']) : 'GHS';
        
        if ($amt <= 0) {
            return [
                'status' => false,
                'message' => 'Amount must be greater than 0.',
                'payment_id' => null
            ];
        }
        
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.',
                'payment_id' => null
            ];
        }
        
        $sql = "INSERT INTO payment (amt, customer_id, order_id, currency, payment_date) 
                VALUES ($amt, $customer_id, $order_id, '$currency', NOW())";
        
        if ($this->db_query($sql)) {
            $payment_id = mysqli_insert_id($this->db);
            return [
                'status' => true,
                'message' => 'Payment recorded successfully.',
                'payment_id' => $payment_id
            ];
        } else {
            $error = mysqli_error($this->db);
            return [
                'status' => false,
                'message' => 'Failed to record payment: ' . $error,
                'payment_id' => null
            ];
        }
    }
    
    /**
     * Get past orders for a user
     * @param int $customer_id Customer ID
     * @return array Orders with details
     */
    public function getPastOrders($customer_id)
    {
        $customer_id = (int)$customer_id;
        
        if (!$this->db_connect()) {
            return [];
        }
        
        $sql = "SELECT o.*, 
                       COUNT(od.product_id) as item_count,
                       SUM(p.product_price * od.qty) as total_amount
                FROM orders o
                LEFT JOIN orderdetails od ON o.order_id = od.order_id
                LEFT JOIN products p ON od.product_id = p.product_id
                WHERE o.customer_id = $customer_id
                GROUP BY o.order_id
                ORDER BY o.order_date DESC";
        
        $orders = $this->db_fetch_all($sql);
        
        if (!$orders || !is_array($orders)) {
            return [];
        }
        
        // Format orders
        $formatted = [];
        foreach ($orders as $order) {
            $formatted[] = [
                'order_id' => (int)$order['order_id'],
                'invoice_no' => $order['invoice_no'],
                'order_date' => $order['order_date'],
                'order_status' => $order['order_status'],
                'item_count' => (int)$order['item_count'],
                'total_amount' => floatval($order['total_amount'] ?? 0),
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get order details by order ID
     * @param int $order_id Order ID
     * @return array Order with full details
     */
    public function getOrderDetails($order_id)
    {
        $order_id = (int)$order_id;
        
        if (!$this->db_connect()) {
            return null;
        }
        
        // Get order info
        $order_sql = "SELECT * FROM orders WHERE order_id = $order_id";
        $order = $this->db_fetch_one($order_sql);
        
        if (!$order) {
            return null;
        }
        
        // Get order items
        $items_sql = "SELECT od.*, p.product_title, p.product_price, p.product_image
                     FROM orderdetails od
                     INNER JOIN products p ON od.product_id = p.product_id
                     WHERE od.order_id = $order_id";
        $items = $this->db_fetch_all($items_sql);
        
        // Get payment info
        $payment_sql = "SELECT * FROM payment WHERE order_id = $order_id";
        $payment = $this->db_fetch_one($payment_sql);
        
        return [
            'order' => $order,
            'items' => $items ?: [],
            'payment' => $payment ?: null
        ];
    }
}
?>

