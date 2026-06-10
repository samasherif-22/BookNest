<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$total = $pickupCount + $deliveryCount;
$pickupPct   = $total > 0 ? round(($pickupCount   / $total) * 100) : 0;
$deliveryPct = $total > 0 ? round(($deliveryCount / $total) * 100) : 0;
?>

<div class="container py-4" style="max-width:720px;">
  <h1 class="section-title mb-2">🌿Sustainability Report</h1>
  <p class="text-muted mb-4">
    BookNest encourages Click &amp; Collect to reduce carbon emissions from home delivery.
  </p>

  <!-- ── CO₂ Hero Card -->
  <div class="card border-0 shadow mb-4 p-4 text-center"
       style="background: linear-gradient(135deg, #1a5276, #27AE60); color:#fff; border-radius:16px;">
    <i class="bi bi-tree-fill mb-2" style="font-size:3rem;"></i>
    <div class="display-5 fw-bold"><?= number_format($carbonSaved, 1) ?> kg</div>
    <div style="font-size:1.1rem; opacity:.85;">CO₂ saved through local pickup</div>
    <div class="mt-2 small" style="opacity:.7;">
      Calculation: <?= (int)$pickupCount ?> pickups × 2.3 kg average CO₂ saved per order
    </div>
  </div>

  <!-- ── Stats Grid -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="stat-card text-center p-3">
        <div class="stat-number text-success"><?= number_format($pickupCount) ?></div>
        <div class="stat-label">Pickup Orders</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card text-center p-3">
        <div class="stat-number text-warning"><?= number_format($deliveryCount) ?></div>
        <div class="stat-label">Delivery Orders</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card text-center p-3">
        <div class="stat-number"><?= number_format($total) ?></div>
        <div class="stat-label">Total Orders</div>
      </div>
    </div>
  </div>

  <!-- ── Progress Bars -->
  <div class="card border-0 shadow-sm p-4">
    <h5 class="fw-bold mb-3">Order Type Breakdown</h5>

    <div class="mb-3">
      <div class="d-flex justify-content-between small mb-1">
        <span><i class="bi bi-geo-alt-fill text-success me-1"></i>Click &amp; Collect</span>
        <span><?= $pickupPct ?>%</span>
      </div>
      <div class="progress" style="height:12px;">
        <div class="progress-bar bg-success" style="width:<?= $pickupPct ?>%;"></div>
      </div>
    </div>

    <div class="mb-2">
      <div class="d-flex justify-content-between small mb-1">
        <span><i class="bi bi-truck text-warning me-1"></i>Home Delivery</span>
        <span><?= $deliveryPct ?>%</span>
      </div>
      <div class="progress" style="height:12px;">
        <div class="progress-bar bg-warning" style="width:<?= $deliveryPct ?>%;"></div>
      </div>
    </div>

    <div class="alert alert-success mt-3 mb-0 small">
      <i class="bi bi-info-circle me-2"></i>
      By choosing local pickup, our customers have collectively avoided
      <strong><?= number_format($carbonSaved, 1) ?> kg of CO₂</strong> this period —
      equivalent to planting approximately <strong><?= max(1, (int)($carbonSaved / 21)) ?> trees</strong>.
    </div>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
