<?php
/**
 * User Class (Admin)
 * Extends database connection and contains user management methods for admin
 */

require_once __DIR__ . '/../settings/db_class.php';

class user_class extends db_connection
{
    /**
     * Get all users (excluding password)
     * @return array All users
     */
    public function get_all()
    {
        $sql = "SELECT customer_id, customer_name, customer_email, customer_contact, date_joined, customer_image, user_role 
                FROM customers 
                ORDER BY date_joined DESC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get a single user by ID
     * @param int $customer_id User ID
     * @return array|false User data or false if not found
     */
    public function get_one($customer_id)
    {
        $customer_id = (int)$customer_id;
        $sql = "SELECT customer_id, customer_name, customer_email, customer_contact, date_joined, customer_image, user_role 
                FROM customers 
                WHERE customer_id = $customer_id";
        return $this->db_fetch_one($sql);
    }

    /**
     * Delete a user
     * @param int $customer_id User ID
     * @return array Result with status and message
     */
    public function delete($customer_id)
    {
        $customer_id = (int)$customer_id;

        // Check if user exists
        $existing = $this->get_one($customer_id);
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'User not found.'
            ];
        }

        // Prevent deleting admin users (optional safety check)
        if ($existing['user_role'] == 1) {
            return [
                'status' => false,
                'message' => 'Cannot delete admin users.'
            ];
        }

        // Check if user has orders
        $check_orders = "SELECT COUNT(*) as count FROM customer_orders WHERE customer_id = $customer_id";
        $order_count = $this->db_fetch_one($check_orders);
        
        if ($order_count && $order_count['count'] > 0) {
            return [
                'status' => false,
                'message' => 'Cannot delete user. User has ' . $order_count['count'] . ' order(s). Please handle orders first.'
            ];
        }

        // Delete user's cart items first
        $delete_cart = "DELETE FROM carts WHERE c_id = $customer_id";
        $this->db_query($delete_cart);

        // Delete user
        $sql = "DELETE FROM customers WHERE customer_id = $customer_id";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'User deleted successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to delete user. Please try again.'
            ];
        }
    }
}

?>

