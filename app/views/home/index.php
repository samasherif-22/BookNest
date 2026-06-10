<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';


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

<section class="hero-section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        
        <h1 class="display-5 fw-bold text-white mb-3">
          Discover-Collect &<br>Read with Community
        </h1>
        <p class="lead text-white-50 mb-4">
          Browse thousands of books from independent stores, join book clubs,<br>
          attend events,  and connect with authors — all in one place.
        </p>
      
        <br>
        <br>

        <!--  Buttons -->
        <div class="d-flex flex-wrap gap-3">
          <a href="<?= BASE_URL ?>index.php?page=books" class="btn btn-primary btn-lg fw-bold px-4">
           Browse Books
          </a>
          <?php if (!isLoggedIn()): ?>
          <a href="<?= BASE_URL ?>index.php?page=register"
             class="btn btn-outline-light btn-lg px-4">
             Join Free
          </a>
          <?php else: ?>
          <a href="<?= BASE_URL ?>index.php?page=clubs"
             class="btn btn-outline-light btn-lg px-4">
             <i class="bi bi-people me-2"></i>Explore Clubs
          </a>
          <?php endif; ?>
        </div>

      </div>

      <!-- right 4 squares -->
      <div class="col-lg-5 d-none d-lg-block">
        <div class="row g-3 mt-2">
          <?php
          $stats = [
            ['icon'=>'bi-book',       'label'=>'Books Listed',   'color'=>'#E67E22'],
            ['icon'=>'bi-people',     'label'=>'Active Readers', 'color'=>'#27AE60'],
            ['icon'=>'bi-shop',       'label'=>'Partner Stores', 'color'=>'#2980B9'],
            ['icon'=>'bi-calendar2',  'label'=>'Events Hosted',  'color'=>'#8e44ad'],
          ];
          foreach ($stats as $s): //loop for the 4 squares
          ?>
          <div class="col-6">
            <div class="card border-0 text-center p-3" style="background:rgba(255,255,255,0.1); border-radius:12px;">
              <i class="bi <?= $s['icon'] ?> mb-1" style="font-size:2rem; color:<?= $s['color'] ?>;"></i>
              <div class="text-white-50 small"><?= $s['label'] ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</section>

<!--browse books-->

<section class="py-5" style="background-color:#fff;">
  <div class="container">
    <h2 class="section-title mb-4">Recently Added Books</h2>

    <?php if (empty($allBooks)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox display-4"></i>
        <p class="mt-3">No books yet. Be the first store to list one!</p>
        <?php if (currentRole() === 'BOOKSTORE_OWNER'): ?>
          <a href="<?= BASE_URL ?>index.php?page=books&action=create" class="btn btn-primary">
            Add First Book
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
    <div class="row g-4">
      <?php foreach ($allBooks as $book): ?>
      
      <?php 
          
          $bookStoreId = isset($book['store_id']) ? (int)$book['store_id'] : -1;
          $isMyBook = ($userRole === 'BOOKSTORE_OWNER' && $currentStoreId > 0 && $bookStoreId === $currentStoreId);
      ?>

     
      <div class="col-6 col-md-4 col-lg-3 fade-in-up">
        
        <div class="card book-card h-100 border-0 shadow-sm position-relative overflow-hidden">
          
        
          <?php if ($isMyBook): ?>
            <div class="position-absolute top-0 end-0 bg-success text-white fw-bold px-3 py-1 shadow-sm small" 
                 style="z-index: 10; border-bottom-left-radius: 12px; font-size: 0.7rem;">
               <i class="bi bi-shop me-1"></i>MY BOOK
            </div>
          <?php endif; ?>

         
          <?php if ($book['cover_url']): ?>
            <img src="<?= BASE_URL . sanitize($book['cover_url']) ?>"
                 class="card-img-top" alt="<?= sanitize($book['title']) ?>"
                 style="height:320px; object-fit:cover;">
          <?php else: ?>
            <div class="cover-placeholder d-flex align-items-center justify-content-center bg-light text-muted" style="height:320px;">
              <i class="bi bi-book-half" style="font-size: 4rem;"></i>
            </div>
          <?php endif; ?>

          <div class="card-body p-3 d-flex flex-column">
            <h6 class="card-title fw-bold mb-1" style="font-size:1rem; line-height:1.3;">
              <?= sanitize($book['title']) ?>
            </h6>
            <p class="text-muted mb-3" style="font-size:.85rem;">
              by <?= sanitize($book['author_name']) ?>
            </p>
            
         
            <div class="mt-auto d-flex gap-2 flex-wrap align-items-center">
              <span class="fw-bold text-success" style="font-size: 1.1rem;">EGP <?= number_format($book['final_price'], 2) ?></span>
              <span class="badge <?= conditionBadgeClass($book['condition_grade']) ?> ms-auto" style="font-size: 0.75rem;">
                <?= sanitize($book['condition_grade']) ?>
              </span>
            </div>
          </div>

          <div class="card-footer bg-transparent border-0 px-3 pb-3 pt-0">
            <a href="<?= BASE_URL ?>index.php?page=books&action=show&id=<?= (int)$book['id'] ?>"
               class="btn btn-outline-primary btn-sm w-100 py-2">
              View Details
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div><!-- /.row -->
    <?php endif; ?>
  </div>
</section>

<!--why booknest-->
<section class="py-5" style="background:var(--bg-light);">
  <div class="container">
    <h2 class="section-title text-center mb-5">Why BookNest?</h2>
    <div class="row g-4 text-center">
      <?php
      $features = [
        ['icon'=>'bi-shop',             'color'=>'#E67E22', 'title'=>'Independent Stores',   'text'=>'Browse books from curated local bookstores with verified quality grading.'],
        ['icon'=>'bi-people-fill',      'color'=>'#27AE60', 'title'=>'Book Clubs',           'text'=>'Join public or private clubs. Vote for the next read, track chapters together.'],
        ['icon'=>'bi-calendar-event',   'color'=>'#2980B9', 'title'=>'Literary Events',      'text'=>'Attend virtual and physical author events. Get your ticket scanned at the door.'],
       ['icon'=>'bi-arrow-left-right', 'color'=>'#8e44ad', 'title'=>'Book Lending',         'text'=>'Easily lend your purchased books to other readers and share your library.']
      ];
      foreach ($features as $f):
      ?>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 p-4 rounded-xl fade-in-up">
          <div class="mb-3">
            <i class="bi <?= $f['icon'] ?>" style="font-size:2.5rem; color:<?= $f['color'] ?>;"></i>
          </div>
          <h5 class="fw-bold mb-2"><?= $f['title'] ?></h5>
          <p class="text-muted small mb-0"><?= $f['text'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php 
  require_once __DIR__ . '/../partials/footer.php'; 
?>