<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$statusBadge = [
    'placed'    => 'bg-primary',
    'ready'     => 'bg-warning text-dark',
    'collected' => 'bg-success',
    'cancelled' => 'bg-danger',
];

// Status transition options for owners (F2 — Click-and-Collect workflow)
$nextStatuses = [
    'placed'    => ['ready' => 'Mark Ready', 'cancelled' => 'Cancel Order'],
    'ready'     => ['collected' => 'Mark Collected'],
    'collected' => [],
    'cancelled' => [],
];
$current = $order['status'];
?>

<div class="container py-4" style="max-width:800px;">
  
 
  <div class="mb-4">
    <a href="<?= BASE_URL ?>index.php?page=orders" class="text-decoration-none d-inline-flex align-items-center gap-1 small fw-medium" style="color: var(--primary); opacity: 0.8;">
      <i class="bi bi-arrow-left-short fs-5"></i>
      <span>Back to My Orders</span>
    </a>
  </div>

  <div class="card border-0 shadow-sm mb-4 p-4">
    <div class="row g-3 align-items-center">
      <div class="col-md-7">
        <h1 class="h4 fw-bold mb-1" style="color:var(--primary);">
          Order #<?= (int)$order['id'] ?>
        </h1>
        <p class="text-muted mb-0 small">
          Placed on <?= date('d M Y H:i', strtotime($order['created_at'])) ?>
        </p>
        <?php if (!empty($order['customer_name'])): ?>
        <p class="mb-0 small mt-1">
          <i class="bi bi-person me-1"></i>Customer: <strong><?= sanitize($order['customer_name']) ?></strong>
        </p>
        <?php endif; ?>
      </div>
      <div class="col-md-5 text-md-end">
        <span class="badge <?= $statusBadge[$current] ?? 'bg-secondary' ?> px-3 py-2" style="font-size:.95rem;">
          <?= ucfirst($current) ?>
        </span>
        <div class="mt-2 text-muted small">
          <?php if ($order['type'] === 'pickup'): ?>
            <i class="bi bi-geo-alt me-1"></i>Click &amp; Collect
          <?php else: ?>
            <i class="bi bi-truck me-1"></i>Home Delivery
          <?php endif; ?>
          &nbsp;|&nbsp; Store: <strong><?= sanitize($order['store_name'] ?? '—') ?></strong>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">Items Ordered</div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Book</th><th>Author</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td class="fw-bold small"><?= sanitize($item['title'] ?? '—') ?></td>
            <td class="text-muted small"><?= sanitize($item['author_name'] ?? '—') ?></td>
            <td class="text-center"><?= (int)$item['qty'] ?></td>
            <td class="text-end">EGP <?= number_format($item['unit_price'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light">
          <tr>
            <td colspan="3" class="text-end fw-bold">Subtotal</td>
            <td class="text-end fw-bold">EGP <?= number_format($order['subtotal'], 2) ?></td>
          </tr>
          
         

          <tr>
            <td colspan="3" class="text-end fw-bold text-success">Total</td>
            <td class="text-end fw-bold text-success" style="font-size: 1.1rem;">
              EGP <?= number_format($order['subtotal'], 2) ?>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <?php if (currentRole() === 'READER' && $order['status'] === 'collected'): ?>
  <div class="mt-4 mb-4 text-end">
      <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#disputeModal">
          <i class="bi bi-exclamation-triangle me-1"></i> Report an Issue
      </button>
  </div>

  <div class="modal fade text-start" id="disputeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="<?= BASE_URL ?>index.php?page=disputes&action=create" method="POST">
          <div class="modal-header bg-danger text-white border-0">
            <h5 class="modal-title"><i class="bi bi-shield-exclamation me-2"></i> Open a Dispute</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body">
            <p class="small text-muted mb-3">
              Having trouble with Order #<?= (int)$order['id'] ?>? Let us know and our admin team will review it.
            </p>
            
            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
            
            <div class="mb-3">
              <label class="form-label fw-bold small">Reason for Dispute <span class="text-danger">*</span></label>
              <textarea name="description" class="form-control" rows="4" 
                        placeholder="Describe the issue (e.g., book arrived damaged, wrong item received...)" required></textarea>
            </div>
          </div>
          
          <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger btn-sm">Submit Dispute</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array(currentRole(), ['BOOKSTORE_OWNER', 'SYSTEM_ADMIN']) && !empty($nextStatuses[$current])): ?>
  <div class="card border-0 shadow-sm p-4">
    <h5 class="fw-bold mb-3">Update Order Status</h5>
    <div class="d-flex gap-2 flex-wrap">
      <?php foreach ($nextStatuses[$current] as $status => $label): ?>
      <form method="POST" action="<?= BASE_URL ?>index.php?page=orders&action=updateStatus">
        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
        <input type="hidden" name="status" value="<?= $status ?>">
        <?php $btnClass = $status === 'cancelled' ? 'btn-outline-danger' : 'btn-primary'; ?>
        <button type="submit" class="btn <?= $btnClass ?>"
                <?= $status === 'cancelled' ? 'onclick="return confirm(\'Cancel this order?\')"' : '' ?>>
          <?= sanitize($label) ?>
        </button>
      </form>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php
 require_once BASE_PATH . '/app/views/partials/footer.php'; 
?>