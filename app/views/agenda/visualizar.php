<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>
<?php $empresa = $agendamento['empresa_fantasia'] ?: $agendamento['empresa_nome']; ?>

<div class="page-head d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Agendamento #<?= $agendamento['id'] ?></h2>
        <p class="text-muted mb-0"><?= htmlspecialchars($empresa) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/agenda" class="btn btn-light rounded-pill px-4">Voltar</a>
        <a href="<?= BASE_URL ?>/agenda/editar/<?= $agendamento['id'] ?>" class="btn btn-primary rounded-pill px-4">Editar</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="panel h-100">
            <h5 class="fw-bold mb-4">Dados da Agenda</h5>
            <div class="row g-3">
                <div class="col-6"><small class="text-muted">Data</small><div class="fw-bold"><?= date('d/m/Y', strtotime($agendamento['data_visita'])) ?></div></div>
                <div class="col-6"><small class="text-muted">Horário</small><div class="fw-bold"><?= substr($agendamento['hora_inicio'] ?? $agendamento['hora_visita'] ?? '',0,5) ?> <?= !empty($agendamento['hora_fim']) ? 'às '.substr($agendamento['hora_fim'],0,5) : '' ?></div></div>
                <div class="col-12"><small class="text-muted">Empresa / Unidade</small><div class="fw-bold"><?= htmlspecialchars($empresa) ?> — <?= htmlspecialchars($agendamento['unidade_nome'] ?? 'Matriz') ?></div></div>
                <div class="col-12"><small class="text-muted">Técnico</small><div class="fw-bold"><?= htmlspecialchars($agendamento['usuario_nome']) ?></div></div>
                <div class="col-12"><small class="text-muted">Responsável no local</small><div class="fw-bold"><?= htmlspecialchars($agendamento['responsavel_acompanhamento'] ?? '-') ?></div></div>
                <div class="col-12"><small class="text-muted">Objetivo</small><div><?= nl2br(htmlspecialchars($agendamento['objetivo'] ?? '-')) ?></div></div>
                <div class="col-12"><small class="text-muted">Observações</small><div><?= nl2br(htmlspecialchars($agendamento['observacoes'] ?? '-')) ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="panel h-100">
            <h5 class="fw-bold mb-4">Histórico</h5>
            <?php foreach (($historico ?? []) as $h): ?>
                <div class="border-start border-primary ps-3 mb-3">
                    <strong><?= htmlspecialchars($h['acao']) ?></strong><br>
                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($h['criado_em'])) ?> — <?= htmlspecialchars($h['usuario_nome'] ?? 'Sistema') ?></small>
                    <p class="mb-0 small"><?= htmlspecialchars($h['motivo'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($historico)): ?><p class="text-muted mb-0">Nenhum histórico registrado.</p><?php endif; ?>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
