<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$isEdit = ($club !== null);
$action = $isEdit
    ? BASE_URL . 'index.php?page=clubs&action=update'
    : BASE_URL . 'index.php?page=clubs&action=store';
?>

<div class="container py-4" style="max-width:620px;">
  <h1 class="section-title mb-4"><?= $isEdit ? 'Edit Club' : 'Create Book Club' ?></h1>

  <div class="card border-0 shadow p-4">
    <form method="POST" action="<?= $action ?>">
      <?php if ($isEdit): ?>
        <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label fw-semibold">Club Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control"
               value="<?= sanitize($club['name'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= sanitize($club['description'] ?? '') ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Primary Genre</label>
        <select name="genre" class="form-select">
          <?php foreach (['Fiction','Non-Fiction','Mystery','Sci-Fi','Fantasy','Romance','History','Other'] as $g): ?>
          <option value="<?= $g ?>" <?= (($club['genre'] ?? '') === $g) ? 'selected' : '' ?>><?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_private" id="is_private" value="1"
                 <?= !empty($club['is_private']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="is_private">
            <strong>Private Club</strong>
            <span class="text-muted small d-block">Members must be approved by the organizer.</span>
          </label>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
          <?= $isEdit ? 'Save Changes' : 'Create Club' ?>
        </button>
        <a href="<?= BASE_URL ?>index.php?page=clubs" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
