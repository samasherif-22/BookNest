<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$isEdit = ($book !== null);
$action = $isEdit
    ? BASE_URL . 'index.php?page=books&action=update'
    : BASE_URL . 'index.php?page=books&action=store';
?>

<div class="container py-4" style="max-width:680px;">
    <! Stylish Breadcrumbs Section (Bootstrap) >
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-white p-3 shadow-sm border rounded-pill" style="--bs-breadcrumb-divider: '›';">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>index.php?page=books" class="text-decoration-none d-inline-flex align-items-center fw-bold text-primary">
                    <i class="bi bi-grid-fill me-2"></i> Books
                </a>
            </li>
            <li class="breadcrumb-item active d-inline-flex align-items-center text-secondary fw-semibold" aria-current="page">
                
                <i class="bi <?= $isEdit ? 'bi-pencil-square' : 'bi-plus-circle-fill' ?> me-2"></i> 
                <?= $isEdit ? 'Edit Book' : 'Add New Book' ?>
            </li>
        </ol>
    </nav>

    <h1 class="section-title mb-4"><?= $isEdit ? 'Edit Book' : 'Add New Book' ?></h1>

    <div class="card border-0 shadow p-4">
        <form method="POST" action="<?= $action ?>" enctype="multipart/form-data">
            
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Book Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control"
                           value="<?= sanitize($book['title'] ?? '') ?>" required placeholder="e.g. The Alchemist">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Author Name <span class="text-danger">*</span></label>
                    <input type="text" name="author_name" class="form-control"
                           value="<?= sanitize($book['author_name'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">ISBN</label>
                    <input type="text" name="isbn" class="form-control"
                           value="<?= sanitize($book['isbn'] ?? '') ?>" placeholder="978-...">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Genre <span class="text-danger">*</span></label>
                    <select name="genre" class="form-select" required>
                        <option value="" disabled <?= !$isEdit ? 'selected' : '' ?>>Select Genre</option>
                        <?php
                        $genres = ['Fiction','Non-Fiction','Mystery','Sci-Fi','Fantasy','Romance',
                                   'History','Biography','horror','Poetry','Children','Other'];
                        foreach ($genres as $g):
                            $sel = (($book['genre'] ?? '') === $g) ? 'selected' : '';
                        ?>
                            <option value="<?= $g ?>" <?= $sel ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Used-Book Grading <span class="text-danger">*</span></label>
                    <select name="condition_grade" class="form-select" required>
                        <?php 
                        $grades = [
                            'fine' => 'Fine — Like new',
                            'good' => 'Good — Readable',
                            'fair' => 'Fair — Visible wear'
                        ];
                        foreach ($grades as $val => $label): 
                        ?>
                            <option value="<?= $val ?>" <?= (($book['condition_grade'] ?? 'fine') === $val) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Book Cover Image</label>
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                    <?php if ($isEdit && !empty($book['cover_url'])): ?>
                        <div class="form-text text-success d-flex align-items-center mt-2">
                            <i class="bi bi-check-circle-fill me-1"></i> 
                            Current file: <span class="badge bg-light text-dark ms-1"><?= basename($book['cover_url']) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="form-text">Recommended: JPG or PNG, max 2MB.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Base Price (EGP) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">EGP</span>
                        <input type="number" name="base_price" class="form-control"
                               value="<?= sanitize($book['base_price'] ?? '') ?>"
                               step="0.01" min="0" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Stock Quantity</label>
                    <input type="number" name="stock_qty" class="form-control"
                           value="<?= (int)($book['stock_qty'] ?? 1) ?>" min="0" required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="4"
                              placeholder="Write a short summary..."><?= sanitize($book['description'] ?? '') ?></textarea>
                </div>

                <div class="col-12 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Save Changes' : 'Add Book' ?>
                    </button>
                    <a href="<?= BASE_URL ?>index.php?page=books" class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>