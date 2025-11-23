<?php
/**
 * Customer Class
 * Extends database connection and contains customer methods
 */

require_once __DIR__ . '/../settings/db_class.php';

class customer_class extends db_connection
{
    /**
     * Add a new customer to the database
     * @param array $data Customer data (customer_name, customer_email, customer_pass, customer_contact, user_role)
     * @return array Result with status and message
     */
    public function add($data)
    {
        // Validate required fields
        $required = ['customer_name', 'customer_email', 'customer_pass', 'customer_contact', 'user_role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'status' => false,
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                ];
            }
        }

        // Check if email already exists
        $email_check = $this->check_email($data['customer_email']);
        if ($email_check['status'] === true) {
            return [
                'status' => false,
                'message' => 'Email already exists. Please use a different email.'
            ];
        }

        // Hash the password
        $hashed_password = password_hash($data['customer_pass'], PASSWORD_DEFAULT);

        // Escape input data
        $name = $this->escape_string($data['customer_name']);
        $email = $this->escape_string($data['customer_email']);
        $contact = $this->escape_string($data['customer_contact']);
        $user_role = (int)$data['user_role'];

        // Prepare SQL query
        $sql = "INSERT INTO customer (customer_name, customer_email, customer_pass, customer_contact, user_role, date_joined) 
                VALUES ('$name', '$email', '$hashed_password', '$contact', $user_role, NOW())";

        // Execute query
        if ($this->db_write_query($sql)) {
            return [
                'status' => true,
                'message' => 'Registration successful!',
                'customer_id' => $this->last_insert_id()
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }

    /**
     * Check if email already exists
     * @param string $email Email to check
     * @return array Result with status
     */
    public function check_email($email)
    {
        $email = $this->escape_string($email);
        $sql = "SELECT customer_id FROM customer WHERE customer_email = '$email'";
        
        $result = $this->db_fetch_one($sql);
        
        if ($result) {
            return [
                'status' => true,
                'message' => 'Email already exists'
            ];
        }
        
        return [
            'status' => false,
            'message' => 'Email is available'
        ];
    }

    /**
     * Get customer by email
     * @param string $email Customer email
     * @return array|false Customer data or false
     */
    public function get_customer_by_email($email)
    {
        $email = $this->escape_string($email);
        $sql = "SELECT * FROM customer WHERE customer_email = '$email'";
        
        return $this->db_fetch_one($sql);
    }

    /**
     * Verify customer login credentials
     * @param string $email Customer email
     * @param string $password Plain text password
     * @return array Result with status and customer data
     */
    public function verify_login($email, $password)
    {
        // Get customer by email
        $customer = $this->get_customer_by_email($email);
        
        if (!$customer) {
            return [
                'status' => false,
                'message' => 'Invalid email or password.'
            ];
        }

        // Verify password
        if (!password_verify($password, $customer['customer_pass'])) {
            return [
                'status' => false,
                'message' => 'Invalid email or password.'
            ];
        }

        // Return customer data (without password)
        unset($customer['customer_pass']);
        return [
            'status' => true,
            'message' => 'Login successful.',
            'customer' => $customer
        ];
    }

    /**
     * Get customer by ID
     * @param int $customer_id Customer ID
     * @return array|false Customer data or false
     */
    public function get_customer_by_id($customer_id)
    {
        $customer_id = (int)$customer_id;
        $sql = "SELECT customer_id, customer_name, customer_email, customer_contact, customer_image, user_role, date_joined 
                FROM customer WHERE customer_id = $customer_id";
        
        return $this->db_fetch_one($sql);
    }

    /**
     * Update customer information
     * @param int $customer_id Customer ID
     * @param array $data Customer data to update
     * @return array Result with status and message
     */
    public function update($customer_id, $data)
    {
        $customer_id = (int)$customer_id;
        $updates = [];

        if (isset($data['customer_name'])) {
            $updates[] = "customer_name = '" . $this->escape_string($data['customer_name']) . "'";
        }
        if (isset($data['customer_email'])) {
            $updates[] = "customer_email = '" . $this->escape_string($data['customer_email']) . "'";
        }
        if (isset($data['customer_contact'])) {
            $updates[] = "customer_contact = '" . $this->escape_string($data['customer_contact']) . "'";
        }
        if (isset($data['customer_image'])) {
            $updates[] = "customer_image = '" . $this->escape_string($data['customer_image']) . "'";
        }
        if (isset($data['user_role'])) {
            $updates[] = "user_role = " . (int)$data['user_role'];
        }

        if (empty($updates)) {
            return [
                'status' => false,
                'message' => 'No data to update.'
            ];
        }

        $sql = "UPDATE customer SET " . implode(', ', $updates) . " WHERE customer_id = $customer_id";

        if ($this->db_write_query($sql)) {
            return [
                'status' => true,
                'message' => 'Customer updated successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Update failed. Please try again.'
            ];
        }
    }

    /**
     * Delete customer
     * @param int $customer_id Customer ID
     * @return array Result with status and message
     */
    public function delete($customer_id)
    {
        $customer_id = (int)$customer_id;
        $sql = "DELETE FROM customer WHERE customer_id = $customer_id";

        if ($this->db_write_query($sql)) {
            return [
                'status' => true,
                'message' => 'Customer deleted successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Delete failed. Please try again.'
            ];
        }
    }
}

?>

