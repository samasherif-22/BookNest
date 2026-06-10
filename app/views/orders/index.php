<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

// Badge colors for each order status
$statusBadge = [
    'placed'    => 'bg-primary',
    'ready'     => 'bg-warning text-dark',
    'collected' => 'bg-success',
    'cancelled' => 'bg-danger',
];
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="section-title mb-0"> My Orders</h1>
    <a href="<?= BASE_URL ?>index.php?page=books" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Continue Shopping
    </a>
  </div>

  <?php if (empty($orders)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-bag-x display-4 d-block mb-3"></i>
      <p class="lead">You haven't placed any orders yet.</p>
      <a href="<?= BASE_URL ?>index.php?page=books" class="btn btn-primary">Browse Books</a>
    </div>
  <?php else: ?>
  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>#Order</th>
            <th>Store</th>
            <th>Type</th>
            <th class="text-end">Total</th>
            <th class="text-center">Status</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
          <tr>
            <td class="fw-bold">#<?= (int)$order['id'] ?></td>
            <td><?= sanitize($order['store_name'] ?? '—') ?></td>
            <td>
              <?php if ($order['type'] === 'pickup'): ?>
                <span class="badge bg-info"><i class="bi bi-geo-alt me-1"></i>Pickup</span>
              <?php else: ?>
                <span class="badge bg-secondary"><i class="bi bi-truck me-1"></i>Delivery</span>
              <?php endif; ?>
            </td>
            <td class="text-end fw-bold">
              EGP <?= number_format($order['total'] > 0 ? $order['total'] : $order['subtotal'], 2) ?>
            </td>
            <td class="text-center">
              <span class="badge <?= $statusBadge[$order['status']] ?? 'bg-secondary' ?>">
                <?= ucfirst(sanitize($order['status'])) ?>
              </span>
            </td>
            <td class="text-muted small"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
            <td>
              <a href="<?= BASE_URL ?>index.php?page=orders&action=show&id=<?= (int)$order['id'] ?>"
                 class="btn btn-sm btn-outline-primary">Details</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>
