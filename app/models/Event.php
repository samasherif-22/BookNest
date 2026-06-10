<?php

class Event
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    
    public function getAll(): array
    {
        $stmt = $this->db->prepare(
            "SELECT e.*, u.name AS organizer_name
             FROM events e LEFT JOIN users u ON u.id = e.organizer_id
             ORDER BY e.event_date ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id)
    {
        $stmt = $this->db->prepare(
            "SELECT e.*, u.name AS organizer_name,
                    (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id AND t.status = 'confirmed') AS tickets_sold
             FROM events e LEFT JOIN users u ON u.id = e.organizer_id
             WHERE e.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }


    public function getByOrganizer(int $organizerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM events WHERE organizer_id = :org ORDER BY event_date DESC"
        );
        $stmt->execute([':org' => $organizerId]);
        return $stmt->fetchAll();
    }


    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO events
             (organizer_id, title, description, event_date, capacity, stream_url, city, ticket_price)
             VALUES (:organizer_id, :title, :description, :event_date, :capacity, :stream_url, :city, :ticket_price)"
        );
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }


    public function updateEventStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE events SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

   
    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM events");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}