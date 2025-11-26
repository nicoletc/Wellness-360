<?php
/**
 * Admin Messages Controller
 * Handles logic for admin contact messages page
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Classes/ContactMessageModel.php';

class AdminMessagesController {
    private $messageModel;
    
    public function __construct() {
        $this->messageModel = new ContactMessageModel();
    }
    
    /**
     * Handle admin actions (update status, delete)
     * @param string $action Action to perform
     * @param int $message_id Message ID
     * @param array $data Additional data (e.g., status)
     * @return array Result with redirect URL
     */
    public function handleAction($action, $message_id, $data = []) {
        $message_id = (int)$message_id;
        
        if ($action === 'update_status' && $message_id) {
            $status = $data['status'] ?? '';
            if (in_array($status, ['new', 'read', 'replied', 'archived'])) {
                $this->messageModel->updateStatus($message_id, $status);
                return [
                    'status' => true,
                    'redirect' => 'messages.php?updated=1&status=' . urlencode($_GET['status'] ?? 'all')
                ];
            }
        }
        
        if ($action === 'delete' && $message_id) {
            $this->messageModel->deleteMessage($message_id);
            return [
                'status' => true,
                'redirect' => 'messages.php?deleted=1&status=' . urlencode($_GET['status'] ?? 'all')
            ];
        }
        
        return [
            'status' => false,
            'message' => 'Invalid action.'
        ];
    }
    
    /**
     * Get all data for the messages page
     * @param string $status_filter Status filter
     * @param string $action Action (view, etc.)
     * @param int $message_id Message ID for viewing
     * @return array Page data
     */
    public function index($status_filter = 'all', $action = '', $message_id = 0) {
        // Validate status filter
        $status_filter = in_array($status_filter, ['all', 'new', 'read', 'replied', 'archived']) ? $status_filter : 'all';
        
        // Get messages
        $messages = $this->messageModel->getAllMessages(
            $status_filter !== 'all' ? $status_filter : null,
            100
        );
        
        // Get counts
        $counts = $this->messageModel->getMessageCounts();
        
        // Get selected message for view
        $selectedMessage = null;
        if ($action === 'view' && $message_id) {
            $selectedMessage = $this->messageModel->getMessageById($message_id);
            if ($selectedMessage && $selectedMessage['status'] === 'new') {
                $this->messageModel->updateStatus($message_id, 'read');
                $selectedMessage['status'] = 'read';
            }
        }
        
        return [
            'messages' => $messages,
            'counts' => $counts,
            'status_filter' => $status_filter,
            'selectedMessage' => $selectedMessage,
            'action' => $action,
            'message_id' => $message_id
        ];
    }
}

?>

