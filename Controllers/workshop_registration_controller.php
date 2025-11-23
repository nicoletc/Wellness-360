<?php
/**
 * Workshop Registration Controller
 * Creates an instance of the workshop registration class and runs the methods
 */

require_once __DIR__ . '/../Classes/workshop_registration_class.php';

class workshop_registration_controller
{
    private $registration;

    public function __construct()
    {
        $this->registration = new workshop_registration_class();
    }

    /**
     * Register a customer for a workshop
     * @param int $workshop_id Workshop ID
     * @param int $customer_id Customer ID
     * @return array Result with status and message
     */
    public function register_ctr($workshop_id, $customer_id)
    {
        return $this->registration->register($workshop_id, $customer_id);
    }

    /**
     * Cancel a workshop registration
     * @param int $workshop_id Workshop ID
     * @param int $customer_id Customer ID
     * @return array Result with status and message
     */
    public function cancel_ctr($workshop_id, $customer_id)
    {
        return $this->registration->cancel($workshop_id, $customer_id);
    }

    /**
     * Get registration count for a workshop
     * @param int $workshop_id Workshop ID
     * @return int Registration count
     */
    public function get_registration_count_ctr($workshop_id)
    {
        return $this->registration->get_registration_count($workshop_id);
    }

    /**
     * Check if customer is registered for a workshop
     * @param int $workshop_id Workshop ID
     * @param int $customer_id Customer ID
     * @return bool True if registered, false otherwise
     */
    public function is_registered_ctr($workshop_id, $customer_id)
    {
        return $this->registration->is_registered($workshop_id, $customer_id);
    }
}
?>

