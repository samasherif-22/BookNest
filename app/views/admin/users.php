<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$roleOptions = ['READER','BOOKSTORE_OWNER','CLUB_ORGANIZER','AUTHOR','SYSTEM_ADMIN'];
$roleBadge   = [
    'READER'          => 'bg-secondary',
    'BOOKSTORE_OWNER' => 'bg-warning text-dark',
    'CLUB_ORGANIZER'  => 'bg-info text-dark',
    'AUTHOR'          => 'bg-primary',
    'SYSTEM_ADMIN'    => 'bg-danger',
];
?>

<div class="container py-4">
  <h1 class="section-title mb-4">User Management</h1>

  <!-- Search Form -->
  <form method="GET" action="<?= BASE_URL ?>index.php" class="d-flex gap-2 mb-4" style="max-width:460px;">
    <input type="hidden" name="page" value="admin">
    <input type="hidden" name="action" value="users">
    <input type="text" name="q" class="form-control" placeholder="Search by name or email…"
           value="<?= sanitize($query ?? '') ?>">
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if (!empty($query)): ?>
    <a href="<?= BASE_URL ?>index.php?page=admin&action=users" class="btn btn-outline-secondary">Clear</a>
    <?php endif; ?>
  </form>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <span class="fw-bold"><?= count($users) ?> users</span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Change Role</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
          <tr>
            <td class="text-muted small"><?= (int)$user['id'] ?></td>
            <td class="fw-bold"><?= sanitize($user['name']) ?></td>
            <td class="text-muted small"><?= sanitize($user['email']) ?></td>
            <td>
              <span class="badge <?= $roleBadge[$user['role']] ?? 'bg-secondary' ?>">
                <?= sanitize($user['role']) ?>
              </span>
            </td>
            <td class="text-muted small"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
            <td>
              <?php if ((int)$user['id'] !== currentUserId()): ?>
              <form method="POST" action="<?= BASE_URL ?>index.php?page=admin&action=changeRole"
                    class="d-flex gap-1">
                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                <select name="role" class="form-select form-select-sm" style="width:auto;">
                  <?php foreach ($roleOptions as $r): ?>
                  <option value="<?= $r ?>" <?= $user['role'] === $r ? 'selected' : '' ?>>
                    <?= $r ?>
                  </option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary py-0">Set</button>
              </form>
              <?php else: ?>
              <span class="text-muted small">That's you</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
