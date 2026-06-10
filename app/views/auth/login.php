<?php

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<section class="d-flex align-items-center justify-content-center py-5" style="min-height:85vh; background-color: #f8f9fa;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5 col-lg-4">

        <div class="text-center mb-4">
          <div class="mb-3">
            <i class="bi bi-book-half text-primary" style="font-size:3.5rem;"></i>
          </div>
          <h1 class="h3 fw-bold text-dark">Welcome Back</h1>
          <p class="text-muted small">Sign in to your account</p>
        </div>

        <?php $flash = getFlash(); if ($flash): ?>
          <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle' ?> me-2"></i>
            <?= $flash['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <div class="card border-0 shadow-lg rounded-4 p-4">
          <div class="card-body p-0">
            <form method="POST" action="index.php?page=login" novalidate>
              <div class="mb-3">
                <label for="email" class="form-label fw-bold small text-uppercase text-muted">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                  <input type="email" id="email" name="email" class="form-control border-start-0 ps-0" placeholder="you@email.com" required>
                </div>
              </div>

              <div class="mb-4">
                <label for="password" class="form-label fw-bold small text-uppercase text-muted">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                  <input type="password" id="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                </div>
              </div>

              <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
              </button>
            </form>
          </div>
        </div>

        <p class="text-center text-muted small mt-4">
          Don't have an account? 
          <a href="index.php?page=register" class="fw-bold text-primary text-decoration-none">Create one free</a>
        </p>

      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>