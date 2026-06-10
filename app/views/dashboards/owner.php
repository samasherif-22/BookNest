<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$statusBadge = [
    'placed'    => 'bg-primary',
    'ready'     => 'bg-warning text-dark',
    'collected' => 'bg-success',
    'cancelled' => 'bg-danger',
];
?>

<div class="container py-4">
  <h1 class="section-title mb-1">Store Dashboard</h1>

  <?php if (!$store): ?>
  <div class="alert alert-warning mt-3">
    <i class="bi bi-exclamation-triangle me-2"></i>
    You don't have an approved store yet.
    <a href="<?= BASE_URL ?>index.php?page=admin&action=applyStore" class="fw-bold">Apply for a store</a>
    and wait for admin approval.
  </div>
  <?php else: ?>

  <p class="text-muted mb-4"><?= sanitize($store['name']) ?> — <?= sanitize($store['city'] ?? '') ?></p>

  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="stat-card text-center p-3">
        <div class="stat-number"><?= count($orders) ?></div>
        <div class="stat-label">Total Orders</div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card text-center p-3">
        <div class="stat-number"><?= count($books) ?></div>
        <div class="stat-label">Books Listed</div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card text-center p-3">
        <div class="stat-number text-success">
          EGP <?= number_format($pendingPayout, 2) ?>
        </div>
        <div class="stat-label">Pending Payout</div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card text-center p-3">
        <div class="stat-number">
          <?= count(array_filter($orders, fn($o) => $o['status'] === 'placed')) ?>
        </div>
        <div class="stat-label">New Orders</div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 flex-wrap mb-4">
    <a href="<?= BASE_URL ?>index.php?page=books&action=create" class="btn btn-primary btn-sm">
      <i class="bi bi-plus me-1"></i>Add Book
    </a>
    <a href="<?= BASE_URL ?>index.php?page=orders" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-bag me-1"></i>All Orders
    </a>
    <a href="<?= BASE_URL ?>index.php?page=reports&action=salesReport" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-file-earmark-bar-graph me-1"></i>Sales Report
    </a>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold"> Recent Orders</div>
        <?php if (empty($orders)): ?>
        <div class="card-body text-center text-muted py-4">No orders yet.</div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr>
              <th>#</th><th>Customer</th><th>Total</th>
              <th class="text-center">Status</th><th>Action</th>
            </tr></thead>
            <tbody>
            <?php foreach (array_slice($orders, 0, 10) as $order): ?>
            <tr>
              <td class="fw-bold">#<?= (int)$order['id'] ?></td>
              <td class="small"><?= sanitize($order['customer_name'] ?? '—') ?></td>
              <td>EGP <?= number_format($order['total'] ?? $order['subtotal'], 2) ?></td>
              <td class="text-center">
                <span class="badge <?= $statusBadge[$order['status']] ?? 'bg-secondary' ?>">
                  <?= ucfirst($order['status']) ?>
                </span>
              </td>
              <td>
                <?php if ($order['status'] === 'placed'): ?>
                <form method="POST" action="<?= BASE_URL ?>index.php?page=orders&action=updateStatus"
                      class="d-inline">
                  <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                  <input type="hidden" name="status" value="ready">
                  <button class="btn btn-sm btn-warning py-0">Mark Ready</button>
                </form>
                <?php elseif ($order['status'] === 'ready'): ?>
                <form method="POST" action="<?= BASE_URL ?>index.php?page=orders&action=updateStatus"
                      class="d-inline">
                  <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                  <input type="hidden" name="status" value="collected">
                  <button class="btn btn-sm btn-success py-0">Collected</button>
                </form>
                <?php else: ?>
                <a href="<?= BASE_URL ?>index.php?page=orders&action=show&id=<?= (int)$order['id'] ?>"
                   class="btn btn-sm btn-outline-secondary py-0">View</a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
          My Books
          <a href="<?= BASE_URL ?>index.php?page=books&action=create"
             class="btn btn-sm btn-outline-primary py-0">Add</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($books)): ?>
          <p class="text-muted text-center p-3">No books listed yet.</p>
          <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach (array_slice($books, 0, 8) as $b): ?>
            <a href="<?= BASE_URL ?>index.php?page=books&action=show&id=<?= (int)$b['id'] ?>"
               class="list-group-item list-group-item-action py-2 px-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="small fw-bold"><?= sanitize(mb_strimwidth($b['title'],0,30,'…')) ?></div>
                  <div class="text-muted" style="font-size:.75rem;">
                    EGP <?= number_format($b['final_price'],2) ?>
                  </div>
                </div>
                <span class="badge <?= (int)$b['stock_qty'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                  <?= (int)$b['stock_qty'] ?> left
                </span>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php endif; // store exists ?>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>