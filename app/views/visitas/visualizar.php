<?php 
require_once dirname(__DIR__) . '/templates/header.php'; 

// O controller deve passar o objeto ou array $visita para esta view
$v = $visita ?? null;
?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">
        
        <header class="mb-4 d-flex align-items-center justify-content-between">
            <h3 class="fw-bold text-dark m-0">
                <i class="fas fa-eye me-2 text-primary"></i> Detalhes da Visita #<?= $v['id'] ?? '' ?>
            </h3>
            <a href="<?= BASE_URL ?>/visitas" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </header>

        <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-primary"><?= htmlspecialchars($v['empresa_nome'] ?? 'Visita') ?></h5>
                <span class="badge bg-<?= ($v['status'] == 'FINALIZADA') ? 'success' : 'warning' ?> rounded-pill px-3">
                    <?= $v['status'] ?? 'ABERTA' ?>
                </span>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Usuário</label>
                        <p class="fs-6 fw-medium"><?= htmlspecialchars($v['usuario_nome'] ?? 'N/A') ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Veículo</label>
                        <p class="fs-6 fw-medium"><?= htmlspecialchars($v['veiculo_modelo'] ?? 'N/A') ?> - <?= htmlspecialchars($v['veiculo_placa'] ?? '') ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Data e Hora</label>
                        <p class="fs-6 fw-medium mb-0">
                            <i class="far fa-calendar-alt text-primary me-2"></i>
                            <?= date('d/m/Y', strtotime($v['data_visita'])) ?>
                            <span class="mx-3 text-muted">|</span>
                            <i class="far fa-clock text-primary me-2"></i>
                            <?= substr($v['hora_inicio'], 0, 5) ?>
                            <i class="fas fa-arrow-right mx-2 text-secondary"></i>
                            <?= substr($v['hora_fim'], 0, 5) ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Unidade</label>
                        <p class="fs-6 fw-medium"><?= htmlspecialchars($v['unidade_nome'] ?? 'N/A') ?></p>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small fw-bold text-uppercase">Objetivo</label>
                        <p class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($v['objetivo'] ?? 'Nenhum objetivo definido.')) ?></p>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small fw-bold text-uppercase">Observações</label>
                        <p class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($v['observacoes'] ?? 'Sem observações.')) ?></p>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <a href="<?= BASE_URL ?>/visitas/excluir?id=<?= $v['id'] ?>" 
                    class="btn btn-danger rounded-pill px-4"
                    onclick="return confirm('Tem certeza que deseja excluir este agendamento?');">
                        <i class="fas fa-trash-alt me-1"></i> Excluir
                    </a>
                </div>

                <div class="d-flex gap-2">
                    <?php if ($v['status'] !== 'CANCELADA'): ?>
                        <a href="<?= BASE_URL ?>/visitas/cancelar?id=<?= $v['id'] ?>" 
                        class="btn btn-outline-danger rounded-pill px-4"
                        onclick="return confirm('Tem certeza que deseja CANCELAR este agendamento?');">
                            <i class="fas fa-ban me-1"></i> Cancelar
                        </a>
                    <?php endif; ?>

                    <?php if ($v['status'] !== 'FINALIZADA'): ?>
                        <form action="<?= BASE_URL ?>/visitas/atualizarStatus?id=<?= $v['id'] ?>" method="POST" class="m-0">
                            <input type="hidden" name="status" value="FINALIZADA">
                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                <i class="fas fa-check-circle me-1"></i> Finalizar
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>/visitas/atualizarStatus?id=<?= $v['id'] ?>" method="POST" class="m-0">
                            <input type="hidden" name="status" value="ABERTA">
                            <button type="submit" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-undo me-1"></i> Reabrir
                            </button>
                        </form>
                    <?php endif; ?>

                    <a href="<?= BASE_URL ?>/visitas/editar?id=<?= $v['id'] ?>" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>