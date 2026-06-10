<?php


class Order
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
     //Get all orders for a specific user (reader's order history).
     
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, s.name AS store_name
             FROM orders o LEFT JOIN stores s ON s.id = o.store_id
             WHERE o.user_id = :uid ORDER BY o.id DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    
      //Get all orders for a specific store (owner's order management).
     
    public function getByStore(int $storeId): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.name AS customer_name
             FROM orders o LEFT JOIN users u ON u.id = o.user_id
             WHERE o.store_id = :sid ORDER BY o.id DESC"
        );
        $stmt->execute([':sid' => $storeId]);
        return $stmt->fetchAll();
    }

   
     // Get a single order by its ID, including customer and store info.
  
    public function getById(int $id)
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.name AS customer_name, s.name AS store_name
             FROM orders o
             LEFT JOIN users u ON u.id = o.user_id
             LEFT JOIN stores s ON s.id = o.store_id
             WHERE o.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    
     // Create a new order (placed status).
    
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO orders (user_id, store_id, subtotal, total, type, status)
             VALUES (:user_id, :store_id, :subtotal, :total, :type, 'placed')"
        );
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    
     // Update the status of an order (F2 — Click-and-Collect workflow).
    
    public function updateStatus(int $id, string $newStatus): bool
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $newStatus, ':id' => $id]);
    }

    
     // Update tax amount and total on an order (F11).
  
    public function applyTax(int $id, float $taxAmount, float $total): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE orders SET tax_amount = :tax, total = :total WHERE id = :id"
        );
        return $stmt->execute([':tax' => $taxAmount, ':total' => $total, ':id' => $id]);
    }

    
    // Count total orders in the system (admin dashboard).
  
    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
     //Get all orders in the system (for System Admin reports).
    
    public function getAll(): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.name AS customer_name, s.name AS store_name
             FROM orders o 
             LEFT JOIN users u ON u.id = o.user_id
             LEFT JOIN stores s ON s.id = o.store_id
             ORDER BY o.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   
    // Count all orders grouped by type ('pickup' vs 'delivery').
     
     
    public function countByType(): array
    {
        $stmt = $this->db->prepare(
            "SELECT type, COUNT(*) AS cnt FROM orders GROUP BY type"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
