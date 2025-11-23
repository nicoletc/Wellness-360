<?php
/**
 * Workshop Registration Class
 * Handles workshop registration operations
 */

require_once __DIR__ . '/../settings/db_class.php';

class workshop_registration_class extends db_connection
{
    /**
     * Register a customer for a workshop
     * @param int $workshop_id Workshop ID
     * @param int $customer_id Customer ID
     * @return array Result with status and message
     */
    public function register($workshop_id, $customer_id)
    {
        $workshop_id = (int)$workshop_id;
        $customer_id = (int)$customer_id;

        if ($workshop_id <= 0 || $customer_id <= 0) {
            return [
                'status' => false,
                'message' => 'Invalid workshop or customer ID.'
            ];
        }

        // Ensure database connection
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }

        // Check if workshop exists
        $workshopSql = "SELECT workshop_id, max_participants FROM workshops WHERE workshop_id = $workshop_id";
        $workshop = $this->db_fetch_one($workshopSql);
        
        if (!$workshop) {
            return [
                'status' => false,
                'message' => 'Workshop not found.'
            ];
        }

        // Check if user is already registered
        $checkSql = "SELECT registration_id FROM workshop_registrations 
                     WHERE workshop_id = $workshop_id AND customer_id = $customer_id";
        $existing = $this->db_fetch_one($checkSql);
        
        if ($existing) {
            // Check if already registered (not cancelled)
            $statusSql = "SELECT status FROM workshop_registrations 
                          WHERE workshop_id = $workshop_id AND customer_id = $customer_id";
            $statusResult = $this->db_fetch_one($statusSql);
            
            if ($statusResult && $statusResult['status'] === 'registered') {
                return [
                    'status' => false,
                    'message' => 'You are already registered for this workshop.'
                ];
            } else {
                // User previously cancelled, update status to registered
                $updateSql = "UPDATE workshop_registrations 
                             SET status = 'registered', registered_at = NOW() 
                             WHERE workshop_id = $workshop_id AND customer_id = $customer_id";
                $result = $this->db_query($updateSql);
                
                if ($result) {
                    return [
                        'status' => true,
                        'message' => 'Registration restored successfully.'
                    ];
                } else {
                    return [
                        'status' => false,
                        'message' => 'Failed to restore registration.'
                    ];
                }
            }
        }

        // Check if workshop is full
        $countSql = "SELECT COUNT(registration_id) as registered_count 
                     FROM workshop_registrations 
                     WHERE workshop_id = $workshop_id AND status = 'registered'";
        $countResult = $this->db_fetch_one($countSql);
        $registeredCount = (int)($countResult['registered_count'] ?? 0);
        $maxParticipants = (int)$workshop['max_participants'];
        
        if ($registeredCount >= $maxParticipants) {
            return [
                'status' => false,
                'message' => 'Workshop is full. No more registrations available.'
            ];
        }

        // Insert registration
        $sql = "INSERT INTO workshop_registrations (workshop_id, customer_id, status, registered_at) 
                VALUES ($workshop_id, $customer_id, 'registered', NOW())";
        
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Successfully registered for the workshop!'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to register. Please try again.'
            ];
        }
    }

    /**
     * Cancel a workshop registration
     * @param int $workshop_id Workshop ID
     * @param int $customer_id Customer ID
     * @return array Result with status and message
     */
    public function cancel($workshop_id, $customer_id)
    {
        $workshop_id = (int)$workshop_id;
        $customer_id = (int)$customer_id;

        if ($workshop_id <= 0 || $customer_id <= 0) {
            return [
                'status' => false,
                'message' => 'Invalid workshop or customer ID.'
            ];
        }

        // Check if registration exists
        $checkSql = "SELECT registration_id FROM workshop_registrations 
                     WHERE workshop_id = $workshop_id AND customer_id = $customer_id AND status = 'registered'";
        $existing = $this->db_fetch_one($checkSql);
        
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'You are not registered for this workshop.'
            ];
        }

        // Update status to cancelled
        $sql = "UPDATE workshop_registrations 
                SET status = 'cancelled' 
                WHERE workshop_id = $workshop_id AND customer_id = $customer_id";
        
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Registration cancelled successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to cancel registration. Please try again.'
            ];
        }
    }

    /**
     * Get registration count for a workshop
     * @param int $workshop_id Workshop ID
     * @return int Registration count
     */
    public function get_registration_count($workshop_id)
    {
        $workshop_id = (int)$workshop_id;
        $sql = "SELECT COUNT(registration_id) as count 
                FROM workshop_registrations 
                WHERE workshop_id = $workshop_id AND status = 'registered'";
        $result = $this->db_fetch_one($sql);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Check if customer is registered for a workshop
     * @param int $workshop_id Workshop ID
     * @param int $customer_id Customer ID
     * @return bool True if registered, false otherwise
     */
    public function is_registered($workshop_id, $customer_id)
    {
        $workshop_id = (int)$workshop_id;
        $customer_id = (int)$customer_id;
        
        $sql = "SELECT registration_id FROM workshop_registrations 
                WHERE workshop_id = $workshop_id AND customer_id = $customer_id AND status = 'registered'";
        $result = $this->db_fetch_one($sql);
        
        return $result !== false;
    }
}
?>

