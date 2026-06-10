<?php


class Vote
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
     // Check if a user has already voted on a specific nomination.
    
    public function hasVoted(int $nominationId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM votes WHERE nomination_id = :nid AND user_id = :uid"
        );
        $stmt->execute([':nid' => $nominationId, ':uid' => $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
