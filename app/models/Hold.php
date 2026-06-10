<?php


class Hold
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
     // Check if a user has an unexpired active hold on a specific book.
 
    public function getActiveHold(int $userId, int $bookId)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM holds
             WHERE user_id = :uid AND book_id = :bid AND expires_at > NOW()"
        );
        $stmt->execute([':uid' => $userId, ':bid' => $bookId]);
        return $stmt->fetch();
    }

    
     // Get all active holds for a specific user, with book details.
     
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT h.*, b.title, b.author_name, b.cover_url
             FROM holds h JOIN books b ON b.id = h.book_id
             WHERE h.user_id = :uid AND h.expires_at > NOW()
             ORDER BY h.expires_at ASC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    
     //Create a new 24-hour hold on a book for a user.
   
    public function create(int $userId, int $bookId): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO holds (user_id, book_id, expires_at)
             VALUES (:uid, :bid, DATE_ADD(NOW(), INTERVAL 24 HOUR))"
        );
        return $stmt->execute([':uid' => $userId, ':bid' => $bookId]);
    }

    
     // Delete a hold ( when user converts hold to order).
     
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM holds WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
