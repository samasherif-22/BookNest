<?php

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieve and clear the flash message from the session.
 * Returns null if there is no pending flash message.
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}



 //Log an important action to the audit_logs table.

function logAction(string $action, string $entity, ?int $entityId = null, ?string $details = null): void
{
    try {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "INSERT INTO audit_logs (user_id, action, entity, entity_id, details)
             VALUES (:user_id, :action, :entity, :entity_id, :details)"
        );
        $stmt->execute([
            ':user_id'   => $_SESSION['user_id'] ?? null,
            ':action'    => $action,
            ':entity'    => $entity,
            ':entity_id' => $entityId,
            ':details'   => $details,
        ]);
    } catch (Exception $e) {
        // If logging fails, don't crash the whole app — just silently skip
        error_log("logAction failed: " . $e->getMessage());
    }
}


function sanitize($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Format a number as EGP currency.
 * Example: formatPrice(49.9) → "EGP 49.90"
 */
function formatPrice(float $amount): string
{
    return 'EGP ' . number_format($amount, 2);
}


function conditionBadgeClass(string $grade): string
{
    return match (strtolower($grade)) {
        'new'   => 'badge-new',
        'fine'  => 'badge-fine',
        'good'  => 'badge-good',
        'fair'  => 'badge-fair',
        default => 'bg-secondary',
    };
}


interface Observer
{
    public function update(string $eventType, array $data): void;
}


class NotificationObserver implements Observer
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

 
   public function update(string $eventType, array $data): void
    {
        if ($eventType === 'author_event_created') {
            try {
                // 1. Get all readers to notify them of the new event
                $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'READER'");
                $stmt->execute();
                $readers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 2. Insert a notification for each reader
                foreach ($readers as $reader) {
                    $stmt2 = $this->db->prepare(
                        "INSERT INTO notifications (user_id, message, link)
                         VALUES (:user_id, :message, :link)"
                    );
                    $stmt2->execute([
                        ':user_id' => $reader['id'],
                        ':message' => "A new event was just created: " . $data['event_title'],
                        ':link'    => BASE_URL . "index.php?page=events&action=show&id=" . $data['event_id'],
                    ]);
                }
            } catch (Exception $e) {
                // If notification fails, just log it silently so the main app doesn't crash
                error_log("Observer Notification Error: " . $e->getMessage());
            }
        }
    }
}


class EventSubject
{
    /** observer */
    private array $observers = [];

   
    public function attach(Observer $observer): void
    {
        $this->observers[] = $observer;
    }

    
    public function notify(string $eventType, array $data): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($eventType, $data);
        }
    }
}
