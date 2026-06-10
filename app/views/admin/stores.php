<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-4">
  <h1 class="section-title mb-4">Store Management</h1>

  <! Pending Applications >
  <?php if (!empty($pending)): ?>
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning text-dark fw-bold">
       Pending Store Approvals (<?= count($pending) ?>)
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Store Name</th><th>City</th><th>Owner</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($pending as $store): ?>
          <tr>
           <td class="fw-bold"><?= sanitize($store['store_name'] ?? '—') ?></td>
            <td><?= sanitize($store['city'] ?? '—') ?></td>
            <td class="small text-muted"><?= sanitize($store['applicant_name'] ?? '—') ?></td>  
            <td>
              <!-- Approve -->
              <form method="POST" action="<?= BASE_URL ?>index.php?page=admin&action=approveStore"
                    class="d-inline">
                <input type="hidden" name="store_id" value="<?= (int)$store['id'] ?>">
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="btn btn-sm btn-success me-1">Approve</button>
              </form>
              <!-- Reject -->
              <form method="POST" action="<?= BASE_URL ?>index.php?page=admin&action=approveStore"
                    class="d-inline">
                <input type="hidden" name="store_id" value="<?= (int)$store['id'] ?>">
                <input type="hidden" name="status" value="rejected">
                <button type="submit" class="btn btn-sm btn-outline-danger">✗ Reject</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── All Stores ────────────────────────────────────────────────────────── -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold">All Stores (<?= count($stores) ?>)</div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>#</th><th>Store</th><th>City</th><th>Region</th><th>Owner</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($stores as $s): ?>
          <?php
            $statusBadge = [
              'approved' => 'bg-success',
              'pending'  => 'bg-warning text-dark',
              'rejected' => 'bg-danger',
            ];
          ?>
          <tr>
            <td class="text-muted small"><?= (int)$s['id'] ?></td>
            <td class="fw-bold"><?= sanitize($s['name']) ?></td>
            <td><?= sanitize($s['city'] ?? '—') ?></td>
            <td><?= sanitize($s['region'] ?? '—') ?></td>
            <td class="small"><?= sanitize($s['owner_name'] ?? '—') ?></td>
            <td>
              <span class="badge <?= $statusBadge[$s['status'] ?? 'pending'] ?? 'bg-secondary' ?>">
                <?= ucfirst($s['status'] ?? 'pending') ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
