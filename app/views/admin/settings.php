<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-4" style="max-width:640px;">
  <h1 class="section-title mb-4">⚙️ Account Settings</h1>

  <!-- ── Reading History Privacy-->
  <div class="card border-0 shadow-sm mb-4 p-4">
    <h5 class="fw-bold mb-1">Reading History Privacy</h5>
    <p class="text-muted small mb-3">
      Choose whether your reading history is visible to other club members.
    </p>
    <form method="POST" action="<?= BASE_URL ?>index.php?page=settings&action=updatePrivacy">
      <div class="d-flex gap-3 mb-3">
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="privacy" id="privPublic"
                 value="PUBLIC" <?= ($user['privacy'] ?? 'PRIVATE') === 'PUBLIC' ? 'checked' : '' ?>>
          <label class="form-check-label" for="privPublic">
            <strong>Public</strong>
            <span class="text-muted small d-block">Other members can see your reading history.</span>
          </label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="privacy" id="privPrivate"
                 value="PRIVATE" <?= ($user['privacy'] ?? 'PRIVATE') === 'PRIVATE' ? 'checked' : '' ?>>
          <label class="form-check-label" for="privPrivate">
            <strong>Private</strong>
            <span class="text-muted small d-block">Only you can see your reading history.</span>
          </label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Save Privacy Setting</button>
    </form>
  </div>

  <!-- ── Account Info-->
  <div class="card border-0 shadow-sm mb-4 p-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-person me-2"></i>Account Info</h5>
    <div class="row g-2 small">
      <div class="col-4 text-muted fw-semibold">Name:</div>
      <div class="col-8"><?= sanitize($user['name'] ?? '—') ?></div>
      <div class="col-4 text-muted fw-semibold">Email:</div>
      <div class="col-8"><?= sanitize($user['email'] ?? '—') ?></div>
      <div class="col-4 text-muted fw-semibold">Role:</div>
      <div class="col-8"><span class="badge bg-primary"><?= sanitize($user['role'] ?? '—') ?></span></div>
      <div class="col-4 text-muted fw-semibold">Joined:</div>
      <div class="col-8"><?= date('d F Y', strtotime($user['created_at'] ?? 'now')) ?></div>
      <div class="col-4 text-muted fw-semibold">Loyalty Points:</div>
      <div class="col-8"><strong class="text-warning"><?= (int)($user['loyalty_points'] ?? 0) ?> pts</strong></div>
    </div>
  </div>

  <!-- ── GDPR: Export Data -->
  <div class="card border-0 shadow-sm mb-4 p-4">
    <h5 class="fw-bold mb-1"> Export My Data</h5>
    <p class="text-muted small mb-3">
      Download a JSON file containing all your personal data stored in BookNest.
      This is your right under GDPR.
    </p>
    <form method="POST" action="<?= BASE_URL ?>index.php?page=settings&action=exportData">
      <button type="submit" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-download me-1"></i>Download My Data
      </button>
    </form>
  </div>

  <!-- ── GDPR: Delete Account -->
  <div class="card border-0 shadow-sm border-danger p-4" style="border-left:4px solid var(--danger)!important;">
    <h5 class="fw-bold text-danger mb-1">Delete My Account</h5>
    <p class="text-muted small mb-3">
      <strong>This action cannot be undone.</strong>
      Your personal data will be anonymized, but order records will be preserved (required for accounting).
    </p>
    <form method="POST" action="<?= BASE_URL ?>index.php?page=settings&action=deleteAccount">
      <button type="submit" class="btn btn-outline-danger btn-sm"
              data-confirm="This will permanently anonymize your account. Are you absolutely sure?">
        <i class="bi bi-exclamation-triangle me-1"></i>Delete / Anonymize My Account
      </button>
    </form>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
