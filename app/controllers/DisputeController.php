<?php


require_once BASE_PATH . '/app/models/Dispute.php';
require_once BASE_PATH . '/app/models/Notification.php'; // Include Notification model

class DisputeController extends Controller
{
    private Dispute $disputeModel;

    public function __construct()
    {
        $this->disputeModel = new Dispute();
    }

    public function create()
    {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $data = [
                'order_id' => (int)$_POST['order_id'],
                'user_id'  => currentUserId(), 
                'reason'   => trim($_POST['description']) 
            ];

            if ($this->disputeModel->create($data)) {
                
                // 1. Fetch all system admin accounts to send them a notification
                $db = Database::getInstance()->getConnection();
                $stmt = $db->query("SELECT id FROM users WHERE role = 'SYSTEM_ADMIN'");
                $admins = $stmt->fetchAll();
                
                // 2. Send notification to each admin
                $notifModel = new Notification();
                foreach ($admins as $admin) {
                    $msg = "New dispute opened for Order #" . $data['order_id'];
                    $link = BASE_URL . "index.php?page=admin&action=disputes";
                    $notifModel->create($admin['id'], $msg, $link);
                }

                // 3. Redirect back to the order page
                header('Location: ' . BASE_URL . 'index.php?page=orders&action=show&id=' . $data['order_id'] . '&success=DisputeSubmitted');
                exit;
            } else {
                echo "Error submitting dispute!";
            }
        }
    }
}