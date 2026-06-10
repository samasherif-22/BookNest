<?php


require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Club.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Store.php';
require_once __DIR__ . '/../models/User.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $bookModel  = new Book();
        // Latest 8 books for the homepage
        $allBooks   = array_slice($bookModel->getAll(), 0, 8); 

        $this->view('home/index', [
            'allBooks'   => $allBooks,
        ]);
    }

    public function dashboard(): void
    {
        requireLogin();
        $role   = currentRole();
        $userId = currentUserId();

        $data = ['role' => $role];

        switch ($role) {
            case 'READER':
                $bookModel    = new Book();
                $notifModel   = new Notification();
                $userModel    = new User();

                $data['notifications'] = $notifModel->getByUser($userId);
                
                // Fetch users for Quick Lend feature
                $allUsers = $userModel->getAll();
                
                // Filter to get only other READERS
                $data['usersList'] = array_filter($allUsers, function($u) use ($userId) {
                    return $u['id'] != $userId && strtoupper($u['role']) === 'READER';
                });
                
                // Fetch books purchased by user
                if (method_exists($bookModel, 'getPurchasedByUser')) {
                    $data['booksList'] = $bookModel->getPurchasedByUser($userId);
                } else {
                    $data['booksList'] = $bookModel->getAll(); 
                }
                break;

            case 'BOOKSTORE_OWNER':
                $storeModel  = new Store();
                $orderModel  = new Order();
                $bookModel   = new Book();
                require_once __DIR__ . '/../models/PayoutLedger.php';
                $ledgerModel = new PayoutLedger();

                $store   = $storeModel->getByOwner($userId);
                $storeId = $store ? $store['id'] : 0;

                $data['store']       = $store;
                $data['orders']      = $storeId ? $orderModel->getByStore($storeId) : [];
                $data['books']       = $storeId ? $bookModel->getByStore($storeId)  : [];
                $data['pendingPayout'] = $storeId ? $ledgerModel->getPendingTotal($storeId) : 0;
                $data['payouts']     = $storeId ? $ledgerModel->getByStore($storeId) : [];
                break;

            case 'CLUB_ORGANIZER':
                $clubModel = new Club();
                $data['clubs'] = $clubModel->getByOrganizer($userId);
                break;

            case 'AUTHOR':
                $eventModel = new Event();
                require_once __DIR__ . '/../models/Notification.php';
                $data['events']        = $eventModel->getByOrganizer($userId);
                $data['notifications'] = (new Notification())->getByUser($userId);
                break;

            case 'SYSTEM_ADMIN':
                // Main stats for Admin Dashboard
                $data['userCount']   = (new User())->count();
                $data['bookCount']   = (new Book())->count();
                $data['orderCount']  = (new Order())->count();
                $data['clubCount']   = (new Club())->count(); 
                $data['eventCount']  = (new Event())->count(); 
                $data['storeCount']  = (new Store())->count();
                
                require_once __DIR__ . '/../models/Dispute.php';
                require_once __DIR__ . '/../models/StoreApplication.php';
                $data['openDisputes']      = (new Dispute())->getOpen();
                $data['pendingStores']     = (new StoreApplication())->getPending();
                break;
        }

        $viewMap = [
            'READER'          => 'dashboards/reader',
            'BOOKSTORE_OWNER' => 'dashboards/owner',
            'CLUB_ORGANIZER'  => 'dashboards/organizer',
            'AUTHOR'          => 'dashboards/author',
            'SYSTEM_ADMIN'    => 'dashboards/admin',
        ];

        $view = $viewMap[$role] ?? 'dashboards/reader';
        $this->view($view, $data);
    }

    public function notifications(): void
    {
        requireLogin();
        $notifModel    = new Notification();
        $notifications = $notifModel->getByUser(currentUserId());

        $notifModel->markAllRead(currentUserId());

        $this->view('notifications/index', ['notifications' => $notifications]);
    }
}