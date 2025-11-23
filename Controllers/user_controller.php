<?php
/**
 * User Controller (Admin)
 * Creates an instance of the user class and runs the methods
 */

require_once __DIR__ . '/../Classes/user_class.php';

class user_controller
{
    private $user;

    public function __construct()
    {
        $this->user = new user_class();
    }

    /**
     * Get all users
     * @return array All users
     */
    public function get_all_users_ctr()
    {
        return $this->user->get_all();
    }

    /**
     * Get a single user by ID
     * @param int $customer_id User ID
     * @return array|false User data or false if not found
     */
    public function get_user_ctr($customer_id)
    {
        return $this->user->get_one($customer_id);
    }

    /**
     * Delete a user
     * @param int $customer_id User ID
     * @return array Result with status and message
     */
    public function delete_user_ctr($customer_id)
    {
        return $this->user->delete($customer_id);
    }
}

?>

