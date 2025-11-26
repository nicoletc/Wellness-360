<?php
/**
 * Contact Message Model
 * Handles database operations for contact form messages
 */

require_once __DIR__ . '/../settings/db_class.php';

class ContactMessageModel extends db_connection
{
    /**
     * Save a contact message
     * @param array $data Message data (first_name, last_name, email, phone, subject, message, customer_id)
     * @return array Result with status and message_id
     */
    public function saveMessage($data)
    {
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }
        
        $first_name = $this->escape_string($data['first_name'] ?? '');
        $last_name = $this->escape_string($data['last_name'] ?? '');
        $email = $this->escape_string($data['email'] ?? '');
        $phone = !empty($data['phone']) ? $this->escape_string($data['phone']) : 'NULL';
        $subject = $this->escape_string($data['subject'] ?? '');
        $message = $this->escape_string($data['message'] ?? '');
        $customer_id = isset($data['customer_id']) && $data['customer_id'] ? (int)$data['customer_id'] : 'NULL';
        
        $phone_sql = $phone !== 'NULL' ? "'$phone'" : 'NULL';
        $customer_sql = $customer_id !== 'NULL' ? $customer_id : 'NULL';
        
        $sql = "INSERT INTO contact_messages 
                (first_name, last_name, email, phone, subject, message, customer_id, status)
                VALUES ('$first_name', '$last_name', '$email', $phone_sql, '$subject', '$message', $customer_sql, 'new')";
        
        if ($this->db_query($sql)) {
            $message_id = mysqli_insert_id($this->db);
            return [
                'status' => true,
                'message_id' => $message_id,
                'message' => 'Your message has been sent successfully!'
            ];
        } else {
            $error = mysqli_error($this->db) ?? 'Unknown database error';
            error_log("ContactMessageModel::saveMessage SQL Error: " . $error);
            return [
                'status' => false,
                'message' => 'Failed to send message: ' . $error
            ];
        }
    }
    
    /**
     * Get all contact messages
     * @param string $status Filter by status (optional)
     * @param int $limit Number of messages to return
     * @param int $offset Offset for pagination
     * @return array Messages
     */
    public function getAllMessages($status = null, $limit = 50, $offset = 0)
    {
        if (!$this->db_connect()) {
            return [];
        }
        
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $status_filter = $status ? "WHERE status = '" . $this->escape_string($status) . "'" : '';
        
        $sql = "SELECT cm.*, c.customer_name, c.customer_email
                FROM contact_messages cm
                LEFT JOIN customers c ON cm.customer_id = c.customer_id
                $status_filter
                ORDER BY cm.created_at DESC
                LIMIT $limit OFFSET $offset";
        
        $messages = $this->db_fetch_all($sql);
        
        return $messages ?: [];
    }
    
    /**
     * Get a single message by ID
     * @param int $message_id Message ID
     * @return array|false Message data or false
     */
    public function getMessageById($message_id)
    {
        if (!$this->db_connect()) {
            return false;
        }
        
        $message_id = (int)$message_id;
        
        $sql = "SELECT cm.*, c.customer_name, c.customer_email
                FROM contact_messages cm
                LEFT JOIN customers c ON cm.customer_id = c.customer_id
                WHERE cm.message_id = $message_id
                LIMIT 1";
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Update message status
     * @param int $message_id Message ID
     * @param string $status New status
     * @return bool Success
     */
    public function updateStatus($message_id, $status)
    {
        if (!$this->db_connect()) {
            return false;
        }
        
        $message_id = (int)$message_id;
        $status = $this->escape_string($status);
        
        if (!in_array($status, ['new', 'read', 'replied', 'archived'])) {
            return false;
        }
        
        $sql = "UPDATE contact_messages 
                SET status = '$status', updated_at = NOW()
                WHERE message_id = $message_id";
        
        return $this->db_query($sql);
    }
    
    /**
     * Delete a message
     * @param int $message_id Message ID
     * @return bool Success
     */
    public function deleteMessage($message_id)
    {
        if (!$this->db_connect()) {
            return false;
        }
        
        $message_id = (int)$message_id;
        
        $sql = "DELETE FROM contact_messages WHERE message_id = $message_id";
        
        return $this->db_query($sql);
    }
    
    /**
     * Get message count by status
     * @return array Counts by status
     */
    public function getMessageCounts()
    {
        if (!$this->db_connect()) {
            return [
                'new' => 0,
                'read' => 0,
                'replied' => 0,
                'archived' => 0,
                'total' => 0
            ];
        }
        
        $sql = "SELECT status, COUNT(*) as count 
                FROM contact_messages 
                GROUP BY status";
        
        $results = $this->db_fetch_all($sql);
        
        $counts = [
            'new' => 0,
            'read' => 0,
            'replied' => 0,
            'archived' => 0,
            'total' => 0
        ];
        
        if ($results) {
            foreach ($results as $row) {
                $status = $row['status'];
                $counts[$status] = (int)$row['count'];
                $counts['total'] += (int)$row['count'];
            }
        }
        
        return $counts;
    }
    
    /**
     * Get count of new messages only
     * @return int Count of new messages
     */
    public function getNewMessageCount()
    {
        if (!$this->db_connect()) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as count 
                FROM contact_messages 
                WHERE status = 'new'";
        
        $result = $this->db_fetch_one($sql);
        
        return $result ? (int)$result['count'] : 0;
    }
}

?>

