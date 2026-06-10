<?php

class ReadingGoal
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
     // Get all reading goals for a club with member progress.
 
    public function getByClub(int $clubId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM reading_goals WHERE club_id = :cid ORDER BY due_date ASC"
        );
        $stmt->execute([':cid' => $clubId]);
        return $stmt->fetchAll();
    }

    
     // Get a single reading goal by ID.
   
    public function getById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM reading_goals WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    
     // Create a new reading goal for a club.
    
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO reading_goals (club_id, target_chapter, due_date, label)
             VALUES (:club_id, :target_chapter, :due_date, :label)"
        );
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    
     // Get all member progress rows for a specific goal.
    
    public function getMemberProgress(int $goalId): array
    {
        $stmt = $this->db->prepare(
            "SELECT mp.*, u.name
             FROM member_progress mp JOIN users u ON u.id = mp.user_id
             WHERE mp.goal_id = :gid ORDER BY mp.current_chapter DESC"
        );
        $stmt->execute([':gid' => $goalId]);
        return $stmt->fetchAll();
    }

    
     /* Upsert a user's chapter progress for a goal.
      If a row exists for (goal_id, user_id), update it; otherwise insert.*/
   
    public function upsertProgress(int $goalId, int $userId, int $chapter): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO member_progress (goal_id, user_id, current_chapter)
             VALUES (:gid, :uid, :ch)
             ON DUPLICATE KEY UPDATE current_chapter = :ch2"
        );
        return $stmt->execute([
            ':gid' => $goalId,
            ':uid' => $userId,
            ':ch'  => $chapter,
            ':ch2' => $chapter,
        ]);
    }

    
     // Get a single user's max chapter progress across all goals in a club.
 
     
    public function getUserMaxChapter(int $clubId, int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT MAX(mp.current_chapter) FROM member_progress mp
             JOIN reading_goals rg ON rg.id = mp.goal_id
             WHERE rg.club_id = :cid AND mp.user_id = :uid"
        );
        $stmt->execute([':cid' => $clubId, ':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }
}
