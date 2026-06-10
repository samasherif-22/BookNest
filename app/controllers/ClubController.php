<?php


require_once __DIR__ . '/../models/Club.php';
require_once __DIR__ . '/../models/ReadingGoal.php';
require_once __DIR__ . '/../models/Discussion.php';
require_once __DIR__ . '/../models/Nomination.php';
require_once __DIR__ . '/../models/Vote.php';
require_once __DIR__ . '/../models/Loan.php';
require_once __DIR__ . '/../models/Notification.php';

class ClubController extends Controller
{
    private Club         $clubModel;
    private Notification $notifModel;

    public function __construct()
    {
        $this->clubModel  = new Club();
        $this->notifModel = new Notification();
    }

    /**
     * Display all public book clubs
     */
    public function index(): void {
        $clubs = $this->clubModel->getAll(); 
        $this->view('clubs/index', ['clubs' => $clubs, 'title' => 'Explore Book Clubs']);
    }

    /**
     * Display clubs joined by the current user
     */
    public function myClubs(): void {
        requireLogin();
        $userId = currentUserId();
        
        $clubs = $this->clubModel->getJoinedClubs($userId); 
        
        $this->view('clubs/index', [
            'clubs' => $clubs, 
            'title' => 'My Joined Clubs'
        ]);
    }

    /**
     * Display single club details, goals, and library
     */
    public function show(): void
    {
        requireLogin();
        $clubId = (int)($_GET['id'] ?? 0);
        $club   = $this->clubModel->getById($clubId);

        if (!$club) {
            setFlash('danger', 'Club not found.');
            $this->redirect(BASE_URL . 'index.php?page=clubs');
            return;
        }

        $goalModel       = new ReadingGoal();
        $discussionModel = new Discussion();
        $nominationModel = new Nomination();

        $goals       = $goalModel->getByClub($clubId);
        $discussions = $discussionModel->getByClub($clubId);
        $nominations = $nominationModel->getByClub($clubId);
        $members     = $this->clubModel->getMembers($clubId);
        $isMember    = $this->clubModel->isMember($clubId, currentUserId());

        foreach ($discussions as &$disc) {
            $disc['accessible'] = $this->canAccessDiscussion($disc, currentUserId());
        }

        $db = Database::getInstance()->getConnection();
        
        // Fetch Q&A section data
        $stmt = $db->prepare("
            SELECT q.question, a.answer, u.name as asker_name, auth.name as author_name
            FROM qa_questions q
            JOIN users u ON q.user_id = u.id
            JOIN qa_answers a ON q.id = a.question_id
            JOIN users auth ON a.author_id = auth.id
            WHERE q.club_id = :cid
            ORDER BY a.id DESC
        ");
        $stmt->execute([':cid' => $clubId]);
        $qaList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Club Library (Books owned by other members)
        $stmtLib = $db->prepare("
            SELECT DISTINCT b.id as book_id, b.title, u.id as owner_id, u.name as owner_name
            FROM club_members cm
            JOIN orders o ON o.user_id = cm.user_id
            JOIN order_items oi ON o.id = oi.order_id AND oi.item_type = 'book'
            JOIN books b ON b.id = oi.item_id
            JOIN users u ON u.id = cm.user_id
            WHERE cm.club_id = :cid AND cm.user_id != :uid
        ");
        $stmtLib->execute([':cid' => $clubId, ':uid' => currentUserId()]);
        $clubLibrary = $stmtLib->fetchAll(PDO::FETCH_ASSOC);

        $this->view('clubs/show', compact(
            'club', 'goals', 'discussions', 'nominations', 'members', 'isMember', 'qaList', 'clubLibrary'
        ));
    }

    public function create(): void
    {
        requireRole(['CLUB_ORGANIZER', 'SYSTEM_ADMIN']);
        $this->view('clubs/form', ['club' => null]);
    }

    public function store(): void
    {
        requireRole(['CLUB_ORGANIZER', 'SYSTEM_ADMIN']);
        $this->clubModel->create([
            ':organizer_id' => currentUserId(),
            ':name'         => trim($_POST['name']         ?? ''),
            ':description'  => trim($_POST['description'] ?? ''),
            ':genre'        => trim($_POST['genre']       ?? ''),
            ':is_private'   => isset($_POST['is_private']) ? 1 : 0,
        ]);
        $db     = Database::getInstance()->getConnection();
        $clubId = $db->lastInsertId();
        logAction('CREATE_CLUB', 'clubs', (int)$clubId);
        setFlash('success', 'Club created!');
        $this->redirect(BASE_URL . 'index.php?page=clubs&action=show&id=' . $clubId);
    }

    /**
     * Handle public join or private join request
     */
    public function joinClub(): void
    {
        requireLogin();
        $clubId = (int)($_POST['club_id'] ?? 0);
        $userId = currentUserId();
        $club   = $this->clubModel->getById($clubId);

        if (!$club) {
            setFlash('danger', 'Club not found.');
            $this->redirect(BASE_URL . 'index.php?page=clubs');
            return;
        }

        if ($club['is_private']) {
            $db = Database::getInstance()->getConnection();
            $this->clubModel->deleteJoinRequest($clubId, $userId);

            try {
                $stmt = $db->prepare("INSERT INTO join_requests (club_id, user_id, status) VALUES (:cid, :uid, 'pending')");
                $stmt->execute([':cid' => $clubId, ':uid' => $userId]);

                $this->notifModel->create(
                    $club['organizer_id'],
                    "New join request for your club: {$club['name']}",
                    BASE_URL . "index.php?page=clubs&action=requests&id={$clubId}"
                );
                setFlash('info', 'Join request sent. Waiting for organizer approval.');
            } catch (PDOException $e) {
                setFlash('warning', 'You already have a pending request.');
            }
        } else {
            if ($this->clubModel->addMember($clubId, $userId)) {
                setFlash('success', 'You have joined the club!');
            } else {
                setFlash('info', 'You are already a member.');
            }
        }
        $this->redirect(BASE_URL . 'index.php?page=clubs&action=show&id=' . $clubId);
    }

    public function requests(): void
    {
        requireRole(['CLUB_ORGANIZER', 'SYSTEM_ADMIN']);
        $clubId   = (int)($_GET['id'] ?? 0);
        $club     = $this->clubModel->getById($clubId);
        $requests = $this->clubModel->getPendingRequests($clubId);
        $this->view('clubs/requests', compact('club', 'requests'));
    }

    public function approveRequest(): void
    {
        requireRole(['CLUB_ORGANIZER', 'SYSTEM_ADMIN']);
        $requestId = (int)($_POST['request_id'] ?? 0);
        $clubId    = (int)($_POST['club_id']    ?? 0);
        $userId    = (int)($_POST['user_id']    ?? 0);
        $action    =       $_POST['action_type'] ?? 'approved';

        $this->clubModel->updateJoinRequest($requestId, $action);

        if ($action === 'approved') {
            $this->clubModel->addMember($clubId, $userId);
            $this->notifModel->create(
                $userId,
                "Your request to join the club has been approved!",
                BASE_URL . "index.php?page=clubs&action=show&id={$clubId}"
            );
        }

        setFlash('success', "Request {$action}.");
        $this->redirect(BASE_URL . 'index.php?page=clubs&action=requests&id=' . $clubId);
    }

    public function createGoal(): void
    {
        requireRole(['CLUB_ORGANIZER', 'SYSTEM_ADMIN']);
        $clubId  = (int)($_POST['club_id']         ?? 0);
        $chapter = (int)($_POST['target_chapter'] ?? 1);
        $dueDate =       $_POST['due_date']         ?? '';
        $label   = trim($_POST['label']           ?? '');

        $goalModel = new ReadingGoal();
        $goalId    = $goalModel->create([
            ':club_id'        => $clubId,
            ':target_chapter' => $chapter,
            ':due_date'       => $dueDate,
            ':label'          => $label,
        ]);

        logAction('CREATE_READING_GOAL', 'reading_goals', $goalId, "Club: {$clubId}");
        setFlash('success', 'Reading goal created!');
        $this->redirect(BASE_URL . 'index.php?page=clubs&action=show&id=' . $clubId);
    }

    public function updateProgress(): void
    {
        requireLogin();
        $goalId  = (int)($_POST['goal_id']         ?? 0);
        $chapter = (int)($_POST['current_chapter'] ?? 0);
        $userId  = currentUserId();

        $goalModel = new ReadingGoal();
        $goalModel->upsertProgress($goalId, $userId, $chapter);
        setFlash('success', 'Progress updated!');
        
        $referer = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . 'index.php?page=dashboard');
        $this->redirect($referer);
    }

    public function createDiscussion(): void
    {
        requireLogin();
        $clubId = (int)($_POST['club_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $requiredChapter = (int)($_POST['required_chapter'] ?? 0);
        $userId = currentUserId();

        if (empty($title) || empty($body)) {
            setFlash('danger', 'Title and body are required.');
            $this->redirect(BASE_URL . 'index.php?page=clubs&action=show&id=' . $clubId);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO discussions (club_id, author_id, title, body, required_chapter) VALUES (:cid, :uid, :title, :body, :req_chap)");
        $stmt->execute([':cid' => $clubId, ':uid' => $userId, ':title' => $title, ':body' => $body, ':req_chap' => $requiredChapter]);

        setFlash('success', 'Discussion posted successfully!');
        $this->redirect(BASE_URL . 'index.php?page=clubs&action=show&id=' . $clubId);
    }

    private function canAccessDiscussion(array $discussion, int $userId): bool
    {
        if ((int)$discussion['required_chapter'] === 0) return true;
        $goalModel = new ReadingGoal();
        $myChapter = $goalModel->getUserMaxChapter($discussion['club_id'], $userId);
        return $myChapter >= (int)$discussion['required_chapter'];
    }

    public function nominate(): void
    {
        requireLogin();
        $clubId    = (int)($_POST['club_id']    ?? 0);
        $bookTitle = trim($_POST['book_title'] ?? '');
        if (empty($bookTitle)) {
            setFlash('danger', 'Please enter a book title.');
            $this->redirect(BASE_URL . 'index.php?page=clubs&action=show&id=' . $clubId);
            return;
        }
        (new Nomination())->create($clubId, currentUserId(), $bookTitle);
        setFlash('success', 'Nomination submitted!');
        $this->redirect(BASE_URL . 'index.php?page=clubs&action=show&id=' . $clubId);
    }

    public function castVote(): void
    {
        requireLogin();
        $nominationId = (int)($_POST['nomination_id'] ?? 0);
        $clubId       = (int)($_POST['club_id']       ?? 0);
        try {
            (new Nomination())->castVote($nominationId, currentUserId());
            setFlash('success', 'Vote cast!');
        } catch (PDOException $e) {
            setFlash('warning', 'You have already voted.');
        }
        $this->redirect(BASE_URL . 'index.php?page=clubs&action=show&id=' . $clubId);
    }

    public function lendBook(): void
    {
        requireLogin();
        $borrowerId = (int)($_POST['borrower_id'] ?? 0);
        $bookId     = (int)($_POST['book_id']     ?? 0);
        $dueDate    =       $_POST['due_date']     ?? '';
        $lenderId   = currentUserId();

        $loanModel = new Loan();
        $loanId    = $loanModel->create([':lender_id' => $lenderId, ':borrower_id' => $borrowerId, ':book_id' => $bookId, ':due_date' => $dueDate]);

        $lenderName = sanitize($_SESSION['name'] ?? 'A member');
        $this->notifModel->create($borrowerId, "{$lenderName} approved your borrow request!", BASE_URL . 'index.php?page=loans');
        logAction('LEND_BOOK', 'loans', $loanId);
        setFlash('success', 'Lending request sent!');
        $this->redirect(BASE_URL . 'index.php?page=loans');
    }

    public function returnBook(): void
    {
        requireLogin();
        $loanId = (int)($_POST['loan_id'] ?? 0);
        (new Loan())->markReturned($loanId);
        logAction('RETURN_BOOK', 'loans', $loanId);
        setFlash('success', 'Book marked as returned.');
        $this->redirect(BASE_URL . 'index.php?page=loans');
    }

    public function loans(): void
    {
        requireLogin();
        $userId = currentUserId();
        $loanModel = new Loan();
        $lent      = $loanModel->getLent($userId);
        $borrowed  = $loanModel->getBorrowed($userId);

        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Book.php';
        $userModel = new User();
        $bookModel = new Book();

        $allUsers = $userModel->getAll();
        $usersList = array_filter($allUsers, function($u) use ($userId) {
            return $u['id'] != $userId && strtoupper($u['role']) === 'READER'; 
        });
        
        $booksList = method_exists($bookModel, 'getPurchasedByUser') ? $bookModel->getPurchasedByUser($userId) : $bookModel->getAll();

        $this->view('clubs/loans', compact('lent', 'borrowed', 'usersList', 'booksList'));
    }

    public function askQuestion(): void
    {
        requireRole(['READER']);
        $clubId   = (int)($_POST['club_id']  ?? 0);
        $question = trim($_POST['question'] ?? '');
        if (empty($question)) {
            setFlash('danger', 'Please enter a question.');
            $this->redirect(BASE_URL . 'index.php?page=clubs&action=show&id=' . $clubId);
            return;
        }
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO qa_questions (club_id, user_id, question) VALUES (:cid, :uid, :q)");
        $stmt->execute([':cid' => $clubId, ':uid' => currentUserId(), ':q' => $question]);
        setFlash('success', 'Question submitted!');
        $this->redirect(BASE_URL . 'index.php?page=clubs&action=show&id=' . $clubId);
    }

    public function answerQuestion(): void
    {
        requireRole(['AUTHOR']);
        $questionId = (int)($_POST['question_id'] ?? 0);
        $answer     = trim($_POST['answer']       ?? '');
        if (empty($answer)) {
            setFlash('danger', 'Please write an answer.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
            return;
        }
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO qa_answers (question_id, author_id, answer) VALUES (:qid, :aid, :a)");
        $stmt->execute([':qid' => $questionId, ':aid' => currentUserId(), ':a' => $answer]);

        setFlash('success', 'Answer posted!');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
    }

    public function requestToBorrow(): void
    {
        requireLogin();
        $clubId = (int)($_POST['club_id'] ?? 0);
        $ownerId = (int)($_POST['owner_id'] ?? 0);
        $bookId = (int)($_POST['book_id'] ?? 0); 
        $bookTitle = trim($_POST['book_title'] ?? 'a book');
        
        $this->notifModel->create($ownerId, "Someone wants to borrow '{$bookTitle}'.", BASE_URL . "index.php?page=loans&prefill_borrower=".currentUserId()."&prefill_book={$bookId}");
        setFlash('success', 'Borrow request sent!');
        $this->redirect(BASE_URL . 'index.php?page=clubs&action=show&id=' . $clubId);
    }
}