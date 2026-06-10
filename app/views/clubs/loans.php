<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-4">
  <h1 class="section-title mb-4">Lending Library</h1>

  <!-- ──  Full-Width Lend Book Form -->
  <div class="card border-0 shadow-sm mb-5 border-start border-4 border-primary">
    <div class="card-body p-4">
      <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-bookmark-heart me-2"></i>Lend a Book to a Friend</h5>
      
      <?php 
        
        $prefillBorrower = (int)($_GET['prefill_borrower'] ?? 0);
        $prefillBook     = (int)($_GET['prefill_book'] ?? 0);
      ?>

      <form method="POST" action="<?= BASE_URL ?>index.php?page=loans&action=lendBook" class="row g-3 align-items-end">
        
        <!-- Select Member -->
        <div class="col-md-4">
          <label class="form-label small fw-semibold text-muted mb-1">Select Member</label>
          <select name="borrower_id" class="form-select" required>
            <option value="" <?= !$prefillBorrower ? 'selected' : '' ?> disabled>Who are you lending to?</option>
            <?php if(!empty($usersList)): foreach ($usersList as $u): ?>
              <option value="<?= (int)$u['id'] ?>" <?= $prefillBorrower === (int)$u['id'] ? 'selected' : '' ?>>
                <?= sanitize($u['name']) ?>
              </option>
            <?php endforeach; endif; ?>
          </select>
        </div>

        <!-- Select Book -->
        <div class="col-md-4">
          <label class="form-label small fw-semibold text-muted mb-1">Select Your Book</label>
          <select name="book_id" class="form-select" required>
            <option value="" <?= !$prefillBook ? 'selected' : '' ?> disabled>Which book are you lending?</option>
            <?php if(!empty($booksList)): foreach ($booksList as $b): ?>
              <option value="<?= (int)$b['id'] ?>" <?= $prefillBook === (int)$b['id'] ? 'selected' : '' ?>>
                <?= sanitize($b['title']) ?>
              </option>
            <?php endforeach; endif; ?>
          </select>
        </div>

        <!-- Due Date -->
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-muted mb-1">Return Due Date</label>
          <input type="date" name="due_date" class="form-control" required>
        </div>

        <!-- Submit Button -->
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
            <i class="bi bi-send me-1"></i> Confirm Loan
          </button>
        </div>

      </form>
    </div>
  </div>

  <!-- ──  Lending & Borrowing Lists  -->
  <div class="row g-4">
    
    <!-- Books I Am Lending -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white fw-bold py-3">
          <i class="bi bi-arrow-up-right-circle text-success me-2"></i>Books I'm Lending
        </div>
        <div class="card-body p-0">
          <?php if (empty($lent)): ?>
            <p class="text-muted text-center p-4 mb-0">You are not lending any books.</p>
          <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($lent as $loan): ?>
            <div class="list-group-item p-3">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <strong class="d-block mb-1"><?= sanitize($loan['book_title']) ?></strong>
                  <div class="text-muted small"><i class="bi bi-person me-1"></i>To: <?= sanitize($loan['borrower_name']) ?></div>
                  <div class="text-muted small"><i class="bi bi-calendar-event me-1"></i>Due: <?= date('d M Y', strtotime($loan['due_date'])) ?></div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                  <?php if ($loan['returned']): ?>
                    <span class="badge bg-success">Returned</span>
                  <?php elseif ($loan['is_overdue']): ?>
                    <span class="badge bg-danger">Overdue</span>
                  <?php else: ?>
                    <span class="badge bg-primary">Active</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Books I Am Borrowing -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white fw-bold py-3">
          <i class="bi bi-arrow-down-left-circle text-info me-2"></i>Books I'm Borrowing
        </div>
        <div class="card-body p-0">
          <?php if (empty($borrowed)): ?>
            <p class="text-muted text-center p-4 mb-0">You are not borrowing any books.</p>
          <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($borrowed as $loan): ?>
            <div class="list-group-item p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <strong class="d-block mb-1"><?= sanitize($loan['book_title']) ?></strong>
                  <div class="text-muted small"><i class="bi bi-person me-1"></i>From: <?= sanitize($loan['lender_name']) ?></div>
                  <div class="text-muted small"><i class="bi bi-calendar-event me-1"></i>Due: <?= date('d M Y', strtotime($loan['due_date'])) ?></div>
                </div>
                <?php if (!$loan['returned']): ?>
                <form method="POST" action="<?= BASE_URL ?>index.php?page=loans&action=returnBook">
                  <input type="hidden" name="loan_id" value="<?= (int)$loan['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-success shadow-sm">Mark Returned</button>
                </form>
                <?php else: ?>
                  <span class="badge bg-success">Returned</span>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>