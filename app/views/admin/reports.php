<?php
/*
 * app/views/admin/reports.php
 * ----------------------------
 * Platform sales and payout report. Also serves inventory report.
 * Variables: $store (if owner), $orders, $payout, $reportType, $books
 */
require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$reportType = $reportType ?? 'menu';
?>

<div class="container py-4">
  <h1 class="section-title mb-4">Reports</h1>

  <!-- Report Choose Menu -->
  <?php if ($reportType === 'menu'): ?>
  <div class="row g-3">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm text-center p-4 h-100">
        <i class="bi bi-graph-up display-4 text-success mb-3"></i>
        <h5 class="fw-bold">Sales Report</h5>
        <p class="text-muted small">All orders, totals, and payout breakdown.</p>
        <a href="<?= BASE_URL ?>index.php?page=reports&action=salesReport"
           class="btn btn-outline-success">View Sales</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm text-center p-4 h-100">
        <i class="bi bi-boxes display-4 text-primary mb-3"></i>
        <h5 class="fw-bold">Inventory Report</h5>
        <p class="text-muted small">Stock levels for all books in your store.</p>
        <a href="<?= BASE_URL ?>index.php?page=reports&action=inventoryReport"
           class="btn btn-outline-primary">View Inventory</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm text-center p-4 h-100">
        <i class="bi bi-leaf display-4 text-success mb-3"></i>
        <h5 class="fw-bold">Sustainability Report</h5>
        <p class="text-muted small">CO₂ savings from local pickups.</p>
        <a href="<?= BASE_URL ?>index.php?page=admin&action=sustainabilityReport"
           class="btn btn-outline-success">View Report</a>
      </div>
    </div>
  </div>

  <?php elseif ($reportType === 'sales'): ?>
  <!Sales Report>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Sales Report — <?= sanitize($store['name'] ?? 'All Stores') ?></h4>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
      <i class="bi bi-printer me-1"></i>Print / Save PDF
    </button>
  </div>

  <!Payout Summary >
  <?php if (!empty($payout)): ?>
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="stat-card text-center p-3">
        <div class="stat-number text-primary">EGP <?= number_format($payout['total_gross'] ?? 0, 2) ?></div>
        <div class="stat-label">Total Gross Revenue</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card text-center p-3">
        <div class="stat-number text-danger">EGP <?= number_format($payout['total_commission'] ?? 0, 2) ?></div>
        <div class="stat-label">Platform Commission (10%)</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card text-center p-3">
        <div class="stat-number text-success">EGP <?= number_format($payout['total_net'] ?? 0, 2) ?></div>
        <div class="stat-label">Your Net Payout</div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr>
          <th>#Order</th><th>Customer</th><th>Type</th><th>Status</th>
          <th class="text-end">Subtotal</th><th class="text-end">Tax</th><th class="text-end">Total</th>
        </tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td>#<?= (int)$o['id'] ?></td>
            <td><?= sanitize($o['customer_name'] ?? '—') ?></td>
            <td><?= sanitize($o['type']) ?></td>
            <td><span class="badge bg-secondary"><?= ucfirst($o['status']) ?></span></td>
            <td class="text-end">EGP <?= number_format($o['subtotal'],2) ?></td>
            <td class="text-end">EGP <?= number_format($o['tax_amount'] ?? 0, 2) ?></td>
            <td class="text-end fw-bold">EGP <?= number_format($o['total'] > 0 ? $o['total'] : $o['subtotal'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php elseif ($reportType === 'inventory'): ?>
  <! Inventory Report>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Inventory — <?= sanitize($store['name'] ?? 'My Store') ?></h4>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
      <i class="bi bi-printer me-1"></i>Print / Save PDF
    </button>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr>
          <th>#</th><th>Title</th><th>Author</th><th>Genre</th>
          <th>Condition</th><th class="text-end">Price</th><th class="text-center">Stock</th>
        </tr></thead>
        <tbody>
          <?php foreach ($books as $b): ?>
          <tr>
            <td class="text-muted small"><?= (int)$b['id'] ?></td>
            <td class="fw-bold"><?= sanitize($b['title']) ?></td>
            <td class="small"><?= sanitize($b['author_name']) ?></td>
            <td><?= sanitize($b['genre'] ?? '—') ?></td>
            <td><span class="badge <?= conditionBadgeClass($b['condition_grade']) ?>">
              <?= sanitize($b['condition_grade']) ?></span></td>
            <td class="text-end">EGP <?= number_format($b['final_price'],2) ?></td>
            <td class="text-center">
              <span class="badge <?= (int)$b['stock_qty'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                <?= (int)$b['stock_qty'] ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

<div class="mt-3">
    <a href="<?= BASE_URL ?>index.php?page=dashboard" class="btn btn-outline-secondary btn-sm">
      ← Back to dashboard
    </a>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
