<?php

require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Notification.php';

class EventController extends Controller
{
    private Event         $eventModel;
    private Ticket        $ticketModel;
    private Notification $notifModel;

    public function __construct()
    {
        $this->eventModel  = new Event();
        $this->ticketModel = new Ticket();
        $this->notifModel  = new Notification();
    }

   
    public function index(): void
    {
        requireLogin();
        if ($_SESSION['role'] === 'AUTHOR') {
            $events = $this->eventModel->getByOrganizer($_SESSION['user_id']);
        } else {
            $events = $this->eventModel->getAll();
        }
        $this->view('events/index', ['events' => $events]);
    }

  
    public function create(): void
    {
        requireLogin();
        if (!in_array($_SESSION['role'], ['AUTHOR', 'SYSTEM_ADMIN'])) {
            setFlash('danger', 'Unauthorized access.');
            header('Location: index.php?page=events'); exit;
        }
        $this->view('events/form', ['event' => null]);
    }

    
    public function store(): void
    {
        requireLogin();
        if (!in_array($_SESSION['role'], ['AUTHOR', 'SYSTEM_ADMIN'])) { exit('Unauthorized'); }

        $data = [
            ':organizer_id' => $_SESSION['user_id'], // ربط الإيفينت بالمؤلف الحالي
            ':title'        => trim($_POST['title'] ?? ''),
            ':description'  => trim($_POST['description'] ?? ''),
            ':event_date'   => $_POST['event_date'] ?? '',
            ':capacity'     => (int)($_POST['capacity'] ?? 0),
            ':stream_url'   => trim($_POST['stream_url'] ?? ''),
            ':city'         => trim($_POST['city'] ?? ''),
            ':ticket_price' => (float)($_POST['ticket_price'] ?? 0),
        ];

        $eventId = $this->eventModel->create($data);

        if ($eventId) {
            
            // Observer Pattern 
            //  Create the Subject
            $eventSubject = new EventSubject();
            // Attach  Observer
            $eventSubject->attach(new NotificationObserver());
            // Notify all readers
            $eventSubject->notify('author_event_created', [
                'author_id'   => $_SESSION['user_id'],
                'event_id'    => $eventId,
                'event_title' => $data[':title']
            ]);
            

            setFlash('success', 'Event created successfully!');
            header('Location: index.php?page=events&action=show&id=' . $eventId);
        } else {
            setFlash('danger', 'Failed to create event.');
            header('Location: index.php?page=events&action=create');
        }
        exit;
    }

  
    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $event = $this->eventModel->getById($id);
        if (!$event) { header('Location: index.php?page=events'); exit; }

        $ticket = null;
        if (isset($_SESSION['user_id'])) {
            $ticket = $this->ticketModel->getLobbyData($id, $_SESSION['user_id']);
        }
        $this->view('events/show', ['event' => $event, 'ticket' => $ticket]);
    }

   
    public function buyTicket(): void
    {
        requireLogin();
        $eventId = (int)($_POST['event_id'] ?? 0);
        $userId  = $_SESSION['user_id'];
        $event   = $this->eventModel->getById($eventId);

        if ($this->ticketModel->hasTicket($eventId, $userId)) {
            setFlash('info', 'Already registered.');
            header('Location: index.php?page=events&action=show&id='.$eventId); exit;
        }

        if ((int)$event['tickets_sold'] < (int)$event['capacity']) {
            $this->ticketModel->create($eventId, $userId, 'free', 'confirmed');
            setFlash('success', 'Spot confirmed!');
        } else {
            $this->ticketModel->create($eventId, $userId, 'free', 'waiting');
            setFlash('warning', 'Added to waiting list.');
        }
        header('Location: index.php?page=events&action=show&id='.$eventId); exit;
    }

   
    public function updateStatus(): void
    {
        requireLogin();
        $eventId = (int)($_POST['event_id'] ?? 0);
        $status  = trim($_POST['status'] ?? 'upcoming');
        $event   = $this->eventModel->getById($eventId);

        $isOwner = ($event && (int)$event['organizer_id'] === (int)$_SESSION['user_id']);
        $isAdmin = ($_SESSION['role'] === 'SYSTEM_ADMIN');

        if (!$isOwner && !$isAdmin) {
            setFlash('danger', 'Unauthorized action.');
            header('Location: index.php?page=events'); exit;
        }

        if ($this->eventModel->updateEventStatus($eventId, $status)) {
            if ($status === 'live') {
                foreach ($this->ticketModel->getUsersByEvent($eventId) as $t) {
                    $this->notifModel->create($t['user_id'], "Event '{$event['title']}' is LIVE!", "index.php?page=events&action=lobby&id=$eventId");
                }
                foreach ($this->ticketModel->getWaitingList($eventId) as $w) {
                    $this->notifModel->create($w['user_id'], "Event '{$event['title']}' is full. Sorry!", "index.php?page=events");
                }
            }
            setFlash('success', 'Status updated!');
        }
        header('Location: index.php?page=events&action=show&id='.$eventId); exit;
    }


    public function lobby(): void
    {
        requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $ticket = $this->ticketModel->getLobbyData($id, $_SESSION['user_id']);
        if (!$ticket || $ticket['status'] !== 'confirmed') {
            setFlash('danger', 'Access denied.');
            header('Location: index.php?page=events'); exit;
        }
        $this->view('events/lobby', ['lobby' => $ticket, 'eventId' => $id]);
    }
}