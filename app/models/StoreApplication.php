<?php

class StoreApplication
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
     //Get all pending store applications for admin review.
     
    public function getPending(): array
    {
        $stmt = $this->db->prepare(
            "SELECT sa.*, u.name AS applicant_name, u.email AS applicant_email
             FROM store_applications sa JOIN users u ON u.id = sa.user_id
             WHERE sa.status = 'pending' ORDER BY sa.id ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }


    
     // Submit a new store application from a user.
    
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO store_applications (user_id, store_name, description, city)
             VALUES (:user_id, :store_name, :description, :city)"
        );
        return $stmt->execute($data);
    }

    
     // Approve or reject an application.
    
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE store_applications SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }
}
