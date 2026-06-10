<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';


$userRole = $_SESSION['role'] ?? 'GUEST';
?>

<div class="container py-4">
  <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
      <?= $flash['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="section-title mb-1">Literary Events</h1>
      <p class="text-muted small mb-0"><?= count($events ?? []) ?> events listed</p>
    </div>
    
    <?php if (in_array($userRole, ['AUTHOR', 'SYSTEM_ADMIN'])): ?>
    <a href="index.php?page=events&action=create" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Create Event
    </a>
    <?php endif; ?>
  </div>

  <?php if (empty($events)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-calendar-x display-4 d-block mb-3"></i>
      No events scheduled yet. Check back soon!
    </div>
  <?php else: ?>
  <div class="row g-4">
    <?php foreach ($events as $event): ?>
    <?php
      
      $statusColors = [
          'upcoming' => 'bg-info text-dark',
          'live'     => 'bg-success',
          'ended'    => 'bg-secondary'
      ];
      $statusColor = $statusColors[$event['status']] ?? 'bg-secondary';
    ?>
    <div class="col-md-6 col-lg-4">
      <div class="card border-0 shadow-sm h-100 rounded-xl overflow-hidden">
        
        <div class="p-3 bg-dark text-white">
          <div class="d-flex justify-content-between align-items-start">
            <h5 class="fw-bold mb-1" style="font-size: 1.1rem;"><?= sanitize($event['title']) ?></h5>
            <span class="badge <?= $statusColor ?>"><?= ucfirst($event['status']) ?></span>
          </div>
          <div class="text-white-50 small">
            <i class="bi bi-calendar me-1"></i>
            <?= date('d M Y • H:i', strtotime($event['event_date'])) ?>
          </div>
        </div>

        <div class="card-body">
          <p class="text-muted small mb-3">
            <?= sanitize(mb_strimwidth($event['description'] ?? '', 0, 100, '…')) ?>
          </p>
          
          <div class="d-flex flex-column gap-2 small">
            <?php if (!empty($event['city'])): ?>
              <span class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i><?= sanitize($event['city']) ?></span>
            <?php endif; ?>
            
            <?php if (!empty($event['stream_url'])): ?>
              <span class="text-success fw-bold"><i class="bi bi-camera-video me-1"></i>Virtual Event</span>
            <?php endif; ?>

            <div class="mt-2">
                <?php if ((float)$event['ticket_price'] > 0): ?>
                  <span class="badge bg-warning text-dark fs-6">EGP <?= number_format($event['ticket_price'], 2) ?></span>
                <?php else: ?>
                  <span class="badge bg-success fs-6">Free</span>
                <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="card-footer bg-white border-0 px-3 pb-3 d-flex gap-2">
          <a href="index.php?page=events&action=show&id=<?= (int)$event['id'] ?>"
             class="btn btn-outline-dark btn-sm flex-grow-1">Details</a>
          
          <?php if ($event['status'] !== 'ended' && $userRole === 'READER'): ?>
          <a href="index.php?page=events&action=show&id=<?= (int)$event['id'] ?>"
             class="btn btn-primary btn-sm px-4">Get Ticket</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>