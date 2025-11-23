<?php
/**
 * Order Controller
 * Creates an instance of the order class and handles order creation, order detail insertion, and simulated payment recording
 */

require_once __DIR__ . '/../Classes/order_class.php';

class order_controller
{
    private $order;

    public function __construct()
    {
        $this->order = new order_class();
    }

    /**
     * Create order
     * @param array $params Order data (customer_id, order_status)
     * @return array Result with status, message, and order_id
     */
    public function create_order_ctr($params)
    {
        return $this->order->createOrder($params);
    }

    /**
     * Add order details
     * @param array $params Order details data (order_id, product_id, qty)
     * @return array Result with status and message
     */
    public function add_order_details_ctr($params)
    {
        return $this->order->addOrderDetails($params);
    }

    /**
     * Record payment
     * @param array $params Payment data (customer_id, order_id, amt, currency)
     * @return array Result with status, message, and payment_id
     */
    public function record_payment_ctr($params)
    {
        return $this->order->recordPayment($params);
    }

    /**
     * Get past orders for a user
     * @param int $customer_id Customer ID
     * @return array Orders with details
     */
    public function get_past_orders_ctr($customer_id)
    {
        return $this->order->getPastOrders($customer_id);
    }

    /**
     * Get order details by order ID
     * @param int $order_id Order ID
     * @return array|null Order with full details or null if not found
     */
    public function get_order_details_ctr($order_id)
    {
        return $this->order->getOrderDetails($order_id);
    }
}
?>

