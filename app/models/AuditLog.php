<?php

class AuditLog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection(); //SINGLETON design pattern
    }


    public function getRecent(): array
    {
        $stmt = $this->db->prepare(
            "SELECT al.*, u.name AS user_name
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT 200"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

  
    public function getByEntity(string $entity): array
    {
        $stmt = $this->db->prepare(
            "SELECT al.*, u.name AS user_name
             FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id
             WHERE al.entity = :entity
             ORDER BY al.created_at DESC LIMIT 100"
        );
        $stmt->execute([':entity' => $entity]);
        return $stmt->fetchAll();
    }
}
