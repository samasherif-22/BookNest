<?php

class User
{
    private PDO $db;

    public function __construct()
    {
        
        $this->db = Database::getInstance()->getConnection();
    }

    
     //Get all users, newest first.
   
    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT id, name, email, role, privacy, loyalty_points, created_at FROM users ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    
     // Find a single user by their primary key.
     
    public function getById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    
     // Find a user by their email address. Used during login.

        public function getByEmail(string $email)
        {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC); 
        }

    
     // Insert a new user record into the database.
    
        public function create(array $data): bool
        {
            $stmt = $this->db->prepare(
                "INSERT INTO users (name, email, password_hash, role)
                VALUES (:name, :email, :password_hash, :role)"
            );
            return $stmt->execute($data);
        }
    
     // Update a user's role (used by admin).
    
    public function updateRole(int $id, string $role): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
        return $stmt->execute([':role' => $role, ':id' => $id]);
    }

    
     //Update privacy setting for a user ('PUBLIC' or 'PRIVATE').
    
    public function updatePrivacy(int $id, string $privacy): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET privacy = :privacy WHERE id = :id");
        return $stmt->execute([':privacy' => $privacy, ':id' => $id]);
    }

    
     // Anonymize a user's personal data (GDPR delete request).
    public function anonymize(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users
             SET name = 'Deleted User',
                 email = CONCAT('deleted_', id, '@anon.com'),
                 password_hash = '',
                 privacy = 'PRIVATE'
             WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    
     // Search users by name or email (admin panel).
     
    public function search(string $query): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, role, created_at FROM users
             WHERE name LIKE :q OR email LIKE :q
             ORDER BY name ASC LIMIT 50"
        );
        $stmt->execute([':q' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }

    
     // Count total registered users.
     
    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}
