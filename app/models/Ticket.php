<?php

class Ticket
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

   
    public function hasTicket(int $eventId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tickets WHERE event_id = :eid AND user_id = :uid"
        );
        $stmt->execute([':eid' => $eventId, ':uid' => $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    
    public function getUsersByEvent(int $eventId): array
    {
        $stmt = $this->db->prepare("SELECT user_id FROM tickets WHERE event_id = :eid AND status = 'confirmed'");
        $stmt->execute([':eid' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   
    public function getLobbyData(int $eventId, int $userId)
    {
        $stmt = $this->db->prepare(
            "SELECT t.type, t.status, e.status AS event_status, e.stream_url, e.title, e.event_date, e.city
             FROM tickets t JOIN events e ON e.id = t.event_id
             WHERE t.event_id = :eid AND t.user_id = :uid"
        );
        $stmt->execute([':eid' => $eventId, ':uid' => $userId]);
        return $stmt->fetch();
    }

    public function getByToken(string $token)
    {
        $stmt = $this->db->prepare(
            "SELECT t.*, e.status FROM tickets t
             JOIN events e ON e.id = t.event_id
             WHERE t.token = :token"
        );
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    
    public function create(int $eventId, int $userId, string $type = 'free', string $status = 'confirmed'): bool
    {
        $token = bin2hex(random_bytes(16));
        $stmt  = $this->db->prepare(
            "INSERT INTO tickets (event_id, user_id, type, token, status)
             VALUES (:eid, :uid, :type, :token, :status)"
        );
        return $stmt->execute([
            ':eid'    => $eventId,
            ':uid'    => $userId,
            ':type'   => $type,
            ':token'  => $token,
            ':status' => $status
        ]);
    }

    
    public function markAttended(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE tickets SET attended = 1, attended_at = NOW() WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT t.*, e.title AS event_title, e.event_date, e.city, e.status AS event_status
             FROM tickets t JOIN events e ON e.id = t.event_id
             WHERE t.user_id = :uid ORDER BY e.event_date DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function getWaitingList(int $eventId): array
    {
        $stmt = $this->db->prepare("SELECT user_id FROM tickets WHERE event_id = :eid AND status = 'waiting'");
        $stmt->execute([':eid' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}