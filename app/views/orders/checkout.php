<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$total = array_sum(array_column($cartItems, 'final_price'));
?>

<div class="container py-4" style="max-width:820px;">
  <h1 class="section-title mb-4">Checkout</h1>

  <div class="row g-4">
    <!-- ── Cart Items ───────────────────────────────────────────────────── -->
    <div class="col-md-7">
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-bold">Cart Items</div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Book</th>
                <th>Condition</th>
                <th class="text-end">Price</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cartItems as $item): ?>
              <tr>
                <td>
                  <div class="fw-bold small"><?= sanitize($item['title']) ?></div>
                  <div class="text-muted" style="font-size:.8rem;">by <?= sanitize($item['author_name']) ?></div>
                </td>
                <td>
                  <span class="badge <?= conditionBadgeClass($item['condition_grade']) ?>">
                    <?= sanitize($item['condition_grade']) ?>
                  </span>
                </td>
                <td class="text-end fw-bold">EGP <?= number_format($item['final_price'], 2) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
              <tr>
                <td colspan="2" class="fw-bold text-end">Subtotal:</td>
                <td class="fw-bold text-end">EGP <?= number_format($total, 2) ?></td>
              </tr>
            
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Clear Cart Button -->
      <form method="POST" action="<?= BASE_URL ?>index.php?page=orders&action=clearCart">
        <button type="submit" class="btn btn-outline-secondary btn-sm"
                onclick="return confirm('Clear your entire cart?')">
          <i class="bi bi-trash me-1"></i>Clear Cart
        </button>
      </form>
    </div>

    <!-- ── Order Summary & Options ─────────────────────────────────────── -->
    <div class="col-md-5">
      <div class="card border-0 shadow-sm p-4">
        <h5 class="fw-bold mb-3">Order Options</h5>

        <form method="POST" action="<?= BASE_URL ?>index.php?page=orders&action=placeOrder">

          <!-- Order Type -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Collection Method</label>
            <div class="d-grid gap-2">
              <div class="form-check border rounded p-3 mb-1" style="cursor:pointer;">
                <input class="form-check-input" type="radio" name="order_type"
                       id="pickup" value="pickup" checked>
                <label class="form-check-label w-100" for="pickup" style="cursor:pointer;">
                  <i class="bi bi-geo-alt-fill me-2 text-success"></i>
                  <strong>Click &amp; Collect</strong>
                  <div class="text-muted small">Pick up from the store (saves CO₂)</div>
                </label>
              </div>
              <div class="form-check border rounded p-3" style="cursor:pointer;">
                <input class="form-check-input" type="radio" name="order_type"
                       id="delivery" value="delivery">
                <label class="form-check-label w-100" for="delivery" style="cursor:pointer;">
                  <i class="bi bi-truck me-2 text-primary"></i>
                  <strong>Home Delivery</strong>
                  <div class="text-muted small">Delivered to your address</div>
                </label>
              </div>
            </div>
          </div>

          <!-- Order Total Summary -->
          <div class="bg-light rounded p-3 mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Items (<?= count($cartItems) ?>)</span>
              <span>EGP <?= number_format($total, 2) ?></span>
            </div>
            
            <hr class="my-2">
            <div class="d-flex justify-content-between fw-bold">
              <span>Total</span>
              <span class="text-success" style="font-size: 1.2rem;">EGP <?= number_format($total, 2) ?></span>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
            <i class="bi bi-bag-check me-2"></i>Place Order
          </button>
        </form>
      </div>

      <div class="alert alert-info mt-3 small py-2 px-3">
        <i class="bi bi-shield-check me-1"></i>
        Single-store checkout: all cart items must be from the same bookstore.
      </div>
    </div>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>