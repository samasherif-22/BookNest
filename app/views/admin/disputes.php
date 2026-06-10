<?php
/*
 * app/views/admin/disputes.php
 * -----------------------------
 * SYSTEM_ADMIN: list open order disputes and resolve them.
 * Variables from AdminController::disputes():  $disputes (array)
 */
require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-4">
  <h1 class="section-title mb-4">Dispute Management</h1>

  <?php if (empty($disputes)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-check-circle display-4 text-success d-block mb-3"></i>
      No open disputes. All clear!
    </div>
  <?php else: ?>
  <div class="row g-4">
    <?php foreach ($disputes as $d): ?>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm border-start border-danger border-4">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="fw-bold mb-0">Dispute #<?= (int)$d['id'] ?></h5>
            <span class="badge bg-danger">Open</span>
          </div>
          <p class="text-muted small mb-1">
            <i class="bi bi-person me-1"></i>Reporter: <strong><?= sanitize($d['reporter_name'] ?? '—') ?></strong>
          </p>
          <p class="text-muted small mb-1">
            <i class="bi bi-bag me-1"></i>Order: #<?= (int)$d['order_id'] ?>
          </p>
          <p class="text-muted small mb-2">
            <i class="bi bi-clock me-1"></i><?= date('d M Y', strtotime($d['created_at'])) ?>
          </p>

          <div class="bg-light rounded p-2 mb-3 small">
            <strong>Reason:</strong> <?= sanitize($d['reason']) ?>
          </div>

          <!-- Resolve Form -->
          <form method="POST" action="<?= BASE_URL ?>index.php?page=admin&action=resolveDispute">
            <input type="hidden" name="dispute_id" value="<?= (int)$d['id'] ?>">
            <div class="mb-2">
              <textarea name="resolution" class="form-control form-control-sm" rows="2"
                        placeholder="Resolution note…" required></textarea>
            </div>
            <button type="submit" class="btn btn-sm btn-success w-100">
              <i class="bi bi-check-lg me-1"></i>Mark Resolved
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
