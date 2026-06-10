<?php


class MemberProgress
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
     // Get the current chapter progress for a user on a specific goal.
    
     
    public function get(int $goalId, int $userId)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM member_progress WHERE goal_id = :gid AND user_id = :uid"
        );
        $stmt->execute([':gid' => $goalId, ':uid' => $userId]);
        return $stmt->fetch();
    }

    
      //Insert or update chapter progress for a user.
   
    public function upsert(int $goalId, int $userId, int $chapter): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO member_progress (goal_id, user_id, current_chapter)
             VALUES (:gid, :uid, :ch)
             ON DUPLICATE KEY UPDATE current_chapter = :ch2"
        );
        return $stmt->execute([
            ':gid' => $goalId, ':uid' => $userId,
            ':ch'  => $chapter, ':ch2' => $chapter,
        ]);
    }
}
