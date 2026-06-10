<?php

class Book {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection(); //singleton design pattern
    }

    public function getAll($preferredStoreId = 0)
    {
        $db = Database::getInstance()->getConnection();
        //b is allias for books and s is allias for stores
        
        $sql = "SELECT b.*, s.name as store_name  
                FROM books b 
                LEFT JOIN stores s ON b.store_id = s.id";

        if ($preferredStoreId > 0) { //  // Pin books from that store to appear first, then sort by newest
            $sql .= " ORDER BY (b.store_id = :pref_id) DESC, b.created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':pref_id', $preferredStoreId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
            $sql .= " ORDER BY b.created_at DESC";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC); //return all rows as associative arr
        }
    }
    
    // Get a single book by its ID
    public function getById(int $id) {
        $stmt = $this->db->prepare(
            "SELECT b.*, s.name AS store_name, s.owner_id
             FROM books b
             LEFT JOIN stores s ON s.id = b.store_id
             WHERE b.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(); // Return one book only
    }
     
    // Get all books for a specific store,Ordered by newest first
    public function getByStore(int $storeId): array {
        $stmt = $this->db->prepare("SELECT * FROM books WHERE store_id = :store_id ORDER BY id DESC");
        $stmt->execute([':store_id' => $storeId]);
        return $stmt->fetchAll();
    }


    public function search(string $query): array {
        $stmt = $this->db->prepare(
            "SELECT id, title, author_name, final_price, condition_grade, cover_url
             FROM books
             WHERE title LIKE :q OR author_name LIKE :q
             LIMIT 20"
        );
        $stmt->execute([':q' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }

    //according to book genre
    public function getRecommended(string $genre, int $excludeId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM books
             WHERE genre = :genre AND id != :id AND stock_qty > 0
             ORDER BY RAND() LIMIT 3"
        );
        $stmt->execute([':genre' => $genre, ':id' => $excludeId]);
        return $stmt->fetchAll();
    }



    public function create(array $data) {
        try {
    
            $stmt = $this->db->prepare(
                "INSERT INTO books (store_id, isbn, title, author_name, genre, base_price, final_price, condition_grade, stock_qty, cover_url, description)
                 VALUES (:store_id, :isbn, :title, :author_name, :genre, :base_price, :final_price, :condition_grade, :stock_qty, :cover_url, :description)"
            );
            if ($stmt->execute($data)) return $this->db->lastInsertId();
            return false;
        } catch (PDOException $e) { die("❌ Error (Create): " . $e->getMessage()); }
    }

    public function update(array $data): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE books SET title=:title, author_name=:author_name, genre=:genre, isbn=:isbn, base_price=:base_price, final_price=:final_price, condition_grade=:condition_grade, stock_qty=:stock_qty, description=:description, cover_url=:cover_url WHERE id = :id"
            );
            return $stmt->execute($data);
        } catch (PDOException $e) { die("❌ Error (Update): " . $e->getMessage()); }
    }

    public function updateCover(int $id, string $url): bool {
        $stmt = $this->db->prepare("UPDATE books SET cover_url = :url WHERE id = :id");
        return $stmt->execute([':url' => $url, ':id' => $id]);
    }


    public function decrementStock(int $id): bool {
        $stmt = $this->db->prepare("UPDATE books SET stock_qty = stock_qty - 1 WHERE id = :id AND stock_qty > 0");
        return $stmt->execute([':id' => $id]);
    }

    public function incrementStock(int $id): bool {
        $stmt = $this->db->prepare("UPDATE books SET stock_qty = stock_qty + 1 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM books WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function count(): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM books");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
     // Get only the books purchased by a specific user.
     
    public function getPurchasedByUser(int $userId): array
    {
      
        $stmt = $this->db->prepare("
            SELECT DISTINCT b.id, b.title 
            FROM books b
            JOIN order_items oi ON b.id = oi.item_id AND oi.item_type = 'book'
            JOIN orders o ON o.id = oi.order_id
            WHERE o.user_id = :uid 
        ");
        
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}