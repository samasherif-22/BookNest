<?php

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderItem.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Store.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/PayoutLedger.php';


class OrderController extends Controller
{
    private Order        $orderModel;
    private OrderItem    $itemModel;
    private Notification $notifModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->itemModel  = new OrderItem();
        $this->notifModel = new Notification();
    }

   
    // Show all orders for the current user (reader) or current store (owner).
    public function index(): void
    {
        requireLogin();
        $userId = currentUserId();

        if (currentRole() === 'BOOKSTORE_OWNER') {
            $store  = (new Store())->getByOwner($userId);
            $orders = $store ? $this->orderModel->getByStore($store['id']) : [];
        } else {
            $orders = $this->orderModel->getByUser($userId);
        }

        $this->view('orders/index', ['orders' => $orders]);
    }

  
    // Show full details for a single order, including line items.
    public function show(): void
    {
        requireLogin();
        $id    = (int)($_GET['id'] ?? 0);
        $order = $this->orderModel->getById($id);
        $items = $this->itemModel->getByOrder($id);

        if (!$order) {
            setFlash('danger', 'Order not found.');
            $this->redirect(BASE_URL . 'index.php?page=orders');
        }

        $this->view('orders/show', ['order' => $order, 'items' => $items]);
    }

   
    // Show the checkout page (cart summary + order type selection).
    public function checkout(): void
    {
        requireRole(['READER']);
        $bookIds = $_SESSION['cart'] ?? [];

        if (empty($bookIds)) {
            setFlash('warning', 'Your cart is empty.');
            $this->redirect(BASE_URL . 'index.php?page=books');
        }

        $bookModel = new Book();
        $cartItems = [];
        foreach (array_unique($bookIds) as $bid) {
            $book = $bookModel->getById($bid);
            if ($book) $cartItems[] = $book;
        }

        $this->view('orders/checkout', ['cartItems' => $cartItems]);
    }


    // Add a book to the session-based cart.
    public function addToCart(): void
    {
        requireRole(['READER']);

        $bookId = (int)($_POST['book_id'] ?? 0);

        // ── Validation: make sure the book ID is a positive integer
        if ($bookId <= 0) {
            setFlash('danger', 'Invalid book selected.');
            $this->redirect(BASE_URL . 'index.php?page=books');
        }

        // ── Validation: check the book actually exists in the database
        $bookModel = new Book();
        $book      = $bookModel->getById($bookId);

        if (!$book) {
            setFlash('danger', 'Book not found. It may have been removed.');
            $this->redirect(BASE_URL . 'index.php?page=books');
        }

        // ── Validation: check the book has stock available
        if ((int)$book['stock_qty'] < 1) {
            setFlash('warning', 'Sorry, "' . sanitize($book['title']) . '" is out of stock.');
            $this->redirect(BASE_URL . 'index.php?page=books&action=show&id=' . $bookId);
        }

  
        // Check whether the cart already has items from a DIFFERENT store.
        $cartIds = $_SESSION['cart'] ?? [];

        if (!empty($cartIds)) {
            $firstBook = $bookModel->getById($cartIds[0]);

            if ($firstBook && $firstBook['store_id'] !== $book['store_id']) {
                setFlash(
                    'warning',
                    'Your cart already contains books from "' .
                    sanitize($firstBook['store_name'] ?? 'another store') .
                    '". Please complete or clear that order before adding books
                    from a different store.'
                );
                $this->redirect(BASE_URL . 'index.php?page=books&action=show&id=' . $bookId);
            }
        }

        // ── Add to cart (stored as array of book IDs in session) ─────────────
        $_SESSION['cart'][] = $bookId;
        setFlash('success', '"' . sanitize($book['title']) . '" added to cart!');
        $this->redirect(BASE_URL . 'index.php?page=orders&action=checkout');
    }


    // Process checkout: validate cart, create order + payout .
    public function placeOrder(): void
    {
        requireRole(['READER']);

        $type = $_POST['order_type'] ?? 'pickup';
        if (!in_array($type, ['pickup', 'delivery'])) {
            $type = 'pickup';
        }

        $userId  = currentUserId();
        $bookIds = $_SESSION['cart'] ?? [];

        if (empty($bookIds)) {
            setFlash('warning', 'Your cart is empty.');
            $this->redirect(BASE_URL . 'index.php?page=books');
        }

        $bookModel = new Book();
        $subtotal  = 0.0;
        $storeId   = null;
        $items     = [];

        foreach (array_unique($bookIds) as $bid) {
            $bid = (int)$bid;
            if ($bid <= 0) continue;

            $book = $bookModel->getById($bid);
            if (!$book || (int)$book['stock_qty'] < 1) continue;

            if ($storeId === null) {
                $storeId = (int)$book['store_id'];
            } elseif ((int)$book['store_id'] !== $storeId) {
                unset($_SESSION['cart']);
                setFlash('danger', 'Cart contained books from different stores. Cart cleared.');
                $this->redirect(BASE_URL . 'index.php?page=books');
            }

            $subtotal += (float)$book['final_price'];
            $items[]   = [
                'item_id'    => $bid,
                'unit_price' => (float)$book['final_price'],
                'qty'        => 1,
            ];
            $bookModel->decrementStock($bid);
        }

        if ($storeId === null || empty($items)) {
            setFlash('danger', 'All items are unavailable.');
            unset($_SESSION['cart']);
            $this->redirect(BASE_URL . 'index.php?page=books');
        }

     
        $orderId = $this->orderModel->create([
            ':user_id'  => $userId,
            ':store_id' => $storeId,
            ':subtotal' => $subtotal,
            ':total'    => $subtotal,
            ':type'     => $type,
        ]);

     
        foreach ($items as $item) {
            $this->itemModel->create([
                ':order_id'   => $orderId,
                ':item_type'  => 'book',
                ':item_id'    => $item['item_id'],
                ':qty'        => $item['qty'],
                ':unit_price' => $item['unit_price'],
            ]);
        }

      
        $this->calculatePayout($orderId);

        // Notify the bookstore owner
        $storeModel = new Store();
        $store = $storeModel->getById($storeId);
        if ($store && !empty($store['owner_id'])) {
            $this->notifModel->create(
                (int)$store['owner_id'],
                "A new order #{$orderId} has been placed.",
                BASE_URL . "index.php?page=orders&action=show&id={$orderId}"
            );
        }

        unset($_SESSION['cart']);

        logAction('PLACE_ORDER', 'orders', $orderId, "Subtotal: {$subtotal}");
        setFlash('success', "Order #{$orderId} placed successfully!");
        $this->redirect(BASE_URL . 'index.php?page=orders&action=show&id=' . $orderId);
    }


    //  Click-and-Collect — validate and apply order status transition.
    public function updateStatus(): void
    {
        requireRole(['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']);
        $orderId   = (int)($_POST['order_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';

        // Define which statuses can transition to which
        $allowedTransitions = [
            'placed'    => ['ready', 'cancelled'],
            'ready'     => ['collected'],
            'collected' => [],
            'cancelled' => [],
        ];

        $order = $this->orderModel->getById($orderId);

        if (!$order) {
            setFlash('danger', 'Order not found.');
            $this->redirect(BASE_URL . 'index.php?page=orders');
        }

        $current = $order['status'];

        // Validate the transition
        if (!isset($allowedTransitions[$current]) || !in_array($newStatus, $allowedTransitions[$current])) {
            setFlash('danger', "Cannot transition from '{$current}' to '{$newStatus}'.");
            $this->redirect(BASE_URL . 'index.php?page=orders&action=show&id=' . $orderId);
        }

        $this->orderModel->updateStatus($orderId, $newStatus);

        // Notify the customer about the status change
        $this->notifModel->create(
            $order['user_id'],
            "Your order #{$orderId} is now: {$newStatus}",
            BASE_URL . "index.php?page=orders&action=show&id={$orderId}"
        );

        logAction('UPDATE_ORDER_STATUS', 'orders', $orderId, "New status: {$newStatus}");
        setFlash('success', "Order status updated to '{$newStatus}'.");
        $this->redirect(BASE_URL . 'index.php?page=orders&action=show&id=' . $orderId);
    }

  
    // Record the platform commission (10%) and vendor net in payout_ledger.
    private function calculatePayout(int $orderId): void
    {
        $COMMISSION_RATE = 0.10; // Platform keeps 10%
        $order = $this->orderModel->getById($orderId);
        if (!$order) return;

        $gross      = (float)$order['subtotal'];
        $commission = round($gross * $COMMISSION_RATE, 2);
        $vendorNet  = round($gross - $commission, 2);

        $ledger = new PayoutLedger();
        $ledger->create([
            ':order_id'        => $orderId,
            ':store_id'        => $order['store_id'],
            ':gross_amount'    => $gross,
            ':commission_rate' => $COMMISSION_RATE,
            ':commission_amt'  => $commission,
            ':vendor_net'      => $vendorNet,
        ]);

        logAction('CREATE_PAYOUT', 'payout_ledger', null, "Order: {$orderId}, Net: {$vendorNet}");
    }


public function clearCart(): void
{
    requireRole(['READER']);
    
    if (isset($_SESSION['cart'])) {
        unset($_SESSION['cart']); 
    }
    
    setFlash('info', 'Cart cleared successfully.');
    $this->redirect(BASE_URL . 'index.php?page=books'); 
}
}