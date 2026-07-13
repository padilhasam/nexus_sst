<?php
$css = 'agenda.css';

require_once dirname(__DIR__) . '/templates/header.php';

$agendamento = $agendamento ?? null;
$historico = $historico ?? [];

if (!$agendamento) {
    echo '<div class="alert alert-danger">Agendamento não encontrado.</div>';

    require_once dirname(__DIR__) . '/templates/footer.php';
    return;
}

$id = (int)($agendamento['id'] ?? 0);

$empresa = !empty($agendamento['empresa_fantasia'])
    ? $agendamento['empresa_fantasia']
    : ($agendamento['empresa_nome'] ?? 'Empresa não informada');

$unidade = !empty($agendamento['unidade_nome'])
    ? $agendamento['unidade_nome']
    : 'Matriz';

$dataAgendada = !empty($agendamento['data_agendada'])
    ? date('d/m/Y', strtotime($agendamento['data_agendada']))
    : '-';

$horaInicio = !empty($agendamento['hora_inicio'])
    ? substr($agendamento['hora_inicio'], 0, 5)
    : '-';

$horaFim = !empty($agendamento['hora_fim'])
    ? substr($agendamento['hora_fim'], 0, 5)
    : null;

$tecnico = !empty($agendamento['tecnico_nome'])
    ? $agendamento['tecnico_nome']
    : 'Não informado';

$status = strtoupper($agendamento['status'] ?? 'AGENDADO');

$statusLabel = match ($status) {
    'AGENDADO' => 'Agendado',
    'CONFIRMADO' => 'Confirmado',
    'REAGENDADO' => 'Reagendado',
    'CANCELADO' => 'Cancelado',
    'CONCLUIDO' => 'Concluído',
    'EXCLUIDO' => 'Excluído',
    default => ucfirst(strtolower($status)),
};

$statusClasse = match ($status) {
    'CONFIRMADO', 'CONCLUIDO' => 'success',
    'REAGENDADO' => 'info',
    'CANCELADO', 'EXCLUIDO' => 'danger',
    default => 'warning',
};

$podeEditar = !in_array(
    $status,
    ['CANCELADO', 'CONCLUIDO', 'EXCLUIDO'],
    true
);
?>

<div class="page-head d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">
            Agendamento #<?= $id ?>
        </h2>

        <p class="text-muted mb-0">
            <?= htmlspecialchars($empresa) ?>
        </p>
    </div>

    <div class="d-flex gap-2">
        <a
            href="<?= BASE_URL ?>/agenda"
            class="btn btn-light rounded-pill px-4"
        >
            Voltar
        </a>

        <?php if ($podeEditar): ?>
            <a
                href="<?= BASE_URL ?>/agenda/editar/<?= $id ?>"
                class="btn btn-primary rounded-pill px-4"
            >
                Editar
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">

    <div class="col-12 col-lg-7">
        <div class="panel h-100">

            <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                <h5 class="fw-bold mb-0">Dados da Agenda</h5>

                <span class="badge text-bg-<?= $statusClasse ?> rounded-pill px-3 py-2">
                    <?= htmlspecialchars($statusLabel) ?>
                </span>
            </div>

            <div class="row g-3">

                <div class="col-12 col-md-6">
                    <small class="text-muted">Data</small>

                    <div class="fw-bold">
                        <?= htmlspecialchars($dataAgendada) ?>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <small class="text-muted">Horário</small>

                    <div class="fw-bold">
                        <?= htmlspecialchars($horaInicio) ?>

                        <?php if ($horaFim): ?>
                            às <?= htmlspecialchars($horaFim) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-12">
                    <small class="text-muted">Empresa / Unidade</small>

                    <div class="fw-bold">
                        <?= htmlspecialchars($empresa) ?>
                        —
                        <?= htmlspecialchars($unidade) ?>
                    </div>
                </div>

                <div class="col-12">
                    <small class="text-muted">Técnico</small>

                    <div class="fw-bold">
                        <?= htmlspecialchars($tecnico) ?>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <small class="text-muted">Prioridade</small>

                    <div class="fw-bold">
                        <?= htmlspecialchars(
                            ($agendamento['prioridade'] ?? 'PADRAO') === 'PADRAO'
                                ? 'Padrão'
                                : ucfirst(
                                    strtolower(
                                        $agendamento['prioridade'] ?? 'PADRAO'
                                    )
                                )
                        ) ?>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <small class="text-muted">Veículo</small>

                    <div class="fw-bold">
                        <?php if (!empty($agendamento['veiculo_modelo'])): ?>
                            <?= htmlspecialchars($agendamento['veiculo_modelo']) ?>

                            <?php if (!empty($agendamento['veiculo_placa'])): ?>
                                —
                                <?= htmlspecialchars($agendamento['veiculo_placa']) ?>
                            <?php endif; ?>
                        <?php else: ?>
                            Não informado
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-12">
                    <small class="text-muted">Responsável no local</small>

                    <div class="fw-bold">
                        <?= htmlspecialchars(
                            $agendamento['responsavel_acompanhamento']
                            ?? '-'
                        ) ?>
                    </div>
                </div>

                <div class="col-12">
                    <small class="text-muted">Título</small>

                    <div class="fw-bold">
                        <?= htmlspecialchars(
                            $agendamento['titulo']
                            ?? '-'
                        ) ?>
                    </div>
                </div>

                <div class="col-12">
                    <small class="text-muted">Objetivo</small>

                    <div>
                        <?= nl2br(
                            htmlspecialchars(
                                $agendamento['objetivo']
                                ?? '-'
                            )
                        ) ?>
                    </div>
                </div>

                <div class="col-12">
                    <small class="text-muted">Observações</small>

                    <div>
                        <?= nl2br(
                            htmlspecialchars(
                                $agendamento['observacoes']
                                ?? '-'
                            )
                        ) ?>
                    </div>
                </div>

                <?php if ($status === 'CANCELADO'): ?>
                    <div class="col-12">
                        <small class="text-muted">
                            Motivo do cancelamento
                        </small>

                        <div class="fw-bold text-danger">
                            <?= nl2br(
                                htmlspecialchars(
                                    $agendamento['motivo_cancelamento']
                                    ?? '-'
                                )
                            ) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($status === 'EXCLUIDO'): ?>
                    <div class="col-12">
                        <small class="text-muted">
                            Motivo da exclusão
                        </small>

                        <div class="fw-bold text-danger">
                            <?= nl2br(
                                htmlspecialchars(
                                    $agendamento['motivo_exclusao']
                                    ?? '-'
                                )
                            ) ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="panel h-100">

            <h5 class="fw-bold mb-4">Histórico</h5>

            <?php foreach ($historico as $h): ?>
                <div class="border-start border-primary ps-3 mb-3">

                    <strong>
                        <?= htmlspecialchars(
                            $h['acao'] ?? 'Alteração'
                        ) ?>
                    </strong>

                    <br>

                    <small class="text-muted">
                        <?php if (!empty($h['criado_em'])): ?>
                            <?= date(
                                'd/m/Y H:i',
                                strtotime($h['criado_em'])
                            ) ?>
                        <?php endif; ?>

                        —

                        <?= htmlspecialchars(
                            $h['usuario_nome']
                            ?? 'Sistema'
                        ) ?>
                    </small>

                    <?php if (!empty($h['motivo'])): ?>
                        <p class="mb-0 small">
                            <?= htmlspecialchars($h['motivo']) ?>
                        </p>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

            <?php if (empty($historico)): ?>
                <p class="text-muted mb-0">
                    Nenhum histórico registrado.
                </p>
            <?php endif; ?>

        </div>
    </div>

</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>