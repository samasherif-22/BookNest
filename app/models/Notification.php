<?php


class Notification
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
     // Get recent notifications for a user (newest first, limit 20).
     
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM notifications
             WHERE user_id = :uid
             ORDER BY created_at DESC LIMIT 20"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    
     // Count unread notifications for a user.
     
    public function countUnread(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0"
        );
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    
     // Mark all notifications as read for a user.
     
    public function markAllRead(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :uid"
        );
        return $stmt->execute([':uid' => $userId]);
    }

   
     //Insert a new notification.
     
    public function create(int $userId, string $message, string $link = '#'): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, message, link)
             VALUES (:uid, :message, :link)"
        );
        return $stmt->execute([':uid' => $userId, ':message' => $message, ':link' => $link]);
    }
}
