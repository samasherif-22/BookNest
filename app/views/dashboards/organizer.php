<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-4">
  <h1 class="section-title mb-1">Organizer Dashboard</h1>
  <p class="text-muted mb-4">Manage your book clubs from here.</p>

  <!-- Quick Actions -->
  <div class="d-flex gap-2 flex-wrap mb-4">
    <a href="<?= BASE_URL ?>index.php?page=clubs&action=create" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-lg me-1"></i>Create New Club
    </a>
    <a href="<?= BASE_URL ?>index.php?page=clubs" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-people me-1"></i>All Clubs
    </a>
    <a href="<?= BASE_URL ?>index.php?page=events&action=verify" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-qr-code-scan me-1"></i>Verify Attendance
    </a>
  </div>

  <?php if (empty($clubs)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-people display-4 d-block mb-3"></i>
      You haven't created any clubs yet.
      <br>
      <a href="<?= BASE_URL ?>index.php?page=clubs&action=create" class="btn btn-primary mt-3">
        Create Your First Club
      </a>
    </div>
  <?php else: ?>

  <!-- Stat Row -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card text-center p-3">
        <div class="stat-number"><?= count($clubs) ?></div>
        <div class="stat-label">Clubs Managed</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card text-center p-3">
        <div class="stat-number">
          <?= array_sum(array_column($clubs, 'member_count')) ?>
        </div>
        <div class="stat-label">Total Members</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card text-center p-3">
        <div class="stat-number">
          <?= count(array_filter($clubs, fn($c) => $c['is_private'])) ?>
        </div>
        <div class="stat-label">Private Clubs</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card text-center p-3">
        <div class="stat-number">
          <?= count(array_filter($clubs, fn($c) => !$c['is_private'])) ?>
        </div>
        <div class="stat-label">Public Clubs</div>
      </div>
    </div>
  </div>

  <!-- Club Cards -->
  <div class="row g-4">
    <?php foreach ($clubs as $club): ?>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="fw-bold mb-0"><?= sanitize($club['name']) ?></h5>
            <?php if ($club['is_private']): ?>
              <span class="badge bg-warning text-dark"><i class="bi bi-lock me-1"></i>Private</span>
            <?php else: ?>
              <span class="badge bg-success">Public</span>
            <?php endif; ?>
          </div>
          <p class="text-muted small mb-3">
            <?= sanitize(mb_strimwidth($club['description'] ?? '', 0, 80, '…')) ?>
          </p>

          <div class="d-flex gap-3 text-muted small mb-3">
            <span><i class="bi bi-people me-1"></i><?= (int)($club['member_count'] ?? 0) ?> members</span>
            <span><i class="bi bi-tag me-1"></i><?= sanitize($club['genre'] ?? 'General') ?></span>
          </div>

          <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>index.php?page=clubs&action=show&id=<?= (int)$club['id'] ?>"
               class="btn btn-outline-primary btn-sm">Manage</a>
            <?php if ($club['is_private']): ?>
            <a href="<?= BASE_URL ?>index.php?page=clubs&action=requests&id=<?= (int)$club['id'] ?>"
               class="btn btn-outline-warning btn-sm">
              <i class="bi bi-person-check me-1"></i>Join Requests
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
