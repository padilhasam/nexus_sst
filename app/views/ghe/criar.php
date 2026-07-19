<?php
$css = 'ghe.css';
require_once dirname(__DIR__) . '/templates/header.php';

$checklists = $checklists ?? [];
$checklist = $checklist ?? null;
$hierarquias = $hierarquias ?? [];
$dadosAnteriores = $dadosAnteriores ?? [];
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
            <h2>Novo GHE</h2>
            <p>Selecione o check-list e agrupe os cargos com exposição semelhante.</p>
        </div>
        <a href="<?= BASE_URL ?>/ghe" class="btn btn-outline-secondary ghe-new-button">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </header>

    <?php if (!$checklist): ?>
        <section class="ghe-form-card ghe-checklist-picker">
            <div class="ghe-section-title">
                <i class="fa-regular fa-square-check"></i>
                <div><h3>Escolha o check-list</h3><p>O GHE será vinculado à empresa, unidade e técnico do levantamento.</p></div>
            </div>

            <?php if (!empty($checklists)): ?>
                <form method="GET" action="<?= BASE_URL ?>/ghe/criar" class="ghe-picker-form">
                    <div class="ghe-field">
                        <label for="checklist_id">Check-list em andamento *</label>
                        <select name="checklist_id" id="checklist_id" class="form-select" required>
                            <option value="">Selecione</option>
                            <?php foreach ($checklists as $item): ?>
                                <option value="<?= (int)$item['id'] ?>">
                                    #<?= (int)$item['id'] ?> — <?= htmlspecialchars($item['empresa_nome']) ?> / <?= htmlspecialchars($item['unidade_nome'] ?? 'Matriz') ?> — <?= !empty($item['data_visita']) ? date('d/m/Y', strtotime($item['data_visita'])) : '-' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrow-right"></i> Continuar</button>
                </form>
            <?php else: ?>
                <div class="ghe-empty-inline">
                    <i class="fa-regular fa-square-check"></i>
                    <div><strong>Nenhum check-list disponível</strong><span>Inicie uma visita técnica antes de criar um GHE.</span></div>
                    <a href="<?= BASE_URL ?>/visitas" class="btn btn-primary">Ir para Visitas</a>
                </div>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <?php require __DIR__ . '/formulario.php'; ?>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
