<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$status   = $lobby['status'] ?? 'upcoming';
$title    = $lobby['title'] ?? 'Event';
$date     = $lobby['event_date'] ?? '';
$streamUrl = $lobby['stream_url'] ?? '';
?>

<div class="container py-5 text-center" style="max-width:680px;">
  <div class="card border-0 shadow-lg p-5 rounded-xl">

    <!-- Event Status Header -->
    <?php if ($status === 'live'): ?>
      <div class="mb-3">
        <span class="badge bg-success px-3 py-2" style="font-size:1rem;">LIVE NOW</span>
      </div>
      <h1 class="fw-bold mb-2"><?= sanitize($title) ?></h1>
      <p class="text-muted mb-4">The event is happening right now!</p>

      <?php if (!empty($streamUrl)): ?>
      <a href="<?= sanitize($streamUrl) ?>" target="_blank" rel="noopener"
         class="btn btn-success btn-lg px-5 py-3 fw-bold">
        <i class="bi bi-play-circle-fill me-2"></i>Join Stream Now
      </a>
      <p class="text-muted small mt-3 mb-0">
        Opens in a new tab. Make sure you are in a quiet location.
      </p>
      <?php else: ?>
      <div class="alert alert-info">
        <i class="bi bi-geo-alt me-2"></i>
        This is a physical event. Please head to the venue in:
        <strong><?= sanitize($lobby['city'] ?? 'the listed location') ?></strong>.
      </div>
      <?php endif; ?>

    <?php elseif ($status === 'upcoming'): ?>
      <div class="mb-3">
        <i class="bi bi-hourglass-split display-3 text-warning"></i>
      </div>
      <h1 class="fw-bold mb-2"><?= sanitize($title) ?></h1>
      <p class="text-muted mb-4">The event hasn't started yet. Your ticket is confirmed!</p>

      <!-- Countdown timer (JavaScript updates this) -->
      <?php if (!empty($date)): ?>
      <div id="countdown" class="display-6 fw-bold text-primary mb-4">
        <!-- filled by JS below -->
      </div>
      <p class="text-muted small">
        Starts on: <?= date('l, d F Y \a\t H:i', strtotime($date)) ?>
      </p>
      <script>
        // Simple countdown to event start time
        const eventDate = new Date("<?= str_replace(' ','T', $date) ?>");
        function tick() {
          const diff = eventDate - new Date();
          if (diff <= 0) {
            document.getElementById('countdown').innerText = 'Starting now! Refresh this page.';
            return;
          }
          const h = Math.floor(diff / 3600000);
          const m = Math.floor((diff % 3600000) / 60000);
          const s = Math.floor((diff % 60000) / 1000);
          document.getElementById('countdown').innerText =
            `${h}h ${m}m ${s}s remaining`;
        }
        tick();
        setInterval(tick, 1000);
      </script>
      <?php endif; ?>

    <?php else: /* ended */ ?>
      <div class="mb-3">
        <i class="bi bi-calendar2-x display-3 text-muted"></i>
      </div>
      <h1 class="fw-bold mb-2 text-muted"><?= sanitize($title) ?></h1>
      <p class="text-muted mb-4">This event has ended. Thank you for attending!</p>
      <a href="<?= BASE_URL ?>index.php?page=events" class="btn btn-outline-primary">Browse More Events</a>
    <?php endif; ?>

    <div class="mt-4 pt-3 border-top">
      <a href="<?= BASE_URL ?>index.php?page=events&action=show&id=<?= (int)$eventId ?>"
         class="text-muted small">← Back to Event Details</a>
    </div>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
