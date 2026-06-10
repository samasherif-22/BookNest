<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-4" style="max-width:680px;">
  <h1 class="section-title mb-4"> Notifications</h1>

  <?php if (empty($notifications)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-bell-slash display-4 d-block mb-3"></i>
      No notifications yet. Great — you're all caught up!
    </div>
  <?php else: ?>
  <div class="card border-0 shadow-sm">
    <div class="list-group list-group-flush">
      <?php foreach ($notifications as $n): ?>
      <a href="<?= sanitize($n['link'] ?? '#') ?>"
         class="list-group-item list-group-item-action py-3 px-4 <?= $n['is_read'] ? '' : 'bg-light' ?>">
        <div class="d-flex justify-content-between align-items-start">
          <div class="d-flex align-items-start gap-3">
            <?php if (!$n['is_read']): ?>
              <span class="badge bg-primary rounded-circle p-1 mt-1 flex-shrink-0" style="width:8px;height:8px;"></span>
            <?php else: ?>
              <span style="width:8px; height:8px; display:inline-block; flex-shrink:0;"></span>
            <?php endif; ?>
            <div>
              <div class="<?= $n['is_read'] ? 'text-muted' : 'fw-bold' ?>" style="font-size:.92rem;">
                <?= sanitize($n['message']) ?>
              </div>
              <div class="text-muted mt-1" style="font-size:.78rem;">
                <?= date('d M Y \a\t H:i', strtotime($n['created_at'])) ?>
              </div>
            </div>
          </div>
          <?php if (!$n['is_read']): ?>
          <span class="badge bg-primary ms-2 flex-shrink-0" style="font-size:.65rem;">New</span>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <p class="text-muted text-center small mt-3 mb-0">
    <?= count($notifications) ?> notification(s) — all marked as read.
  </p>
  <?php endif; ?>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
