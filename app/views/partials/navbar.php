<?php

// Load Notification model so the bell count works on every page
require_once BASE_PATH . '/app/models/Notification.php';

// Count unread notifications if the user is logged in
$unreadCount = 0;
$cartCount = 0; // Define cart variable

if (isLoggedIn()) {
    $notifModel  = new Notification();
    $unreadCount = $notifModel->countUnread(currentUserId());
    
    // Calculate number of books in cart from session
    if (isset($_SESSION['cart'])) {
        $cartCount = count($_SESSION['cart']);
    }
}
?>
<nav class="navbar navbar-expand-lg sticky-top" style="background-color: var(--primary);" id="mainNavbar">
  <div class="container">

    <a class="navbar-brand text-white d-flex align-items-center gap-2" href="<?= BASE_URL ?>index.php?page=home">
      <i class="bi bi-book-half fs-4"></i>
      <span style="font-family:'Playfair Display',serif; font-size:1.35rem; letter-spacing:-0.5px;">BookNest</span>
    </a>

    <button class="navbar-toggler border-secondary" type="button"
            data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">

      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item">
          <a class="nav-link text-white" href="<?= BASE_URL ?>index.php?page=books">
            <i class="bi bi-bookshelf me-1"></i>Books
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link text-white" href="<?= BASE_URL ?>index.php?page=clubs">
            <i class="bi bi-people me-1"></i>Clubs
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link text-white" href="<?= BASE_URL ?>index.php?page=events">
            <i class="bi bi-calendar-event me-1"></i>Events
          </a>
        </li>

        <?php if (currentRole() === 'SYSTEM_ADMIN'): ?>
        <li class="nav-item">
          <a class="nav-link text-warning" href="<?= BASE_URL ?>index.php?page=admin&action=users">
            <i class="bi bi-shield-lock me-1"></i>Admin
          </a>
        </li>
        <?php endif; ?>

      </ul>
      <ul class="navbar-nav align-items-center">

        <?php if (isLoggedIn()): ?>

        <?php if (currentRole() === 'READER'): ?>
        <li class="nav-item me-2">
          <a class="nav-link text-white position-relative" href="<?= BASE_URL ?>index.php?page=orders&action=checkout">
            <i class="bi bi-cart3 fs-5"></i>
            <?php if ($cartCount > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    style="font-size:.6rem;">
                <?= $cartCount ?>
              </span>
            <?php endif; ?>
          </a>
        </li>
        <?php endif; ?>

        <li class="nav-item me-1">
          <a class="nav-link text-white position-relative" href="<?= BASE_URL ?>index.php?page=notifications">
            <i class="bi bi-bell fs-5"></i>
            <?php if ($unreadCount > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    style="font-size:.6rem;">
                <?= min($unreadCount, 99) ?>
              </span>
            <?php endif; ?>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-1"
             href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle fs-5"></i>
            <span class="d-none d-sm-inline"><?= sanitize($_SESSION['name'] ?? 'User') ?></span>
            <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">
              <?= sanitize(currentRole()) ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>index.php?page=dashboard">
                <i class="bi bi-speedometer2 me-2 text-accent"></i>Dashboard
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>index.php?page=settings">
                <i class="bi bi-gear me-2 text-accent"></i>Settings
              </a>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
              <a class="dropdown-item text-danger" href="<?= BASE_URL ?>index.php?page=logout">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
              </a>
            </li>
          </ul>
        </li>

        <?php else: ?>

        <li class="nav-item">
          <a class="nav-link text-white" href="<?= BASE_URL ?>index.php?page=login">
            <i class="bi bi-box-arrow-in-right me-1"></i>Login
          </a>
        </li>
        <li class="nav-item">
          <a class="btn btn-sm ms-2" style="background:var(--accent);color:#fff;border:none;"
             href="<?= BASE_URL ?>index.php?page=register">
            Register
          </a>
        </li>

        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>