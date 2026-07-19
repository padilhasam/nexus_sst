<?php
require_once dirname(__DIR__) . '/templates/header.php';

$v = $visita ?? null;
$historico = $historico ?? [];
$podeIniciarChecklist = $podeIniciarChecklist ?? false;

$status = strtoupper((string)($v['status'] ?? 'ABERTA'));
$statusClasse = match ($status) {
    'FINALIZADA' => 'success',
    'CANCELADA', 'EXCLUIDA' => 'danger',
    'CHECKLIST_INICIADO', 'EM_ANDAMENTO' => 'warning',
    default => 'primary',
};

$statusLabel = match ($status) {
    'ABERTA', 'AGENDADA' => 'Aguardando check-list',
    'CONFIRMADA' => 'Confirmada',
    'CHECKLIST_INICIADO', 'EM_ANDAMENTO' => 'Em andamento',
    'FINALIZADA' => 'Concluída',
    'CANCELADA' => 'Cancelada',
    'EXCLUIDA' => 'Excluída',
    default => ucfirst(strtolower($status)),
};

$horaInicio = !empty($v['hora_inicio'])
    ? substr($v['hora_inicio'], 0, 5)
    : (!empty($v['hora_visita']) ? substr($v['hora_visita'], 0, 5) : '-');
$horaFim = !empty($v['hora_fim']) ? substr($v['hora_fim'], 0, 5) : '';
?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">
        <header class="mb-4 d-flex align-items-center justify-content-between">
            <h3 class="fw-bold text-dark m-0">
                <i class="fas fa-eye me-2 text-primary"></i>
                Detalhes da Visita #<?= (int)($v['id'] ?? 0) ?>
            </h3>
            <a href="<?= BASE_URL ?>/visitas" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </header>

        <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-primary">
                    <?= htmlspecialchars($v['empresa_fantasia'] ?: ($v['empresa_nome'] ?? 'Visita')) ?>
                </h5>
                <span class="badge bg-<?= $statusClasse ?> rounded-pill px-3">
                    <?= htmlspecialchars($statusLabel) ?>
                </span>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Técnico responsável</label>
                        <p class="fs-6 fw-medium"><?= htmlspecialchars($v['tecnico_nome'] ?? 'N/A') ?></p>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Veículo</label>
                        <p class="fs-6 fw-medium">
                            <?= htmlspecialchars($v['veiculo_modelo'] ?? 'Não informado') ?>
                            <?= !empty($v['veiculo_placa']) ? ' - ' . htmlspecialchars($v['veiculo_placa']) : '' ?>
                        </p>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Data e Hora</label>
                        <p class="fs-6 fw-medium mb-0">
                            <i class="far fa-calendar-alt text-primary me-2"></i>
                            <?= !empty($v['data_visita']) ? date('d/m/Y', strtotime($v['data_visita'])) : '-' ?>
                            <span class="mx-3 text-muted">|</span>
                            <i class="far fa-clock text-primary me-2"></i>
                            <?= $horaInicio ?><?= $horaFim !== '' ? ' às ' . $horaFim : '' ?>
                        </p>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Unidade</label>
                        <p class="fs-6 fw-medium"><?= htmlspecialchars($v['unidade_nome'] ?? 'Matriz') ?></p>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Prioridade</label>
                        <p class="fs-6 fw-medium">
                            <?= match (strtoupper((string)($v['prioridade'] ?? 'PADRAO'))) {
                                'CRITICA' => 'Crítica',
                                'URGENTE' => 'Urgente',
                                default => 'Padrão',
                            } ?>
                        </p>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Responsável pelo acompanhamento</label>
                        <p class="fs-6 fw-medium">
                            <?= htmlspecialchars($v['responsavel_acompanhamento'] ?? 'Não informado') ?>
                        </p>
                    </div>

                    <div class="col-12">
                        <label class="text-muted small fw-bold text-uppercase">Objetivo</label>
                        <p class="bg-light p-3 rounded">
                            <?= nl2br(htmlspecialchars($v['objetivo'] ?? 'Nenhum objetivo definido.')) ?>
                        </p>
                    </div>

                    <div class="col-12">
                        <label class="text-muted small fw-bold text-uppercase">Observações</label>
                        <p class="bg-light p-3 rounded">
                            <?= nl2br(htmlspecialchars($v['observacoes'] ?? 'Sem observações.')) ?>
                        </p>
                    </div>
                </div>

                <?php if (!empty($historico)): ?>
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3">Histórico operacional</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Ação</th>
                                    <th>Status</th>
                                    <th>Usuário</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historico as $item): ?>
                                    <tr>
                                        <td><?= !empty($item['criado_em']) ? date('d/m/Y H:i', strtotime($item['criado_em'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($item['acao'] ?? '-') ?></td>
                                        <td>
                                            <?= htmlspecialchars(($item['status_anterior'] ?? '-') . ' → ' . ($item['status_novo'] ?? '-')) ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['usuario_nome'] ?? 'Sistema') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <?php if (!empty($v['agenda_ref_id'])): ?>
                        <a
                            href="<?= BASE_URL ?>/agenda/visualizar/<?= (int)$v['agenda_ref_id'] ?>"
                            class="btn btn-outline-secondary rounded-pill px-4"
                        >
                            <i class="fa-regular fa-calendar me-1"></i> Ver Agendamento
                        </a>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-2">
                    <?php if ($podeIniciarChecklist): ?>
                        <form
                            action="<?= BASE_URL ?>/checklists/iniciar/<?= (int)$v['id'] ?>"
                            method="POST"
                            class="m-0"
                            onsubmit="return confirm('Deseja iniciar o check-list desta visita técnica?');"
                        >
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="fa-regular fa-square-check me-1"></i> Iniciar Check-list
                            </button>
                        </form>
                    <?php elseif (!empty($v['checklist_id'])): ?>
                        <a
                            href="<?= BASE_URL ?>/checklists/visualizar/<?= (int)$v['checklist_id'] ?>"
                            class="btn btn-primary rounded-pill px-4"
                        >
                            <i class="fa-regular fa-square-check me-1"></i> Abrir Check-list
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
