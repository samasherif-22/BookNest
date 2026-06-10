<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BookNest — Connecting readers with independent bookstores and literary communities.">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' — BookNest' : 'BookNest'; ?></title>

    <!-- Bootstrap 5 CSS -->
    <!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Google Fonts (FINAL CHOICE ONLY) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">

<!-- Custom CSS -->
<link href="<?= BASE_URL ?>public/assets/css/custom.css" rel="stylesheet">
</head>
<body>

<?php
// Displayed once at top of page then removed from session ex:warnning message
$flash = getFlash();
if ($flash):
?>
<div class="alert alert-<?= sanitize($flash['type']) ?> flash-alert alert-dismissible fade show m-0 rounded-0" role="alert">
    <div class="container d-flex align-items-center gap-2">
        <?php if ($flash['type'] === 'success'): ?>
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
        <?php elseif ($flash['type'] === 'danger'): ?>
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
        <?php elseif ($flash['type'] === 'warning'): ?>
            <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
        <?php else: ?>
            <i class="bi bi-info-circle-fill flex-shrink-0"></i>
        <?php endif; ?>
        <span><?= sanitize($flash['message']) ?></span>
    </div>
    <button type="button" class="btn-close me-2" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<main>  <!--close body and main in footer.php-->
