<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';


$userRole = $_SESSION['role'] ?? 'GUEST';
if (!in_array($userRole, ['BOOKSTORE_OWNER', 'SYSTEM_ADMIN', 'CLUB_ORGANIZER'])) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Unauthorized Access.</div></div>";
    require_once BASE_PATH . '/app/views/partials/footer.php';
    exit;
}
?>

<div class="container py-5" style="max-width:520px;">
  
  <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
      <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?> me-2"></i>
      <?= $flash['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="text-center mb-4">
    <div class="display-6 text-primary mb-2"><i class="bi bi-shield-check"></i></div>
    <h1 class="h3 fw-bold">Attendance Verification</h1>
    <p class="text-muted small">Process event entries and award loyalty points (F29)</p>
  </div>

  <div class="card border-0 shadow-lg p-4 rounded-xl">
    <form method="POST" action="index.php?page=events&action=verifyAttendance">
      <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Attendee Entry Token</label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-key text-muted"></i></span>
            <input type="text" name="token" 
                   class="form-control form-control-lg text-center fw-monospace"
                   placeholder="e.g. a1b2c3d4..." 
                   pattern="[a-f0-9]{32}" 
                   maxlength="32" 
                   required
                   autocomplete="off" 
                   style="letter-spacing: 1px; font-size: 0.95rem;">
        </div>
        <div class="form-text mt-2 small text-center">
          Tokens are 32-character hexadecimal strings.
        </div>
      </div>

      <button type="submit" class="btn btn-success w-100 py-3 fw-bold shadow-sm">
        <i class="bi bi-qr-code-scan me-2"></i>Confirm Attendance
      </button>
    </form>
  </div>

  <div class="mt-4 p-3 bg-light rounded border border-info-subtle">
    <div class="d-flex align-items-start">
        <i class="bi bi-info-circle-fill text-info me-3 mt-1"></i>
        <div class="small">
            <strong>System Protocol:</strong> On successful verification, the system marks the ticket as <code>attended</code> and automatically credits <strong>10 loyalty points</strong> to the reader's profile.
        </div>
    </div>
  </div>

  <div class="text-center mt-4">
    <a href="index.php?page=events" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i> Back to Events List
    </a>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>