<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';


$currentUserId = $_SESSION['user_id'] ?? 0;
$userRole      = $_SESSION['role'] ?? 'GUEST';


$isOwner = ($userRole === 'AUTHOR' && (int)($event['organizer_id'] ?? 0) === (int)$currentUserId);
$isAdmin = ($userRole === 'SYSTEM_ADMIN');
$canManage = ($isOwner || $isAdmin); 
?>

<div class="container py-4" style="max-width:860px;">
  <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-4 shadow-sm" role="alert">
      <?= $flash['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4 pt-2">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
          <a href="<?= BASE_URL ?>index.php?page=events" class="text-decoration-none fw-bold" style="color: #2c3e50;">
              <i class="bi bi-arrow-left-short me-1"></i>All Events
          </a>
      </li>
      <li class="breadcrumb-item active fw-semibold text-muted"><?= sanitize($event['title']) ?></li>
    </ol>
  </nav>

  <!-- Header Card -->
  <div class="card border-0 shadow mb-4 p-4" style="background: #2c3e50; color:#fff; border-radius:16px;">
    <div class="row align-items-center">
      <div class="col-md-8">
        <span class="badge bg-<?= ($event['status'] === 'live' ? 'success' : ($event['status'] === 'ended' ? 'secondary' : 'info text-dark')) ?> mb-2 px-3 py-2">
          <?= ucfirst($event['status']) ?>
        </span>
        <h1 class="fw-bold mb-2"><?= sanitize($event['title']) ?></h1>
        <div class="text-white-50 small">
          <i class="bi bi-calendar3 me-2"></i><?= date('l, d F Y • H:i', strtotime($event['event_date'])) ?>
          <?php if (!empty($event['city'])): ?> | <i class="bi bi-geo-alt-fill me-1 text-danger"></i><?= sanitize($event['city']) ?><?php endif; ?>
        </div>
       
        <div class="mt-2 text-white-50 small">
            <i class="bi bi-person-circle me-1"></i> Organizer: <?= sanitize($event['organizer_name'] ?? 'Unknown') ?>
        </div>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <div class="display-6 fw-bold text-warning"><?= (float)$event['ticket_price'] > 0 ? 'EGP '.number_format($event['ticket_price'], 2) : 'FREE' ?></div>
          <div class="text-white-50 small">per ticket</div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-8">
     
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 12px;">
        <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>About This Event</h5>
        <p class="text-muted" style="line-height:1.75;"><?= nl2br(sanitize($event['description'] ?? 'No description.')) ?></p>
      </div>

      
      <?php if ($canManage): ?>
      <div class="card border-0 shadow-sm p-4 bg-light border-start border-primary border-4" style="border-radius: 12px;">
        <h5 class="fw-bold mb-3">Management Tools</h5>
        <p class="small text-muted mb-3">As the owner, you can update the event status to notify attendees.</p>
        <form method="POST" action="<?= BASE_URL ?>index.php?page=events&action=updateStatus" class="d-flex gap-2">
          <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
          <select name="status" class="form-select w-auto shadow-sm">
            <?php foreach (['upcoming','live','ended'] as $s): ?>
              <option value="<?= $s ?>" <?= $event['status']===$s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-dark px-4 shadow-sm">Update Status</button>
        </form>
      </div>
      <?php endif; ?>
    </div>

    
    <div class="col-md-4">
      <div class="card border-0 shadow-sm p-4 text-center" style="border-radius: 12px;">
        
        <?php if (isset($ticket) && $ticket): ?>
            
            <?php if ($ticket['status'] === 'confirmed'): ?>
                <i class="bi bi-check-circle-fill display-4 text-success mb-2"></i>
                <h5 class="fw-bold">You're Going!</h5>
                <p class="text-muted small">Your spot is confirmed.</p>
                <?php if ($event['status'] === 'live'): ?>
                    <a href="<?= BASE_URL ?>index.php?page=events&action=lobby&id=<?= $event['id'] ?>" class="btn btn-success w-100 shadow-sm">Enter Lobby</a>
                <?php endif; ?>
            <?php else: ?>
                <i class="bi bi-hourglass-split display-4 text-warning mb-2"></i>
                <h5 class="fw-bold text-warning">Waiting List</h5>
                <p class="text-muted small">We'll notify you if a spot opens up.</p>
            <?php endif; ?>

        <?php elseif ($event['status'] === 'ended'): ?>
            <i class="bi bi-calendar-x display-4 text-muted mb-2"></i>
            <p class="mb-0 fw-semibold text-muted">This event has ended.</p>

        <?php elseif ($userRole === 'READER'): ?>
            
            <h5 class="fw-bold mb-3">Registration</h5>
            <?php $isFull = ((int)$event['tickets_sold'] >= (int)$event['capacity']); ?>
            <form method="POST" action="<?= BASE_URL ?>index.php?page=events&action=buyTicket">
                <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                <?php if ($isFull): ?>
                    <div class="alert alert-warning py-1 small mb-3">Sold Out! Join Waitlist?</div>
                    <button type="submit" class="btn btn-warning w-100 fw-bold shadow-sm">Join Waiting List</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Get Ticket Now</button>
                <?php endif; ?>
            </form>

        <?php elseif ($userRole === 'GUEST' || $currentUserId === 0): ?>
            <p class="text-muted small mb-0">Please <a href="<?= BASE_URL ?>index.php?page=login" class="fw-bold">login</a> to register.</p>

        <?php else: ?>
           
            <?php if ($event['status'] === 'upcoming'): ?>
                <i class="bi bi-calendar-event display-4 text-info mb-2"></i>
                <p class="mb-0 fw-semibold text-info">Upcoming Event</p>
            <?php elseif ($event['status'] === 'live'): ?>
                <i class="bi bi-broadcast display-4 text-success mb-2"></i>
                <p class="mb-0 fw-semibold text-success">Live Now!</p>
            <?php endif; ?>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>