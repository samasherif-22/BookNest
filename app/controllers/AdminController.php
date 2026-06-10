<?php


require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Store.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Dispute.php';
require_once __DIR__ . '/../models/StoreApplication.php';
require_once __DIR__ . '/../models/Notification.php';

class AdminController extends Controller
{
    
    // Route to the correct admin section (default: user list).
    public function index(): void
    {
        requireRole(['SYSTEM_ADMIN']);
        $this->redirect(BASE_URL . 'index.php?page=admin&action=users');
    }

   
    // List all users; support a search query.
    public function users(): void
    {
        requireRole(['SYSTEM_ADMIN']);
        $userModel = new User();
        $query     = trim($_GET['q'] ?? '');
        $users     = $query ? $userModel->search($query) : $userModel->getAll();
        $this->view('admin/users', ['users' => $users, 'query' => $query]);
    }

    
    // Admin changes a user's role. Cannot change own role.
    public function changeRole(): void
    {
        requireRole(['SYSTEM_ADMIN']);
        $userId    = (int)($_POST['user_id'] ?? 0);
        $newRole   =       $_POST['role']    ?? '';
        $allowedRoles = ['READER','BOOKSTORE_OWNER','CLUB_ORGANIZER','AUTHOR','SYSTEM_ADMIN'];

        if ($userId === currentUserId()) {
            setFlash('danger', 'You cannot change your own role.');
            $this->redirect(BASE_URL . 'index.php?page=admin&action=users');
        }

        if (!in_array($newRole, $allowedRoles)) {
            setFlash('danger', 'Invalid role.');
            $this->redirect(BASE_URL . 'index.php?page=admin&action=users');
        }

        (new User())->updateRole($userId, $newRole);
        logAction('CHANGE_ROLE', 'users', $userId, "New role: {$newRole}");
        setFlash('success', "User role updated to {$newRole}.");
        $this->redirect(BASE_URL . 'index.php?page=admin&action=users');
    }
        
    
    //  Let a BOOKSTORE_OWNER apply to open a new store.
    public function applyStore(): void
    {
        // Allow Bookstore Owners and Admins to access this page
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']); 

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Map the input data to the array keys expected by the model
            $data = [
                ':user_id'     => currentUserId(),
                ':store_name'  => sanitize($_POST['store_name'] ?? ''),
                ':description' => sanitize($_POST['description'] ?? ''),
                ':city'        => sanitize($_POST['city'] ?? '')
            ];

            $appModel = new StoreApplication();
            
            if ($appModel->create($data)) {
                
                // --- Notification Logic: Send alert to all System Admins ---
                $db = Database::getInstance()->getConnection();
                $stmt = $db->query("SELECT id FROM users WHERE role = 'SYSTEM_ADMIN'");
                $admins = $stmt->fetchAll();
                
                $notifModel = new Notification();
                foreach ($admins as $admin) {
                    $msg = "New store application submitted by " . sanitize($_SESSION['name'] ?? 'a user') . ".";
                    $link = BASE_URL . "index.php?page=admin&action=stores"; // Link to the stores approval page
                    $notifModel->create($admin['id'], $msg, $link);
                }
                // --------------------------------------------------------------

                setFlash('success', 'Your store application has been submitted and is pending admin approval.');
                // Redirect to dashboard on success
                $this->redirect(BASE_URL . 'index.php?page=dashboard'); 
            } else {
                setFlash('danger', 'Error submitting application.');
                $this->redirect(BASE_URL . 'index.php?page=admin&action=applyStore');
            }
        } else {
            // Load the application form for GET requests
            $this->view('admin/apply_store'); 
        }
    }


    // Admin sees all stores and approves / rejects pending ones.
    public function stores(): void
    {
        requireRole(['SYSTEM_ADMIN']);
        $storeModel = new Store();
        $appModel   = new StoreApplication(); // Added the application model
        
        $stores  = $storeModel->getAll();
        $pending = $appModel->getPending(); // Fetch pending applications properly
        
        $this->view('admin/stores', ['stores' => $stores, 'pending' => $pending]);
    }


    // Approve or reject a store application.
    public function approveStore(): void
    {
        requireRole(['SYSTEM_ADMIN']);
        $applicationId = (int)($_POST['store_id'] ?? 0);
        $status        =      $_POST['status']   ?? 'approved';

        $appModel = new StoreApplication();
        $storeModel = new Store();

        // 1. Update the application status
        $appModel->updateStatus($applicationId, $status);

        // 2. If approved, officially create the store in the 'stores' table
        if ($status === 'approved') {
            // Fetch the application data to get the name, city, and user
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM store_applications WHERE id = :id");
            $stmt->execute([':id' => $applicationId]);
            $app = $stmt->fetch();

            if ($app) {
                $storeModel->create([
                    ':owner_id' => $app['user_id'],
                    ':name'     => $app['store_name'],
                    ':city'     => $app['city'],
                    ':region'   => 'General', // Default value or fetch from request if available
                    ':is_verified' => 1
                ]);
            }
        }

        logAction('APPROVE_STORE', 'store_applications', $applicationId, "Status: {$status}");
        setFlash('success', "Store application {$status} and created officially.");
        $this->redirect(BASE_URL . 'index.php?page=admin&action=stores');
    }


    // Show combined platform reports: orders by type, payout summary.
    public function reports(): void
    {
        requireRole(['SYSTEM_ADMIN']);
        require_once __DIR__ . '/../models/PayoutLedger.php';

        $orderModel  = new Order();
        $ledgerModel = new PayoutLedger();

        $ordersByType   = $orderModel->countByType();
        $payoutSummary  = $ledgerModel->getSummary();

        $this->view('admin/reports', compact('ordersByType', 'payoutSummary'));
    }

    
    // F41: View the system-wide audit trail.
    public function auditLog(): void
    {
        requireRole(['SYSTEM_ADMIN']);
        $logs = (new AuditLog())->getRecent();
        $this->view('admin/audit_log', ['logs' => $logs]);
    }

   
    // View open disputes and resolve them.
    public function disputes(): void
    {
        requireRole(['SYSTEM_ADMIN']);
        $disputes = (new Dispute())->getOpen();
        $this->view('admin/disputes', ['disputes' => $disputes]);
    }

    public function resolveDispute(): void
    {
        requireRole(['SYSTEM_ADMIN']);
        $id         = (int)($_POST['dispute_id'] ?? 0);
        $resolution = trim($_POST['resolution'] ?? '');

        // 1. Fetch dispute details before resolving to identify the user
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT user_id, order_id FROM disputes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $disputeInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($disputeInfo) {
            // 2. Resolve the dispute in the database
            $disputeModel = new Dispute();
            if ($disputeModel->resolve($id, $resolution)) {
                
                // 3. Send notification to the user
                $notifModel = new Notification();
                $userId  = $disputeInfo['user_id'];
                $orderId = $disputeInfo['order_id'];
                $msg     = "Admin has resolved your dispute regarding Order #{$orderId}. Resolution: " . substr($resolution, 0, 50) . "...";
                $link    = BASE_URL . "index.php?page=orders&action=show&id={$orderId}";
                
                $notifModel->create($userId, $msg, $link);
                
                logAction('RESOLVE_DISPUTE', 'disputes', $id);
                setFlash('success', 'Dispute resolved and user notified successfully!');
            } else {
                setFlash('danger', 'Could not update dispute status.');
            }
        } else {
            setFlash('danger', 'Dispute not found.');
        }

        $this->redirect(BASE_URL . 'index.php?page=admin&action=disputes');
    }

    
    //  Calculate CO2 savings from local pickup vs. delivery.
    public function sustainabilityReport(): void
    {
        requireRole(['SYSTEM_ADMIN']);
        $CARBON_PER_PICKUP = 2.3; // kg of CO2 saved per local pickup order

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE type = 'pickup'");
        $stmt->execute();
        $pickupCount = (int)$stmt->fetchColumn();

        $stmt2 = $db->prepare("SELECT COUNT(*) FROM orders WHERE type = 'delivery'");
        $stmt2->execute();
        $deliveryCount = (int)$stmt2->fetchColumn();

        $carbonSaved = round($pickupCount * $CARBON_PER_PICKUP, 1);

        $this->view('admin/sustainability', [
            'pickupCount'   => $pickupCount,
            'deliveryCount' => $deliveryCount,
            'carbonSaved'   => $carbonSaved,
        ]);
    }

    
    //  Let a user toggle their reading history privacy setting.
    public function updatePrivacy(): void
    {
        requireLogin();
        $privacy = $_POST['privacy'] ?? 'PRIVATE';
        $userId  = currentUserId();

        if (!in_array($privacy, ['PUBLIC', 'PRIVATE'])) {
            setFlash('danger', 'Invalid privacy setting.');
            $this->redirect(BASE_URL . 'index.php?page=settings');
        }

        (new User())->updatePrivacy($userId, $privacy);
        $_SESSION['privacy'] = $privacy;

        logAction('UPDATE_PRIVACY', 'users', $userId, "Privacy: {$privacy}");
        setFlash('success', "Reading history privacy set to: {$privacy}");
        $this->redirect(BASE_URL . 'index.php?page=settings');
    }

    
    // User settings page for privacy controls.
    public function settings(): void
    {
        requireLogin();
        $user = (new User())->getById(currentUserId());
        $this->view('admin/settings', ['user' => $user]);
    }

    
    //  Export all personal data to a downloadable JSON file (GDPR).
    public function exportData(): void
    {
        requireLogin();
        $userId = currentUserId();
        $db     = Database::getInstance()->getConnection();

        // Collect data from all user-related tables
        $data   = [];
        $tables = ['users', 'orders', 'read_books', 'loans', 'nominations', 'club_members'];

        foreach ($tables as $table) {
            try {
                $col  = ($table === 'users') ? 'id' : 'user_id';
                $stmt = $db->prepare("SELECT * FROM {$table} WHERE {$col} = :uid");
                $stmt->execute([':uid' => $userId]);
                $data[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Handle cases where a table might not exist or encountered an error
                $data[$table] = "Error fetching data or table does not exist.";
            }
        }

        // Automatically create the upload directory if it does not exist
        $uploadDir = __DIR__ . '/../../public/assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // true ensures parent directories are created if missing
        }

        // Build the filename and save to uploads folder
        $filename = "gdpr_export_{$userId}_" . time() . ".json";
        $filepath = $uploadDir . $filename;
        
        // Save the file
        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));

        // Log the export in a GDPR table if it exists (best effort)
        try {
            $stmt2 = $db->prepare(
                "INSERT INTO gdpr_exports (user_id, request_type, status, file_path)
                 VALUES (:uid, 'export', 'completed', :path)"
            );
            $stmt2->execute([':uid' => $userId, ':path' => $filename]);
        } catch (PDOException $e) { /* Table may not exist yet — skip */ }

        // Trigger file download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    }

  
    //  Anonymize user data (GDPR right to erasure). Preserves referential integrity.
    public function deleteAccount(): void
    {
        requireLogin();
        $userId = currentUserId();

        (new User())->anonymize($userId);

        // Log before we destroy the session
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                "INSERT INTO gdpr_exports (user_id, request_type, status)
                 VALUES (:uid, 'delete', 'completed')"
            );
            $stmt->execute([':uid' => $userId]);
        } catch (PDOException $e) { /* skip */ }

        session_destroy();
        header('Location: ' . BASE_URL . 'index.php?page=home');
        exit();
    }
}