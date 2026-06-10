</main><!--opened in header.php -->

<footer>
  <div class="container">
    <div class="row g-4">

      <!-- Brand -->
      <div class="col-md-4">
        <h6 class="d-flex align-items-center gap-2">
          <i class="bi bi-book-half"></i> BookNest
        </h6>
        <p class="small mb-0" style="color:#adb5bd;">
          Connecting readers with independent bookstores and literary communities.
        </p>
      </div>

      <!-- Quick Links -->
      <div class="col-md-4">
        <h6>Quick Links</h6>
        <ul class="list-unstyled small mb-0">
          <li><a href="<?= BASE_URL ?>index.php?page=books"><i class="bi bi-chevron-right me-1"></i>Browse Books</a></li>
          <li><a href="<?= BASE_URL ?>index.php?page=clubs"><i class="bi bi-chevron-right me-1"></i>Book Clubs</a></li>
          <li><a href="<?= BASE_URL ?>index.php?page=events"><i class="bi bi-chevron-right me-1"></i>Events</a></li>
          <?php if (isLoggedIn()): ?>
          <li><a href="<?= BASE_URL ?>index.php?page=dashboard"><i class="bi bi-chevron-right me-1"></i>My Dashboard</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- about -->
      <div class="col-md-4">
        <h6>About</h6>
        <p class="small mb-0" style="color:#adb5bd;">
          BookNest is your ultimate destination for everything books.<br>
          We connect passionate readers with local independent bookstores.<br>
          Independent Bookstore & Reader's Club Network
        </p>
      </div>

    </div>

    <hr class="border-secondary mt-4 mb-3">

    <p class="text-center small mb-0" style="color:#6c757d;">
      &copy; 2026 BookNest-All rights reserved.
    </p>

  </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- BookNest Custom JS -->
<script src="<?= BASE_URL ?>public/assets/js/app.js"></script>


</body>
</html>
