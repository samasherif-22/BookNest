<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';


$userRole = $_SESSION['role'] ?? 'GUEST';
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;


$currentStoreId = 0;
if ($userRole === 'BOOKSTORE_OWNER' && $currentUserId > 0) {
    require_once BASE_PATH . '/app/models/Store.php';
    $storeModel = new Store();
    $myStore = $storeModel->getByOwner($currentUserId);
    $currentStoreId = $myStore ? (int)$myStore['id'] : 0;
}
?>

<div class="container py-4">
  <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
      <?= $flash['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="section-title mb-1">Browse Books</h1>
      <p class="text-muted small mb-0"><?= count($books ?? []) ?> books available in our collection</p>
    </div>
    
    <?php if (in_array($userRole, ['BOOKSTORE_OWNER', 'SYSTEM_ADMIN'])): ?>
    <a href="index.php?page=books&action=create" class="btn btn-primary shadow-sm">
      <i class="bi bi-plus-lg me-1"></i>Add New Book
    </a>
    <?php endif; ?>
  </div>

 <!--search bar-->
 <div class="search-wrapper mb-5" style="max-width:550px; position:relative;">
  <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-primary"></i></span>
    <input type="text" class="form-control border-0 ps-0 dynamic-search-input"
           placeholder="Search by title, author..." autocomplete="off">
  </div>
  <div class="dynamic-search-results list-group shadow-lg position-absolute w-100 mt-1" style="z-index: 1050;"></div>
</div>

  <?php if (empty($books)): ?>
    <div class="text-center py-5 text-muted bg-light rounded-xl">
      <i class="bi bi-book display-1 d-block mb-3 text-light"></i>
      <h4 class="fw-bold">No books found</h4>
      <p>We couldn't find any books matching your criteria.</p>
    </div>
  <?php else: ?>
  <div class="row g-4" id="books-grid">
    <?php foreach ($books as $book): ?>
    
    <?php 
       
        $bookStoreId = isset($book['store_id']) ? (int)$book['store_id'] : -1;
        $isMyBook = ($userRole === 'BOOKSTORE_OWNER' && $currentStoreId > 0 && $bookStoreId === $currentStoreId);
    ?>

    <div class="col-6 col-md-4 col-lg-3 book-item">
      <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 12px; transition: transform 0.2s;">
        
        
        <?php if ($book['is_staff_pick'] ?? false): ?>
          <div class="position-absolute top-0 start-0 bg-warning text-dark fw-bold px-3 py-1 shadow-sm small" 
               style="z-index: 10; border-bottom-right-radius: 12px; font-size: 0.7rem;">
             <i class="bi bi-star-fill me-1"></i>STAFF PICK
          </div>
        <?php endif; ?>

        
        <?php if ($isMyBook): ?>
          <div class="position-absolute top-0 end-0 bg-success text-white fw-bold px-3 py-1 shadow-sm small" 
               style="z-index: 10; border-bottom-left-radius: 12px; font-size: 0.7rem;">
             <i class="bi bi-shop me-1"></i>MY BOOK
          </div>
        <?php endif; ?>

       
        <div class="bg-light d-flex align-items-center justify-content-center" style="height:280px; overflow:hidden;">
            <?php if (!empty($book['cover_url'])): ?>
              <img src="<?= sanitize($book['cover_url']) ?>"
                   class="card-img-top w-100 h-100" alt="<?= sanitize($book['title']) ?>"
                   style="object-fit: cover;">
            <?php else: ?>
              <div class="text-center text-muted">
                  <i class="bi bi-image display-4"></i>
                  <p class="x-small mb-0">No Cover</p>
              </div>
            <?php endif; ?>
        </div>

        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-start mb-1">
              <h6 class="card-title fw-bold text-dark mb-0 text-truncate" title="<?= sanitize($book['title']) ?>">
                <?= sanitize($book['title']) ?>
              </h6>
          </div>
          <p class="text-muted mb-2 small italic">by <?= sanitize($book['author_name'] ?? 'Unknown Author') ?></p>
          
          <div class="mb-2">
            <span class="text-primary fw-bold">EGP <?= number_format($book['final_price'], 2) ?></span>
            <span class="badge <?= conditionBadgeClass($book['condition_grade'] ?? 'good') ?> ms-1">
              <?= strtoupper(sanitize($book['condition_grade'] ?? 'good')) ?>
            </span>
          </div>

          <?php if ((int)($book['stock_qty'] ?? 0) <= 0): ?>
             <div class="text-danger small mb-2"><i class="bi bi-x-circle me-1"></i>Out of Stock</div>
          <?php else: ?>
             <div class="text-success small mb-2"><i class="bi bi-check-circle me-1"></i>In Stock (<?= (int)$book['stock_qty'] ?>)</div>
          <?php endif; ?>

          <p class="text-muted mb-0 x-small bg-light p-1 rounded-pill d-inline-block px-2">
            <i class="bi bi-tag me-1"></i><?= sanitize($book['genre'] ?? 'General') ?>
          </p>
        </div>

        <div class="card-footer bg-white border-0 px-3 pb-3 pt-0 d-grid gap-2">
          <a href="index.php?page=books&action=show&id=<?= (int)$book['id'] ?>"
             class="btn btn-outline-dark btn-sm">
            View Details
          </a>

          <?php if ($userRole === 'READER' && (int)($book['stock_qty'] ?? 0) > 0): ?>
          <form method="POST" action="index.php?page=orders&action=addToCart" class="d-grid">
            <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
            <button type="submit" class="btn btn-primary btn-sm shadow-sm">
              <i class="bi bi-cart-plus me-1"></i>Add to Cart
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>