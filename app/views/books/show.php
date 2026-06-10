<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$userRole = currentRole();
$currentUserId = currentUserId();
$currentStoreId = 0;

// Fetch store ID if the user is a bookstore owner
if ($userRole === 'BOOKSTORE_OWNER' && $currentUserId > 0) {
    require_once BASE_PATH . '/app/models/Store.php';
    $storeModel = new Store();
    $myStore = $storeModel->getByOwner($currentUserId);
    $currentStoreId = $myStore ? (int)$myStore['id'] : 0;
}

$bookStoreId = isset($book['store_id']) ? (int)$book['store_id'] : -1;
$isMyBook = ($userRole === 'BOOKSTORE_OWNER' && $currentStoreId > 0 && $bookStoreId === $currentStoreId);
$isAdmin = ($userRole === 'SYSTEM_ADMIN');
$canManageBook = ($isAdmin || $isMyBook); 
?>

<div class="container py-4">
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-white p-3 shadow-sm border rounded-pill" style="--bs-breadcrumb-divider: '›';">
      <li class="breadcrumb-item">
        <a href="<?= BASE_URL ?>index.php?page=books" class="text-decoration-none d-inline-flex align-items-center fw-bold text-primary">
          <i class="bi bi-grid-fill me-2"></i> Books
        </a>
      </li>
      <li class="breadcrumb-item active d-inline-flex align-items-center text-secondary fw-semibold" aria-current="page">
        <i class="bi bi-book-half me-2"></i> <?= sanitize($book['title']) ?>
      </li>
    </ol>
  </nav>

  <div class="row g-4">
    <div class="col-md-4 col-lg-3">
      <div class="text-center mb-3">
        <?php if (!empty($book['cover_url'])): ?>
          <img src="<?= BASE_URL . sanitize($book['cover_url']) ?>"
               class="img-fluid rounded shadow" alt="<?= sanitize($book['title']) ?>"
               style="max-height:320px; object-fit:cover;">
        <?php else: ?>
          <div class="cover-placeholder rounded shadow" style="height:280px; max-width:200px; margin:auto;">
            <i class="bi bi-book-half"></i>
          </div>
        <?php endif; ?>
      </div>

      <div class="text-center mb-3">
        <span class="badge <?= conditionBadgeClass($book['condition_grade']) ?> px-3 py-2" style="font-size:.85rem;">
          Condition: <?= strtoupper(sanitize($book['condition_grade'])) ?>
        </span>
      </div>

      <?php if (currentRole() === 'READER'): ?>
        <?php if ((int)$book['stock_qty'] > 0): ?>
        <form method="POST" action="<?= BASE_URL ?>index.php?page=orders&action=addToCart" class="d-grid mb-2">
          <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-cart-plus me-2"></i>Add to Cart
          </button>
        </form>

        <?php if ($activeHold): ?>
          <div class="alert alert-warning text-center py-2 small">
            <i class="bi bi-clock me-1"></i>
            Hold active until <?= date('H:i d M', strtotime($activeHold['expires_at'])) ?>
          </div>
        <?php else: ?>
        <form method="POST" action="<?= BASE_URL ?>index.php?page=books&action=placeHold" class="d-grid">
          <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
          <button type="submit" class="btn btn-outline-primary">
            <i class="bi bi-bookmark me-2"></i>Hold for 24h (Free)
          </button>
        </form>
        <?php endif; ?>
        <?php else: ?>
          <div class="alert alert-secondary text-center py-2"><i class="bi bi-x-circle me-1"></i>Out of Stock</div>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($canManageBook): ?> 
        <hr>
        <div class="d-grid gap-2">
          <a href="<?= BASE_URL ?>index.php?page=books&action=edit&id=<?= (int)$book['id'] ?>"
             class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit Book
          </a>

          <form method="POST" action="<?= BASE_URL ?>index.php?page=books&action=applyGrade">
            <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
            <div class="input-group input-group-sm">
              <select name="condition_grade" class="form-select">
                <?php foreach (['fine','good','fair'] as $g): ?>
                  <option value="<?= $g ?>" <?= $book['condition_grade'] === $g ? 'selected' : '' ?>>
                    <?= ucfirst($g) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-sm btn-outline-info">Apply Grade</button>
            </div>
          </form>

          <form method="POST" action="<?= BASE_URL ?>index.php?page=books&action=uploadCover"
                enctype="multipart/form-data">
            <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
            <label class="form-label small mb-1">Upload Cover:</label>
            <input type="file" name="cover" id="cover-upload" class="form-control form-control-sm mb-1"
                   accept="image/jpeg,image/png,image/webp">
            <img id="cover-preview" src="" class="img-fluid mb-1 d-none rounded" style="max-height:80px;">
            <button type="submit" class="btn btn-sm btn-outline-primary w-100">Upload</button>
          </form>

          <form method="POST" action="<?= BASE_URL ?>index.php?page=books&action=delete" 
                onsubmit="return confirm('Are you sure you want to delete this book? This action cannot be undone!');">
            <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
              <i class="bi bi-trash me-1"></i>Delete Book
            </button>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <div class="col-md-8 col-lg-9">
      <h1 class="fw-bold mb-1" style="color:var(--primary);"><?= sanitize($book['title']) ?></h1>
      <p class="text-muted mb-3" style="font-size:1.05rem;">
        by <strong><?= sanitize($book['author_name']) ?></strong>
      </p>

      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="stat-card text-center p-3">
            <div class="stat-number text-success">EGP <?= number_format($book['final_price'], 2) ?></div>
            <div class="stat-label">Price</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card text-center p-3">
            <div class="stat-number"><?= (int)$book['stock_qty'] ?></div>
            <div class="stat-label">In Stock</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card text-center p-3">
            <div class="stat-number text-info" style="font-size:1.25rem;">
              <?= sanitize($book['genre'] ?? '—') ?>
            </div>
            <div class="stat-label">Genre</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card text-center p-3">
            <div class="stat-number text-muted" style="font-size:1.1rem;">
              <?= sanitize($book['isbn'] ?? '—') ?>
            </div>
            <div class="stat-label">ISBN</div>
          </div>
        </div>
      </div>

      <?php if (!empty($book['store_name'])): ?>
      <p class="mb-3">
        <i class="bi bi-shop me-2 text-accent"></i>
        Sold by: <strong><?= sanitize($book['store_name']) ?></strong>
      </p>
      <?php endif; ?>

      <div class="card border-0 shadow-sm p-4 mb-4" style="background:#fff;">
        <h5 class="fw-bold mb-3">About This Book</h5>
        <p class="text-muted mb-0" style="line-height:1.75;">
          <?= nl2br(sanitize($book['description'] ?? 'No description available.')) ?>
        </p>
      </div>

      <?php if (!empty($recommended)): ?>
      <h5 class="fw-bold mb-3">You Might Also Like</h5>
      <div class="row g-3">
        <?php foreach ($recommended as $rec): ?>
        <div class="col-6 col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <?php if (!empty($rec['cover_url'])): ?>
              <img src="<?= BASE_URL . sanitize($rec['cover_url']) ?>"
                   class="card-img-top" style="height:120px; object-fit:cover;" alt="">
            <?php else: ?>
              <div class="cover-placeholder" style="height:120px;"><i class="bi bi-book"></i></div>
            <?php endif; ?>
            <div class="card-body p-2">
              <h6 class="mb-1" style="font-size:.8rem;"><?= sanitize($rec['title']) ?></h6>
              <span class="badge bg-success" style="font-size:.7rem;">EGP <?= number_format($rec['final_price'],2) ?></span>
              <div class="mt-1">
                <a href="<?= BASE_URL ?>index.php?page=books&action=show&id=<?= (int)$rec['id'] ?>"
                   class="btn btn-outline-primary btn-sm w-100" style="font-size:.75rem;">View</a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div></div></div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>