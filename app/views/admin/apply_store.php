<?php
require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';
?>

<div class="container py-5" style="max-width: 600px;">
    <h1 class="section-title mb-4">Apply to Open a Store</h1>
    
    <div class="card border-0 shadow-sm p-4">
        <form method="POST" action="<?= BASE_URL ?>index.php?page=admin&action=applyStore">
            
            <div class="mb-3">
                <label class="form-label fw-bold">Store Name <span class="text-danger">*</span></label>
                <input type="text" name="store_name" class="form-control" required placeholder="e.g. Bookchino">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">City <span class="text-danger">*</span></label>
                <input type="text" name="city" class="form-control" required placeholder="e.g. Cairo">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Store Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Tell us about your bookstore..."></textarea>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Submit Application</button>
            </div>
        </form>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>