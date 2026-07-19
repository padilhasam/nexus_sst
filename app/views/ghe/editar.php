<?php
$css = 'ghe.css';
require_once dirname(__DIR__) . '/templates/header.php';

$ghe = $ghe ?? [];
$hierarquias = $hierarquias ?? [];
?>

<div class="ghe-page">
    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger rounded-4 alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?= htmlspecialchars($_SESSION['erro']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <header class="ghe-page-header">
        <div>
            <h2>Editar GHE</h2>
            <p>Atualize a identificação e os cargos vinculados ao grupo.</p>
        </div>
        <a href="<?= BASE_URL ?>/ghe/visualizar/<?= (int)$ghe['id'] ?>" class="btn btn-outline-secondary ghe-new-button">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </header>

    <?php require __DIR__ . '/formulario.php'; ?>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
