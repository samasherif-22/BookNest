<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-4" style="max-width:720px;">
  
  <!-- ── Stylish Breadcrumbs Section ──────────────────────────── -->
  <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb bg-white p-3 shadow-sm border rounded-pill" style="--bs-breadcrumb-divider: '›';">
          <li class="breadcrumb-item">
              <a href="<?= BASE_URL ?>index.php?page=clubs" class="text-decoration-none d-inline-flex align-items-center fw-bold text-primary">
                  <i class="bi bi-people-fill me-2"></i> Clubs
              </a>
          </li>
          <li class="breadcrumb-item">
              <a href="<?= BASE_URL ?>index.php?page=clubs&action=show&id=<?= (int)$club['id'] ?>" class="text-decoration-none d-inline-flex align-items-center fw-bold text-primary">
                  <?= sanitize($club['name']) ?>
              </a>
          </li>
          <li class="breadcrumb-item active d-inline-flex align-items-center text-secondary fw-semibold" aria-current="page">
              <i class="bi bi-person-plus-fill me-2"></i> Join Requests
          </li>
      </ol>
  </nav>

  <h1 class="section-title mb-4"> Pending Join Requests</h1>

  <?php if (empty($requests)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-inbox display-4 d-block mb-3"></i>
      No pending join requests.
    </div>
  <?php else: ?>
  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Name</th><th>Email</th><th>Requested</th><th class="text-center">Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($requests as $req): ?>
          <tr>
           
            <td class="fw-bold"><?= sanitize($req['name'] ?? '—') ?></td>
            <td class="text-muted small"><?= sanitize($req['email'] ?? '—') ?></td>
            <td class="small text-muted"><?= date('d M Y', strtotime($req['created_at'])) ?></td>
            <td class="text-center">
              <!-- Approve -->
              <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=approveRequest"
                    class="d-inline">
                <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                <input type="hidden" name="club_id"    value="<?= (int)$club['id'] ?>">
                <input type="hidden" name="user_id"    value="<?= (int)$req['user_id'] ?>">
                <input type="hidden" name="action_type" value="approved">
                <button type="submit" class="btn btn-sm btn-success me-1">Approve</button>
              </form>
              <!-- Reject -->
              <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=approveRequest"
                    class="d-inline">
                <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                <input type="hidden" name="club_id"    value="<?= (int)$club['id'] ?>">
                <input type="hidden" name="user_id"    value="<?= (int)$req['user_id'] ?>">
                <input type="hidden" name="action_type" value="rejected">
                <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>