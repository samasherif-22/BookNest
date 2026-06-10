<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-4">
  <h1 class="section-title mb-1">Hello, <?= sanitize($_SESSION['name'] ?? 'Reader') ?>!</h1>
  <p class="text-muted mb-4">Here's your reading hub for today.</p>

  <div class="row g-4">

    <div class="col-12">
      <div class="card border-0 shadow-sm p-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-lightning me-2 text-accent"></i>Quick Links</h5>
        <div class="row g-3">
            <div class="col-md-3">
              <a href="<?= BASE_URL ?>index.php?page=books" class="btn btn-outline-primary btn-sm w-100">
                <i class="bi bi-bookshelf me-2"></i>Browse Books
              </a>
            </div>
            <div class="col-md-3">
              <a href="<?= BASE_URL ?>index.php?page=orders" class="btn btn-outline-primary btn-sm w-100">
                <i class="bi bi-bag me-2"></i>My Orders
              </a>
            </div>
            <div class="col-md-3">
              <a href="<?= BASE_URL ?>index.php?page=clubs&action=myClubs" class="btn btn-outline-primary btn-sm w-100">
                <i class="bi bi-people me-2"></i>My Clubs
              </a>
            </div>
            <div class="col-md-3">
              <a href="<?= BASE_URL ?>index.php?page=events" class="btn btn-outline-primary btn-sm w-100">
                <i class="bi bi-calendar-event me-2"></i>Events
              </a>
            </div>
            <div class="col-md-6">
              <a href="<?= BASE_URL ?>index.php?page=loans" class="btn btn-outline-secondary btn-sm w-100">
                <i class="bi bi-arrow-left-right me-2"></i>Lending Library
              </a>
            </div>
            <div class="col-md-6">
              <a href="<?= BASE_URL ?>index.php?page=settings" class="btn btn-outline-secondary btn-sm w-100">
                <i class="bi bi-shield-lock me-2"></i>Privacy Settings
              </a>
            </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card border-0 shadow-sm p-4 border-start border-4 border-info">
        <h5 class="fw-bold mb-3"><i class="bi bi-bookmark-heart me-2 text-info"></i>Quick Lend a Book</h5>
        <form method="POST" action="<?= BASE_URL ?>index.php?page=loans&action=lendBook" class="row g-3">
          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted mb-1">Select Member</label>
            <select name="borrower_id" class="form-select form-select-sm" required>
              <option value="" disabled selected>Who are you lending to?</option>
              <?php if (!empty($usersList)): ?>
                <?php foreach ($usersList as $user): ?>
                  <option value="<?= $user['id'] ?>"><?= sanitize($user['name']) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted mb-1">Select Your Book</label>
            <select name="book_id" class="form-select form-select-sm" required>
              <option value="" disabled selected>Which book are you lending?</option>
              <?php if (!empty($booksList)): ?>
                <?php foreach ($booksList as $book): ?>
                  <option value="<?= $book['id'] ?>"><?= sanitize($book['title']) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Return Due Date</label>
            <input type="date" name="due_date" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-info text-white btn-sm w-100 fw-bold shadow-sm">
              <i class="bi bi-send me-1"></i> Lend
            </button>
          </div>
        </form>
      </div>
    </div>

    <?php if (!empty($notifications)): ?>
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
          <span><i class="bi bi-bell me-2"></i>Recent Notifications</span>
          <a href="<?= BASE_URL ?>index.php?page=notifications" class="small text-accent">View All</a>
        </div>
        <div class="list-group list-group-flush">
          <?php foreach (array_slice($notifications, 0, 5) as $n): ?>
          <a href="<?= sanitize($n['link'] ?? '#') ?>"
             class="list-group-item list-group-item-action py-2 <?= empty($n['is_read']) ? 'fw-bold' : '' ?>">
            <div class="d-flex justify-content-between">
              <span style="font-size:.88rem;"><?= sanitize($n['message']) ?></span>
              <small class="text-muted ms-2 flex-shrink-0">
                <?= date('d M', strtotime($n['created_at'])) ?>
              </small>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>