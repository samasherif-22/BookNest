<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-4">
  <h1 class="section-title mb-2"> System Audit Trail </h1>
  <p class="text-muted mb-4">Showing the 200 most recent logged actions across the platform.</p>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0" style="font-size:.85rem;">
        <thead>
          <tr>
            <th>When</th>
            <th>User</th>
            <th>Action</th>
            <th>Entity</th>
            <th>Entity ID</th>
            <th>Detail</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No audit records found.</td></tr>
          <?php else: ?>
          <?php foreach ($logs as $log): ?>
          <tr>
            <td class="text-muted" style="white-space:nowrap;">
              <?= date('d M H:i', strtotime($log['created_at'])) ?>
            </td>
            <td><?= sanitize($log['user_name'] ?? '(system)') ?></td>
            <td>
              <span class="badge bg-secondary" style="font-size:.72rem; letter-spacing:.3px;">
                <?= sanitize($log['action']) ?>
              </span>
            </td>
            <td><?= sanitize($log['entity']) ?></td>
            <td class="text-muted"><?= $log['entity_id'] ? '#' . (int)$log['entity_id'] : '—' ?></td>
            <td class="text-muted" style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
              <?= sanitize($log['detail'] ?? '') ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
