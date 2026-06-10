<?php
/*
 * New user registration form. POST goes to AuthController::register().
 * Users can self-select a role (except SYSTEM_ADMIN).
 */
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<section class="d-flex align-items-center justify-content-center py-5" style="min-height:85vh; background:var(--bg-light);">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">

        <!-- Title -->
        <div class="text-center mb-4">
          <i class="bi bi-person-plus" style="font-size:3rem; color:var(--primary);"></i>
          <h1 class="h3 fw-bold mt-2" style="color:var(--primary);">Create Your Account</h1>
          <p class="text-muted small">Join the BookNest community today</p>
        </div>

        <!-- Registration Card -->
        <div class="card border-0 shadow-lg rounded-xl p-4">
          <div class="card-body p-0">
            <form method="POST" action="index.php?page=register">

              <!-- Full Name -->
              <div class="mb-3">
                <label for="name" class="form-label fw-semibold small text-uppercase text-muted">Full Name</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-person text-muted"></i>
                  </span>
                  <input type="text" id="name" name="name"
                         class="form-control border-start-0 ps-0"
                         placeholder="Your full name" required>
                </div>
              </div>

              <!-- Email -->
              <div class="mb-3">
                <label for="email" class="form-label fw-semibold small text-uppercase text-muted">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-envelope text-muted"></i>
                  </span>
                  <input type="email" id="email" name="email"
                         class="form-control border-start-0 ps-0"
                         placeholder="you@email.com" required>
                </div>
              </div>

              <!-- Password -->
              <div class="mb-3">
                <label for="password" class="form-label fw-semibold small text-uppercase text-muted">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-lock text-muted"></i>
                  </span>
                  <input type="password" id="password" name="password"
                         class="form-control border-start-0 ps-0"
                         placeholder="Minimum 8 characters" required minlength="8">
                </div>
              </div>

              <!-- Role Selection -->
              <div class="mb-4">
                <label for="role" class="form-label fw-semibold small text-uppercase text-muted">I am a…</label>
                <select id="role" name="role" class="form-select">
                  <option value="READER">Reader</option>
                  <option value="BOOKSTORE_OWNER">Bookstore Owner</option>
                  <option value="CLUB_ORGANIZER">Club Organizer</option>
                  <option value="AUTHOR"> Author </option>
                </select>
                <div class="form-text small">
                  Choose the role that best describes you. You can request role changes from your dashboard later.
                </div>
              </div>

              <!-- Submit -->
              <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                <i class="bi bi-person-check me-2"></i>Create Account
              </button>

            </form>
          </div>
        </div>

        <p class="text-center text-muted small mt-4 mb-0">
          Already have an account?
         <a href="index.php?page=login" class="fw-bold" style="color:var(--accent);">Sign In</a>
        </p>

      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
