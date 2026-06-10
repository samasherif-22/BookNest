<?php


class Nomination
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
     // Get all open nominations for a club, with vote counts.
    
    public function getByClub(int $clubId): array
    {
        $stmt = $this->db->prepare(
            "SELECT n.id, n.book_title, n.user_id, u.name AS nominator_name,
                    COUNT(v.id) AS vote_count
             FROM nominations n
             LEFT JOIN votes v ON v.nomination_id = n.id
             LEFT JOIN users u ON u.id = n.user_id
             WHERE n.club_id = :cid AND n.is_open = 1
             GROUP BY n.id
             ORDER BY vote_count DESC"
        );
        $stmt->execute([':cid' => $clubId]);
        return $stmt->fetchAll();
    }

    
    // Submit a new book nomination.
    
    public function create(int $clubId, int $userId, string $bookTitle): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO nominations (club_id, user_id, book_title)
             VALUES (:club_id, :user_id, :book_title)"
        );
        return $stmt->execute([
            ':club_id'    => $clubId,
            ':user_id'    => $userId,
            ':book_title' => $bookTitle,
        ]);
    }

    
     // Cast a vote for a nomination.
   
    public function castVote(int $nominationId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO votes (nomination_id, user_id) VALUES (:nid, :uid)"
        );
        return $stmt->execute([':nid' => $nominationId, ':uid' => $userId]);
    }
}
