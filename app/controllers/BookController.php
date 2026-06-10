<?php

require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Store.php';
require_once __DIR__ . '/../models/Hold.php';

class BookController extends Controller
{
    private Book  $bookModel;
    private Store $storeModel;

    public function __construct()
    {
        $this->bookModel  = new Book();
        $this->storeModel = new Store();
    }

    public function index(): void
    {
        $preferredStoreId = 0;

        if (currentRole() === 'BOOKSTORE_OWNER') {
            require_once BASE_PATH . '/app/models/Store.php';
            $storeModel = new Store();
            $myStore = $storeModel->getByOwner(currentUserId());
            if ($myStore) {
                $preferredStoreId = (int)$myStore['id'];
            }
        }

        $books = $this->bookModel->getAll($preferredStoreId);
        $this->view('books/index', ['books' => $books]);
    }

    public function ajaxSearch(): void
    {
        $query = trim($_GET['q'] ?? '');
        if (strlen($query) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }
        $results = $this->bookModel->search($query);
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    public function show(): void
    {
        $id   = (int)($_GET['id'] ?? 0);
        $book = $this->bookModel->getById($id);

        if (!$book) {
            setFlash('danger', 'Book not found.');
            $this->redirect('index.php?page=books');
        }

        $recommended = $this->bookModel->getRecommended($book['genre'], $id);
        $holdModel   = new Hold();
        $currentUserId = $_SESSION['user_id'] ?? 0;
        $activeHold = ($currentUserId > 0) ? $holdModel->getActiveHold($currentUserId, $id) : false;

        $this->view('books/show', [
            'book'        => $book,
            'recommended' => $recommended,
            'activeHold'  => $activeHold,
        ]);
    }

    public function create(): void
    {
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']);
        $this->view('books/form', ['book' => null]);
    }

    public function store(): void
    {
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']);

        $storeId = $_SESSION['store_id'] ?? 0;
        if (!$storeId) {
            $store = $this->storeModel->getByOwner($_SESSION['user_id'] ?? 0);
            $storeId = $store ? $store['id'] : 0;
        }

        $coverUrl = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $coverUrl = $this->handleUpload($_FILES['cover_image']);
        }

        $basePrice = (float)($_POST['base_price'] ?? 0);
        $grade     = $_POST['condition_grade'] ?? 'fine';
        $multipliers = ['fine' => 1.00, 'good' => 0.85, 'fair' => 0.65];
        $finalPrice  = round($basePrice * ($multipliers[$grade] ?? 1.00), 2);

        $data = [
            ':store_id'        => $storeId,
            ':isbn'            => trim($_POST['isbn'] ?? ''),
            ':title'           => trim($_POST['title'] ?? ''),
            ':author_name'     => trim($_POST['author_name'] ?? ''),
            ':genre'           => trim($_POST['genre'] ?? ''),
            ':base_price'      => $basePrice,
            ':final_price'     => $finalPrice,
            ':condition_grade' => $grade,
            ':stock_qty'       => (int)($_POST['stock_qty'] ?? 1),
            ':cover_url'       => $coverUrl,
            ':description'     => trim($_POST['description'] ?? ''),
        ];

        $bookId = $this->bookModel->create($data);
        
        if ($bookId) {
            setFlash('success', 'Book added successfully!');
            $this->redirect('index.php?page=books&action=show&id=' . $bookId);
        } else {
            setFlash('danger', 'Failed to add book.');
            $this->redirect('index.php?page=books&action=create');
        }
    }

    public function edit(): void
    {
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']);
        $id = (int)($_GET['id'] ?? 0);
        $book = $this->bookModel->getById($id);
        
        if (!$book) {
            setFlash('danger', 'Book not found.');
            $this->redirect('index.php?page=dashboard');
        }
        if (currentRole() !== 'SYSTEM_ADMIN') {
            $store = $this->storeModel->getByOwner(currentUserId());
            $storeId = $store ? $store['id'] : 0;
            if ($book['store_id'] != $storeId) {
                setFlash('danger', 'Access Denied: You can only edit your own books.');
                $this->redirect('index.php?page=books');
            }
        }

        $this->view('books/form', ['book' => $book]);
    }

    public function update(): void
    {
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? $_POST['book_id'] ?? 0);
            $book = $this->bookModel->getById($id);
            
            if (!$book) {
                setFlash('danger', 'Book not found.');
                $this->redirect('index.php?page=books');
            }

            // Security check
            if (currentRole() !== 'SYSTEM_ADMIN') {
                $store = $this->storeModel->getByOwner(currentUserId());
                $storeId = $store ? $store['id'] : 0;
                if ($book['store_id'] != $storeId) {
                    setFlash('danger', 'Access Denied: You can only update your own books.');
                    $this->redirect('index.php?page=books');
                }
            }
            
            $coverUrl = $book['cover_url'] ?? null;
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $coverUrl = $this->handleUpload($_FILES['cover_image']);
            }

            $basePrice = (float)($_POST['base_price'] ?? 0);
            $grade     = $_POST['condition_grade'] ?? 'fine';
            $multipliers = ['fine' => 1.00, 'good' => 0.85, 'fair' => 0.65];
            $finalPrice  = round($basePrice * ($multipliers[$grade] ?? 1.00), 2);

            $data = [
                ':id'              => $id,
                ':isbn'            => trim($_POST['isbn'] ?? ''),
                ':title'           => trim($_POST['title'] ?? ''),
                ':author_name'     => trim($_POST['author_name'] ?? ''),
                ':genre'           => trim($_POST['genre'] ?? ''),
                'base_price'      => (float)$_POST['base_price'], // التأكد من وجودها
    'final_price'     => (float)$_POST['base_price'],
                ':condition_grade' => $grade,
                ':stock_qty'       => (int)($_POST['stock_qty'] ?? 1),
                ':description'     => trim($_POST['description'] ?? ''),
                ':cover_url'       => $coverUrl
            ];

            if ($this->bookModel->update($data)) {
                setFlash('success', 'Book updated successfully!');
                $this->redirect("index.php?page=books&action=show&id=$id");
            } else {
                setFlash('danger', 'Failed to update book.');
                $this->redirect("index.php?page=books&action=edit&id=$id");
            }
        }
    }
    
    public function uploadCover(): void
    {
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bookId = (int)$_POST['book_id'];
            $book = $this->bookModel->getById($bookId); 

            // Security check
            if ($book && currentRole() !== 'SYSTEM_ADMIN') {
                $store = $this->storeModel->getByOwner(currentUserId());
                $storeId = $store ? $store['id'] : 0;
                if ($book['store_id'] != $storeId) {
                    setFlash('danger', 'Access Denied: You do not own this book.');
                    $this->redirect('index.php?page=books');
                }
            }

            if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                $coverUrl = $this->handleUpload($_FILES['cover']);
                if ($coverUrl) {
                    $this->bookModel->updateCover($bookId, $coverUrl);
                    setFlash('success', 'Cover image updated!');
                }
            }
            $this->redirect("index.php?page=books&action=show&id=$bookId");
        }
    }

    private function handleUpload($file): ?string {
        $path = 'public/uploads/covers/';
        $absolutePath = BASE_PATH . '/' . $path;
        if (!is_dir($absolutePath)) mkdir($absolutePath, 0777, true);

        $name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file['name']);
        if (move_uploaded_file($file['tmp_name'], $absolutePath . $name)) {
            return $path . $name;
        }
        return null;
    }

    public function toggleStaffPick(): void 
    { 
        requireRole(['SYSTEM_ADMIN']);
        $id = (int)($_GET['id'] ?? 0);
        $this->bookModel->toggleStaffPick($id);
        $this->redirect("index.php?page=books&action=show&id=$id");
    }

    public function delete(): void
    {
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $book = $this->bookModel->getById($id);
            
            if (!$book) {
                setFlash('danger', 'Book not found.');
                $this->redirect('index.php?page=books');
            }

            // Security check
            if (currentRole() !== 'SYSTEM_ADMIN') {
                $store = $this->storeModel->getByOwner(currentUserId());
                $storeId = $store ? $store['id'] : 0;
                
                if ($book['store_id'] != $storeId) {
                    setFlash('danger', 'Access Denied: You can only delete your own books.');
                    $this->redirect('index.php?page=books');
                }
            }

            if ($this->bookModel->delete($id)) {
                setFlash('success', 'Book deleted successfully!');
            } else {
                setFlash('danger', 'Failed to delete book. It might be linked to existing orders.');
            }
            
            $this->redirect('index.php?page=books');
        }
    }
    
    public function placeHold(): void
    {
        if (currentRole() !== 'READER') {
            setFlash('danger', 'Access Denied: Only readers can place a hold.');
            $this->redirect('index.php?page=books');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bookId = (int)($_POST['book_id'] ?? 0);
            $userId = (int)currentUserId(); 

            if ($bookId > 0 && $userId > 0) {
                $book = $this->bookModel->getById($bookId);
                
                if (!$book) {
                    setFlash('danger', 'Book not found.');
                    $this->redirect('index.php?page=books');
                }

                if ((int)$book['stock_qty'] <= 0) {
                    setFlash('warning', 'Sorry, this book is currently out of stock.');
                    $this->redirect("index.php?page=books&action=show&id=$bookId");
                    return; 
                }

                require_once BASE_PATH . '/app/models/Hold.php';
                $holdModel = new Hold();
                
                $activeHold = $holdModel->getActiveHold($userId, $bookId);
                
                if ($activeHold) {
                    setFlash('warning', 'You already have an active hold on this book.');
                } else {
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    
                    if ($holdModel->create($userId, $bookId, $expiresAt)) {
                        $newStock = (int)$book['stock_qty'] - 1;
                        
                        $this->bookModel->update([
                            ':id'              => $book['id'],
                            ':isbn'            => $book['isbn'],
                            ':title'           => $book['title'],
                            ':author_name'     => $book['author_name'],
                            ':genre'           => $book['genre'],
                            ':base_price'      => $book['base_price'],
                            ':final_price'     => $book['final_price'],
                            ':condition_grade' => $book['condition_grade'],
                            ':stock_qty'       => $newStock, 
                            ':description'     => $book['description'],
                            ':cover_url'       => $book['cover_url']
                        ]);

                        setFlash('success', 'Book placed on hold successfully for 24 hours!');
                    } else {
                        setFlash('danger', 'Failed to place hold. Please try again.');
                    }
                }
            }
            $this->redirect("index.php?page=books&action=show&id=$bookId");
        }
    }

    // ── applyGrade() ────────────────────────────────────────────────────────
    // Handle the small form on the show page to update condition and price
    public function applyGrade(): void
    {
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bookId = (int)($_POST['book_id'] ?? 0);
            $grade  = $_POST['condition_grade'] ?? 'fine';

            // 1. Fetch the book
            $book = $this->bookModel->getById($bookId);

            if (!$book) {
                setFlash('danger', 'Book not found.');
                $this->redirect('index.php?page=books');
            }

            // 2. Security Check: Only the owner or admin can edit
            if (currentRole() !== 'SYSTEM_ADMIN') {
                $store = $this->storeModel->getByOwner(currentUserId());
                $storeId = $store ? $store['id'] : 0;
                if ($book['store_id'] != $storeId) {
                    setFlash('danger', 'Access Denied: You do not own this book.');
                    $this->redirect("index.php?page=books&action=show&id=$bookId");
                }
            }

            // 3. Recalculate Final Price based on 3 grades
            $multipliers = ['fine' => 1.00, 'good' => 0.85, 'fair' => 0.65];
            $multiplier  = $multipliers[$grade] ?? 1.00;
            $finalPrice  = round((float)$book['base_price'] * $multiplier, 2);

            // 4. Update Database
            $data = [
                ':id'              => $book['id'],
                ':isbn'            => $book['isbn'],
                ':title'           => $book['title'],
                ':author_name'     => $book['author_name'],
                ':genre'           => $book['genre'],
               'base_price'      => (float)$_POST['base_price'], // التأكد من وجودها
    'final_price'     => (float)$_POST['base_price'],
                ':condition_grade' => $grade,
                ':stock_qty'       => $book['stock_qty'],
                ':description'     => $book['description'],
                ':cover_url'       => $book['cover_url']
            ];

            if ($this->bookModel->update($data)) {
                setFlash('success', 'Book condition updated to ' . ucfirst($grade));
            } else {
                setFlash('danger', 'Failed to update grade.');
            }

            // 5. Redirect back to the book details page
            $this->redirect("index.php?page=books&action=show&id=$bookId");
        }
    }
}