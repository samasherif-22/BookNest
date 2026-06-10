<?php


require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller
{
    private User $userModel;
    private Store $storeModel;

    public function __construct()
    {
        $this->userModel = new User();
        require_once __DIR__ . '/../models/Store.php'; 
    $this->storeModel = new Store();
    }

   
    //default action redirects to login
   
    public function index(): void
    {
     $this->login();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // login() — show the login form (GET) or process credentials (POST)
    // ─────────────────────────────────────────────────────────────────────────
   // app/controllers/AuthController.php
public function login(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? ''); 

        $user = $this->userModel->getByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
           
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['name'];

           
            if ($user['role'] === 'BOOKSTORE_OWNER') {
                
                $store = $this->storeModel->getByOwner($user['id']);
                if ($store) {
                    $_SESSION['store_id'] = $store['id'];
                } else {
                    $_SESSION['store_id'] = null;
                }
            }

            setFlash('success', "Welcome back, " . $user['name']);
            header("Location: index.php?page=home");
            exit();
        } else {
            setFlash('danger', 'Invalid email or password.');
            header("Location: index.php?page=login");
            exit();
        }
    } else {
        $this->view('auth/login');
    }
}
    
    // register() 
   
    public function register(): void
    {
        // If the user is already logged in redirect them to the dashboard.
        if (isLoggedIn()) {
            $this->redirect(BASE_URL . 'index.php?page=dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {   // If the form is submitted (user clicked Register), run the code inside
            $name     = trim($_POST['name']     ?? '');
            $email    = trim($_POST['email']    ?? '');
            $password =       $_POST['password'] ?? '';
            $role     =       $_POST['role']     ?? 'READER';
            // Only allow valid roles, otherwise set role to READER 
            $allowedRoles = ['READER', 'BOOKSTORE_OWNER', 'CLUB_ORGANIZER', 'AUTHOR']; 
            if (!in_array($role, $allowedRoles)) {
                $role = 'READER';
            }

            // Validate required fields
            if (empty($name) || empty($email) || empty($password)) {
                setFlash('danger', 'All fields are required.');
                $this->view('auth/register');
                return;
            }

            // Hash the password 
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            //save user data in database
            try {
                $this->userModel->create([    
                    ':name'          => $name,
                    ':email'         => $email,
                    ':password_hash' => $hashedPassword,
                    ':role'          => $role,
                ]);
                logAction('REGISTER', 'users', null, "New user: {$email}");
                setFlash('success', 'Account created! Please log in.');
                $this->redirect(BASE_URL . 'index.php?page=login');
            } catch (PDOException $e) {
                setFlash('danger', 'Email is already registered. Please log in.');
                $this->view('auth/register');
            }
        } else {
            $this->view('auth/register');
        }
    }


    // logout() — destroy the session and redirect to homepage
   
    public function logout(): void
    {
        session_destroy();
        header('Location: ' . BASE_URL . 'index.php?page=home');
        exit();
    }
}
