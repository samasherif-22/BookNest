<?php

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Store.php';
require_once __DIR__ . '/../models/PayoutLedger.php';
require_once __DIR__ . '/../models/AuditLog.php';

class ReportController extends Controller
{
    
    // Show the reports menu.
    public function index(): void
    {
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']);
        $this->view('admin/reports', []);
    }

   
    // Printable sales report for a store: all orders with totals.
    public function salesReport(): void
    {
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']);
        $userId     = currentUserId();
        $storeModel = new Store();
        $store      = $storeModel->getByOwner($userId);
        $role       = currentRole(); 

        if (!$store && $role !== 'SYSTEM_ADMIN') {
            setFlash('danger', 'You do not have an approved store.');
            $this->redirect(BASE_URL . 'index.php?page=dashboard');
            return;
        }

        $orderModel  = new Order();
        $ledgerModel = new PayoutLedger();

   
        if ($role === 'SYSTEM_ADMIN') {
            $orders = $orderModel->getAll(); 
            $payout = []; 
            $store  = ['name' => 'System Wide Report (All Stores)']; 
        } else {
            
            $storeId = $store ? (int)$store['id'] : 0;
            $orders  = $storeId ? $orderModel->getByStore($storeId) : [];
            $payout  = $storeId ? $ledgerModel->getSummary($storeId) : []; 
        }

        $this->view('admin/reports', [
            'store'      => $store,
            'orders'     => $orders,
            'payout'     => $payout,
            'reportType' => 'sales',
        ]);
    }

    
    // Printable inventory list for a store owner.
    public function inventoryReport(): void
    {
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']);
        $userId     = currentUserId();
        $storeModel = new Store();
        $store      = $storeModel->getByOwner($userId);
        $role       = currentRole();

        $bookModel = new Book();
        
        
        if ($role === 'SYSTEM_ADMIN') {
            $books = $bookModel->getAll();
            $store = ['name' => 'System Wide Report (All Stores)'];
        } else {
          
        }

        $this->view('admin/reports', [
            'store'      => $store,
            'books'      => $books,
            'reportType' => 'inventory',
        ]);
    }
}