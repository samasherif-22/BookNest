<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
   
      <h1 class="section-title mb-1"><?= $title ?? 'Book Clubs' ?></h1>
      <p class="text-muted small mb-0"><?= count($clubs) ?> clubs found</p>
    </div>
    
    <?php if (in_array(currentRole(), ['CLUB_ORGANIZER', 'SYSTEM_ADMIN'])): ?>
    <a href="<?= BASE_URL ?>index.php?page=clubs&action=create" class="btn btn-primary shadow-sm">
      <i class="bi bi-plus-lg me-1"></i>Create Club
    </a>
    <?php endif; ?>
  </div>

  <?php if (empty($clubs)): ?>
    <div class="text-center py-5 text-muted bg-light rounded-4 border border-dashed">
      <i class="bi bi-people display-4 d-block mb-3 text-secondary"></i>
      <?php if (isset($title) && $title === 'My Joined Clubs'): ?>
        <p>You haven't joined any clubs yet.</p>
        <a href="<?= BASE_URL ?>index.php?page=clubs" class="btn btn-sm btn-outline-primary">Explore Clubs</a>
      <?php else: ?>
        <p>No clubs available at the moment.</p>
      <?php endif; ?>
    </div>
  <?php else: ?>

  <div class="row g-4">
    <?php foreach ($clubs as $club): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card border-0 shadow-sm h-100 rounded-xl overflow-hidden">
        
        
        <div class="p-3 bg-dark text-white">
          <div class="d-flex justify-content-between align-items-start">
            <h5 class="fw-bold mb-1"><?= sanitize($club['name']) ?></h5>
            <?php if ($club['is_private']): ?>
              <span class="badge bg-warning text-dark" style="font-size: 0.65rem;"><i class="bi bi-lock-fill me-1"></i>Private</span>
            <?php else: ?>
              <span class="badge bg-success" style="font-size: 0.65rem;"><i class="bi bi-unlock-fill me-1"></i>Public</span>
            <?php endif; ?>
          </div>
          <span class="badge bg-light text-dark mt-1" style="font-size:.65rem;"><?= sanitize($club['genre'] ?? 'General') ?></span>
        </div>

        <div class="card-body">
          <p class="text-muted small mb-3" style="min-height:45px;">
            <?= sanitize(mb_strimwidth($club['description'] ?? 'No description provided.', 0, 100, '…')) ?>
          </p>
          <div class="d-flex align-items-center gap-2 small text-muted">
            <i class="bi bi-people-fill text-primary"></i>
            <span><?= (int)($club['member_count'] ?? 0) ?> Members</span>
          </div>
        </div>

        <div class="card-footer bg-white border-0 p-3 pt-0 d-flex gap-2">
          <a href="<?= BASE_URL ?>index.php?page=clubs&action=show&id=<?= (int)$club['id'] ?>"
             class="btn btn-outline-primary btn-sm flex-grow-1 rounded-pill">Details</a>

        
          <?php if (isLoggedIn() && currentRole() === 'READER' && ($title ?? '') !== 'My Joined Clubs'): ?>
          <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=joinClub" class="flex-grow-1">
            <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
            <button class="btn btn-primary btn-sm w-100 rounded-pill shadow-sm">
              <i class="bi bi-person-plus-fill me-1"></i>
              <?= $club['is_private'] ? 'Request' : 'Join' ?>
            </button>
          </form>
          <?php elseif (($title ?? '') === 'My Joined Clubs'): ?>
           
            <button class="btn btn-light btn-sm flex-grow-1 rounded-pill disabled text-success fw-bold">
                <i class="bi bi-check-circle-fill me-1"></i> Member
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>