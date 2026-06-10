<?php

class Club
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
    // Get all clubs, with member count and organizer name.
     
    public function getAll(): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.name AS organizer_name,
                    (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.id) AS member_count
             FROM clubs c
             LEFT JOIN users u ON u.id = c.organizer_id
             ORDER BY c.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
     // Get a single club by ID.
     
    public function getById(int $id)
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.name AS organizer_name,
                    (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.id) AS member_count
             FROM clubs c LEFT JOIN users u ON u.id = c.organizer_id
             WHERE c.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
     // Get all clubs organized by a specific clubOrgnizer.
     
    public function getByOrganizer(int $organizerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.id) AS member_count
             FROM clubs c
             WHERE c.organizer_id = :org 
             ORDER BY c.id DESC"
        );
        $stmt->execute([':org' => $organizerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
     // Check if a user is already a member.
     
    public function isMember(int $clubId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM club_members WHERE club_id = :cid AND user_id = :uid"
        );
        $stmt->execute([':cid' => $clubId, ':uid' => $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    
     // Create a new book club.
     
    public function create(array $data): bool 
    {
        $stmt = $this->db->prepare(
            "INSERT INTO clubs (organizer_id, name, description, genre, is_private) 
             VALUES (:organizer_id, :name, :description, :genre, :is_private)"
        );
        return $stmt->execute($data);
    }

    
     // Add a user to a club.
     
    public function addMember(int $clubId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO club_members (club_id, user_id, role)
             VALUES (:club_id, :user_id, 'member')"
        );
        return $stmt->execute([':club_id' => $clubId, ':user_id' => $userId]);
    }

    
     // Get all pending join requests.
     
    public function getPendingRequests(int $clubId): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                jr.id, 
                jr.user_id, 
                jr.club_id, 
                jr.status, 
                jr.requested_at AS created_at,
                u.name, 
                u.email
             FROM join_requests jr 
             JOIN users u ON u.id = jr.user_id
             WHERE jr.club_id = :cid AND jr.status = 'pending'
             ORDER BY jr.requested_at DESC"
        );
        $stmt->execute([':cid' => $clubId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
     // Update join request status.
     
    public function updateJoinRequest(int $requestId, string $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE join_requests SET status = :status WHERE id = :id"
        );
        return $stmt->execute([':status' => $status, ':id' => $requestId]);
    }


     // Delete a previous join request (used if a user was rejected and wants to retry).
     
    public function deleteJoinRequest(int $clubId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM join_requests WHERE club_id = :cid AND user_id = :uid AND status = 'rejected'"
        );
        return $stmt->execute([':cid' => $clubId, ':uid' => $userId]);
    }

    
     // Get club members list.
     
    public function getMembers(int $clubId): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.name, u.email, cm.role, cm.joined_at
             FROM club_members cm JOIN users u ON u.id = cm.user_id
             WHERE cm.club_id = :cid ORDER BY cm.joined_at ASC"
        );
        $stmt->execute([':cid' => $clubId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  
    //show specific user clubs only 
    public function getJoinedClubs($userId) {
        $sql = "SELECT c.* FROM clubs c 
                INNER JOIN club_members cm ON c.id = cm.club_id 
                WHERE cm.user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM clubs");
        return (int) $stmt->fetchColumn();
    }
}