<?php

class OrderItem
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
      //Get all items for a given order, including book details.
     
    public function getByOrder(int $orderId): array
    {
        $stmt = $this->db->prepare(
            "SELECT oi.*, b.title, b.author_name, b.cover_url
             FROM order_items oi
             LEFT JOIN books b ON b.id = oi.item_id AND oi.item_type = 'book'
             WHERE oi.order_id = :order_id"
        );
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    
     // Insert a new line item into an order.
    
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO order_items (order_id, item_type, item_id, qty, unit_price)
             VALUES (:order_id, :item_type, :item_id, :qty, :unit_price)"
        );
        return $stmt->execute($data);
    }
}