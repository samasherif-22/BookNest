<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$isEdit = ($event !== null);
$action = $isEdit
    ? BASE_URL . 'index.php?page=events&action=update'
    : BASE_URL . 'index.php?page=events&action=store';
?>

<div class="container py-4" style="max-width:640px;">
  <h1 class="section-title mb-4"><?= $isEdit ? 'Edit Event' : 'Create Event' ?></h1>

  <div class="card border-0 shadow p-4">
    <form method="POST" action="<?= $action ?>">
      <?php if ($isEdit): ?>
        <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12">
          <label class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control"
                 value="<?= sanitize($event['title'] ?? '') ?>" required>
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" class="form-control" rows="4"><?= sanitize($event['description'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Date &amp; Time <span class="text-danger">*</span></label>
          <input type="datetime-local" name="event_date" class="form-control"
                 value="<?= sanitize($event['event_date'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Ticket Price (EGP)</label>
          <input type="number" name="ticket_price" class="form-control"
                 value="<?= sanitize($event['ticket_price'] ?? '0') ?>"
                 step="0.01" min="0">
          <div class="form-text">Leave at 0 for a free event.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">City (Physical Events)</label>
          <input type="text" name="city" class="form-control"
                 value="<?= sanitize($event['city'] ?? '') ?>" placeholder="e.g. Cairo">
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Capacity</label>
          <input type="number" name="capacity" class="form-control"
                 value="<?= (int)($event['capacity'] ?? 100) ?>" min="1">
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Stream URL (Virtual Events)</label>
          <input type="url" name="stream_url" class="form-control"
                 value="<?= sanitize($event['stream_url'] ?? '') ?>"
                 placeholder="https://zoom.us/j/…">
          <div class="form-text">Leave blank for physical-only events.</div>
        </div>

        <div class="col-12 d-flex gap-2 mt-2">
          <button type="submit" class="btn btn-primary px-4">
            <?= $isEdit ? 'Save Changes' : 'Create Event' ?>
          </button>
          <a href="<?= BASE_URL ?>index.php?page=events" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
