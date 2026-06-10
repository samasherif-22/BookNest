<?php
require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-4">
  <h1 class="section-title mb-1"> Admin Dashboard</h1>
 


  <div class="row g-3 mb-4">
    <?php
    $stats = [
      ['label' => 'Total Users',    'value' => $userCount,  'icon' => 'bi-people-fill',   'color' => '#2980B9'],
      ['label' => 'Books Listed',   'value' => $bookCount,  'icon' => 'bi-book-fill',      'color' => '#27AE60'],
      ['label' => 'Orders Placed',  'value' => $orderCount, 'icon' => 'bi-bag-fill',       'color' => '#E67E22'],
      ['label' => 'Active Clubs',   'value' => $clubCount,  'icon' => 'bi-people',         'color' => '#8e44ad'],
      ['label' => 'Events',         'value' => $eventCount, 'icon' => 'bi-calendar-event', 'color' => '#2980B9'],
      ['label' => 'Partner Stores', 'value' => $storeCount, 'icon' => 'bi-shop',           'color' => '#c0392b'],
    ];
    foreach ($stats as $s):
    ?>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="stat-card text-center p-3">
        <i class="bi <?= $s['icon'] ?> mb-1" style="font-size:1.8rem; color:<?= $s['color'] ?>;"></i>
        <div class="stat-number"><?= number_format($s['value']) ?></div>
        <div class="stat-label"><?= $s['label'] ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <! Alerts: Pending Stores + Open Disputes >
  <div class="row g-3 mb-4">
    <?php if (!empty($pendingStores)): ?>
    <div class="col-md-6">
      <div class="alert alert-warning d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-shop fs-5"></i>
        <div>
          <strong><?= count($pendingStores) ?> store application(s) waiting for approval.</strong>
          <a href="<?= BASE_URL ?>index.php?page=admin&action=stores" class="ms-2 fw-bold">Review Now →</a>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($openDisputes)): ?>
    <div class="col-md-6">
      <div class="alert alert-danger d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-exclamation-triangle fs-5"></i>
        <div>
          <strong><?= count($openDisputes) ?> open dispute(s) need resolution.</strong>
          <a href="<?= BASE_URL ?>index.php?page=admin&action=disputes" class="ms-2 fw-bold">Resolve →</a>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <! Admin Navigation Cards>
  <div class="row g-3">
    <?php
    $sections = [
      ['icon'=>'bi-people-fill',       'color'=>'#2980B9', 'title'=>'User Management',
       'desc'=>'Search, list, and change roles.',        'link'=>'admin&action=users'],
      ['icon'=>'bi-shop',              'color'=>'#E67E22', 'title'=>'Store Approvals',
       'desc'=>'Approve or reject store applications.',  'link'=>'admin&action=stores'],
      ['icon'=>'bi-exclamation-circle','color'=>'#c0392b', 'title'=>'Disputes',
       'desc'=>'Resolve open order disputes.',           'link'=>'admin&action=disputes'],
      ['icon'=>'bi-bar-chart-line',    'color'=>'#27AE60', 'title'=>'Reports',
       'desc'=>'Sales, payout and inventory summaries.', 'link'=>'reports'],
      ['icon'=>'bi-shield-check',      'color'=>'#8e44ad', 'title'=>'Audit Trail',
       'desc'=>'View full system audit log.',            'link'=>'admin&action=auditLog'],
      ['icon'=>'bi-leaf',              'color'=>'#27AE60', 'title'=>'Sustainability',
       'desc'=>'CO₂ savings from local pickups.',        'link'=>'admin&action=sustainabilityReport'],
    ];
    foreach ($sections as $sec):
    ?>
    <div class="col-md-4 col-lg-4">
      <a href="<?= BASE_URL ?>index.php?page=<?= $sec['link'] ?>"
         class="card border-0 shadow-sm h-100 text-decoration-none hover-lift">
        <div class="card-body p-4">
          <i class="bi <?= $sec['icon'] ?> mb-2" style="font-size:2rem; color:<?= $sec['color'] ?>;"></i>
          <h5 class="fw-bold mb-1"><?= $sec['title'] ?></h5>
          <p class="text-muted small mb-0"><?= $sec['desc'] ?></p>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
