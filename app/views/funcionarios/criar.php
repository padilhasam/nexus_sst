<?php
$css = 'funcionarios.css';
require_once dirname(__DIR__) . '/templates/header.php';

$empresas = $empresas ?? [];
$unidades = $unidades ?? [];
$hierarquias = $hierarquias ?? [];
$dados = $dadosAnteriores ?? [];
$csrfToken = $csrfToken ?? '';
?>

<div class="funcionarios-page">
    <header class="funcionarios-header">
        <div>
            <h2>Novo funcionário</h2>
            <p>Vincule o trabalhador à empresa, unidade, setor e cargo corretos.</p>
        </div>
        <a href="<?= BASE_URL ?>/funcionarios" class="btn btn-outline-secondary funcionarios-new-button">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </header>

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger rounded-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['erro']) ?>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/funcionarios/salvar" class="funcionario-form-card">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <?php require __DIR__ . '/formulario.php'; ?>
        <div class="funcionario-form-actions">
            <a href="<?= BASE_URL ?>/funcionarios" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar funcionário</button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
