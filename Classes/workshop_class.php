<?php
/**
 * Workshop Class
 * Extends database connection and contains workshop methods
 */

require_once __DIR__ . '/../settings/db_class.php';

class workshop_class extends db_connection
{
    /**
     * Add a new workshop to the database
     * @param array $data Workshop data
     * @return array Result with status and message
     */
    public function add($data)
    {
        // Validate required fields
        if (empty($data['workshop_title'])) {
            return [
                'status' => false,
                'message' => 'Workshop title is required.'
            ];
        }

        if (empty($data['workshop_leader'])) {
            return [
                'status' => false,
                'message' => 'Workshop leader is required.'
            ];
        }

        if (empty($data['workshop_date'])) {
            return [
                'status' => false,
                'message' => 'Workshop date is required.'
            ];
        }

        if (empty($data['workshop_time'])) {
            return [
                'status' => false,
                'message' => 'Workshop time is required.'
            ];
        }

        if (empty($data['workshop_type'])) {
            return [
                'status' => false,
                'message' => 'Workshop type is required.'
            ];
        }

        if (empty($data['location']) && $data['workshop_type'] === 'in-person') {
            return [
                'status' => false,
                'message' => 'Location is required for in-person workshops.'
            ];
        }

        if (empty($data['max_participants']) || $data['max_participants'] <= 0) {
            return [
                'status' => false,
                'message' => 'Maximum participants must be greater than 0.'
            ];
        }

        // Ensure database connection
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }

        $workshop_title = $this->escape_string(trim($data['workshop_title']));
        $workshop_desc = isset($data['workshop_desc']) ? $this->escape_string(trim($data['workshop_desc'])) : '';
        $workshop_leader = $this->escape_string(trim($data['workshop_leader']));
        $workshop_date = $this->escape_string(trim($data['workshop_date']));
        $workshop_time = $this->escape_string(trim($data['workshop_time']));
        $workshop_type = $this->escape_string(trim($data['workshop_type']));
        $location = isset($data['location']) ? $this->escape_string(trim($data['location'])) : '';
        $max_participants = (int)$data['max_participants'];
        $customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
        $workshop_image = isset($data['workshop_image']) ? $this->escape_string(trim($data['workshop_image'])) : '';

        // Validate title length
        if (strlen($workshop_title) < 3) {
            return [
                'status' => false,
                'message' => 'Workshop title must be at least 3 characters long.'
            ];
        }

        if (strlen($workshop_title) > 200) {
            return [
                'status' => false,
                'message' => 'Workshop title must not exceed 200 characters.'
            ];
        }

        // Validate workshop type
        if (!in_array($workshop_type, ['in-person', 'virtual'])) {
            return [
                'status' => false,
                'message' => 'Invalid workshop type. Must be "in-person" or "virtual".'
            ];
        }

        // Validate date format
        $date_check = DateTime::createFromFormat('Y-m-d', $workshop_date);
        if (!$date_check || $date_check->format('Y-m-d') !== $workshop_date) {
            return [
                'status' => false,
                'message' => 'Invalid date format. Use YYYY-MM-DD.'
            ];
        }

        // Insert workshop
        $sql = "INSERT INTO workshops (customer_id, workshop_title, workshop_image, workshop_desc, workshop_leader, workshop_date, workshop_time, workshop_type, location, max_participants) 
                VALUES ($customer_id, '$workshop_title', '$workshop_image', '$workshop_desc', '$workshop_leader', '$workshop_date', '$workshop_time', '$workshop_type', '$location', $max_participants)";
        
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Workshop added successfully.',
                'workshop_id' => mysqli_insert_id($this->db)
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to add workshop. Please try again.'
            ];
        }
    }

    /**
     * Get all workshops with customer information and registration count
     * @return array All workshops
     */
    public function get_all()
    {
        $sql = "SELECT w.*, 
                       c.customer_name,
                       c.customer_email,
                       COUNT(r.registration_id) AS registered_count
                FROM workshops w
                LEFT JOIN customer c ON w.customer_id = c.customer_id
                LEFT JOIN workshop_registrations r ON w.workshop_id = r.workshop_id AND r.status = 'registered'
                GROUP BY w.workshop_id, w.customer_id, w.workshop_title, w.workshop_image, w.workshop_desc, 
                         w.workshop_leader, w.workshop_date, w.workshop_time, w.workshop_type, w.location, 
                         w.max_participants, c.customer_name, c.customer_email
                ORDER BY w.workshop_date DESC, w.workshop_time DESC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get a single workshop by ID
     * @param int $workshop_id Workshop ID
     * @return array|false Workshop data or false if not found
     */
    public function get_one($workshop_id)
    {
        $workshop_id = (int)$workshop_id;
        $sql = "SELECT w.*, 
                       c.customer_name,
                       c.customer_email
                FROM workshops w
                LEFT JOIN customer c ON w.customer_id = c.customer_id
                WHERE w.workshop_id = $workshop_id";
        
        return $this->db_fetch_one($sql);
    }

    /**
     * Update a workshop
     * @param array $data Workshop data
     * @return array Result with status and message
     */
    public function update($data)
    {
        // Validate required fields
        if (empty($data['workshop_id']) || $data['workshop_id'] <= 0) {
            return [
                'status' => false,
                'message' => 'Workshop ID is required.'
            ];
        }

        if (empty($data['workshop_title'])) {
            return [
                'status' => false,
                'message' => 'Workshop title is required.'
            ];
        }

        if (empty($data['workshop_leader'])) {
            return [
                'status' => false,
                'message' => 'Workshop leader is required.'
            ];
        }

        if (empty($data['workshop_date'])) {
            return [
                'status' => false,
                'message' => 'Workshop date is required.'
            ];
        }

        if (empty($data['workshop_time'])) {
            return [
                'status' => false,
                'message' => 'Workshop time is required.'
            ];
        }

        if (empty($data['workshop_type'])) {
            return [
                'status' => false,
                'message' => 'Workshop type is required.'
            ];
        }

        if (empty($data['location']) && $data['workshop_type'] === 'in-person') {
            return [
                'status' => false,
                'message' => 'Location is required for in-person workshops.'
            ];
        }

        if (empty($data['max_participants']) || $data['max_participants'] <= 0) {
            return [
                'status' => false,
                'message' => 'Maximum participants must be greater than 0.'
            ];
        }

        $workshop_id = (int)$data['workshop_id'];
        $workshop_title = $this->escape_string(trim($data['workshop_title']));
        $workshop_desc = isset($data['workshop_desc']) ? $this->escape_string(trim($data['workshop_desc'])) : '';
        $workshop_leader = $this->escape_string(trim($data['workshop_leader']));
        $workshop_date = $this->escape_string(trim($data['workshop_date']));
        $workshop_time = $this->escape_string(trim($data['workshop_time']));
        $workshop_type = $this->escape_string(trim($data['workshop_type']));
        $location = isset($data['location']) ? $this->escape_string(trim($data['location'])) : '';
        $max_participants = (int)$data['max_participants'];
        $workshop_image = isset($data['workshop_image']) ? $this->escape_string(trim($data['workshop_image'])) : '';

        // Check if workshop exists
        $existing = $this->get_one($workshop_id);
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'Workshop not found.'
            ];
        }

        // Validate title length
        if (strlen($workshop_title) < 3) {
            return [
                'status' => false,
                'message' => 'Workshop title must be at least 3 characters long.'
            ];
        }

        if (strlen($workshop_title) > 200) {
            return [
                'status' => false,
                'message' => 'Workshop title must not exceed 200 characters.'
            ];
        }

        // Validate workshop type
        if (!in_array($workshop_type, ['in-person', 'virtual'])) {
            return [
                'status' => false,
                'message' => 'Invalid workshop type. Must be "in-person" or "virtual".'
            ];
        }

        // Validate date format
        $date_check = DateTime::createFromFormat('Y-m-d', $workshop_date);
        if (!$date_check || $date_check->format('Y-m-d') !== $workshop_date) {
            return [
                'status' => false,
                'message' => 'Invalid date format. Use YYYY-MM-DD.'
            ];
        }

        // Update workshop
        $sql = "UPDATE workshops 
                SET workshop_title = '$workshop_title', 
                    workshop_desc = '$workshop_desc',
                    workshop_leader = '$workshop_leader', 
                    workshop_date = '$workshop_date',
                    workshop_time = '$workshop_time',
                    workshop_type = '$workshop_type',
                    location = '$location',
                    max_participants = $max_participants";
        
        if (!empty($workshop_image)) {
            $sql .= ", workshop_image = '$workshop_image'";
        }
        
        $sql .= " WHERE workshop_id = $workshop_id";
        
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Workshop updated successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to update workshop. Please try again.'
            ];
        }
    }

    /**
     * Delete a workshop
     * @param int $workshop_id Workshop ID
     * @return array Result with status and message
     */
    public function delete($workshop_id)
    {
        $workshop_id = (int)$workshop_id;

        // Check if workshop exists
        $existing = $this->get_one($workshop_id);
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'Workshop not found.'
            ];
        }

        // Delete workshop
        $sql = "DELETE FROM workshops WHERE workshop_id = $workshop_id";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Workshop deleted successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to delete workshop. Please try again.'
            ];
        }
    }
}
?>

