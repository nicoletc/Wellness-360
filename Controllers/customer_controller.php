<?php
/**
 * Customer Controller
 * Creates instance of customer class and runs methods
 */

require_once __DIR__ . '/../Classes/customer_class.php';

class customer_controller
{
    private $customer;

    public function __construct()
    {
        $this->customer = new customer_class();
    }

    /**
     * Register a new customer
     * @param array $kwargs Customer registration data
     * @return array Result with status and message
     */
    public function register_customer_ctr($kwargs)
    {
        // Validate input data
        $validation = $this->validate_registration_data($kwargs);
        if (!$validation['valid']) {
            return [
                'status' => false,
                'message' => $validation['message']
            ];
        }

        // Call the customer class add method
        return $this->customer->add($kwargs);
    }

    /**
     * Validate registration data
     * @param array $data Registration data
     * @return array Validation result
     */
    private function validate_registration_data($data)
    {
        // Check required fields
        if (empty($data['customer_name'])) {
            return ['valid' => false, 'message' => 'Full name is required.'];
        }
        if (strlen($data['customer_name']) > 100) {
            return ['valid' => false, 'message' => 'Full name must be less than 100 characters.'];
        }

        if (empty($data['customer_email'])) {
            return ['valid' => false, 'message' => 'Email is required.'];
        }
        if (!filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format.'];
        }
        if (strlen($data['customer_email']) > 100) {
            return ['valid' => false, 'message' => 'Email must be less than 100 characters.'];
        }

        if (empty($data['customer_pass'])) {
            return ['valid' => false, 'message' => 'Password is required.'];
        }
        if (strlen($data['customer_pass']) < 8) {
            return ['valid' => false, 'message' => 'Password must be at least 8 characters long.'];
        }

        if (empty($data['customer_contact'])) {
            return ['valid' => false, 'message' => 'Contact number is required.'];
        }
        if (strlen($data['customer_contact']) > 20) {
            return ['valid' => false, 'message' => 'Contact number must be less than 20 characters.'];
        }

        // Set default user role if not provided
        if (empty($data['user_role'])) {
            $data['user_role'] = 2; // Default to customer
        }

        return ['valid' => true];
    }

    /**
     * Check if email is available
     * @param string $email Email to check
     * @return array Result with status
     */
    public function check_email_availability_ctr($email)
    {
        return $this->customer->check_email($email);
    }

    /**
     * Get customer by email
     * @param string $email Customer email
     * @return array|false Customer data or false
     */
    public function get_customer_by_email_ctr($email)
    {
        return $this->customer->get_customer_by_email($email);
    }

    /**
     * Get customer by ID
     * @param int $customer_id Customer ID
     * @return array|false Customer data or false
     */
    public function get_customer_by_id_ctr($customer_id)
    {
        return $this->customer->get_customer_by_id($customer_id);
    }

    /**
     * Login customer
     * @param array $kwargs Login data (customer_email, customer_pass)
     * @return array Result with status, message, and customer data
     */
    public function login_customer_ctr($kwargs)
    {
        // Validate input data
        if (empty($kwargs['customer_email'])) {
            return [
                'status' => false,
                'message' => 'Email is required.'
            ];
        }

        if (empty($kwargs['customer_pass'])) {
            return [
                'status' => false,
                'message' => 'Password is required.'
            ];
        }

        // Validate email format
        if (!filter_var($kwargs['customer_email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => false,
                'message' => 'Invalid email format.'
            ];
        }

        // Verify login credentials
        return $this->customer->verify_login($kwargs['customer_email'], $kwargs['customer_pass']);
    }
}

?>

