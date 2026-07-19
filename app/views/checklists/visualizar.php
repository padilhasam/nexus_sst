<?php
$css = 'checklists.css';
$rotaAtual = 'checklists';
$pageTitle = 'Check-list de Visita';
require_once dirname(__DIR__) . '/templates/header.php';

$c = $checklist ?? [];
$abaAtiva = $abaAtiva ?? 'dados';
$estruturaPronta = (bool)($estruturaPronta ?? false);
$checklistId = (int)($c['id'] ?? 0);
$visitaId = (int)($c['visita_id'] ?? 0);
$percentual = max(0, min(100, (int)($progresso['percentual'] ?? 10)));
$statusChecklist = strtoupper((string)($c['status'] ?? 'ABERTO'));
$somenteLeitura = in_array($statusChecklist, ['CONCLUIDO', 'CANCELADO'], true);

$empresaNome = !empty($c['empresa_fantasia'])
    ? $c['empresa_fantasia']
    : ($c['empresa_nome'] ?? 'Empresa não informada');

$unidadeNome = !empty($c['unidade_fantasia'])
    ? $c['unidade_fantasia']
    : (!empty($c['unidade_razao_social'])
        ? $c['unidade_razao_social']
        : ($c['unidade_nome'] ?? 'Matriz'));

$prioridade = strtoupper((string)($c['agenda_prioridade'] ?? $c['prioridade'] ?? 'PADRAO'));
$prioridadeLabel = match ($prioridade) {
    'CRITICA' => 'Crítica',
    'URGENTE' => 'Urgente',
    default => 'Padrão',
};

$statusLabel = match ($statusChecklist) {
    'EM_ANDAMENTO' => 'Em andamento',
    'CONCLUIDO' => 'Concluído',
    'CANCELADO' => 'Cancelado',
    default => 'Aberto',
};

$statusClasse = match ($statusChecklist) {
    'CONCLUIDO' => 'is-completed',
    'CANCELADO' => 'is-cancelled',
    'EM_ANDAMENTO' => 'is-progress',
    default => 'is-open',
};

$dataVisita = !empty($c['data_visita'])
    ? date('d/m/Y', strtotime($c['data_visita']))
    : '-';

$horarioVisita = !empty($c['hora_visita'])
    ? substr((string)$c['hora_visita'], 0, 5)
    : '-';

if (!empty($c['hora_fim'])) {
    $horarioVisita .= ' às ' . substr((string)$c['hora_fim'], 0, 5);
}

$abas = [
    'dados' => [
        'label' => 'Dados da Visita',
        'icone' => 'fa-solid fa-shield-halved',
        'contador' => null,
        'disponivel' => true,
    ],
    'hierarquia' => [
        'label' => 'Hierarquia',
        'icone' => 'fa-solid fa-sitemap',
        'contador' => (int)($progresso['hierarquias'] ?? 0),
        'disponivel' => $estruturaPronta,
    ],
    'funcionarios' => [
        'label' => 'Funcionários',
        'icone' => 'fa-regular fa-user',
        'contador' => (int)($progresso['funcionarios'] ?? 0),
        'disponivel' => $estruturaPronta,
    ],
    'ghe-riscos' => [
        'label' => 'GHE / Riscos',
        'icone' => 'fa-solid fa-flask-vial',
        'contador' => (int)($progresso['ghes'] ?? 0),
        'disponivel' => $estruturaPronta,
    ],
    'epi-epc' => [
        'label' => 'EPI / EPC',
        'icone' => 'fa-solid fa-helmet-safety',
        'contador' => null,
        'disponivel' => false,
    ],
    'evidencias' => [
        'label' => 'Evidências',
        'icone' => 'fa-regular fa-image',
        'contador' => null,
        'disponivel' => false,
    ],
    'fiscalizacao' => [
        'label' => 'Fiscalização',
        'icone' => 'fa-solid fa-clipboard-check',
        'contador' => null,
        'disponivel' => false,
    ],
    'assinaturas' => [
        'label' => 'Assinaturas',
        'icone' => 'fa-solid fa-signature',
        'contador' => null,
        'disponivel' => false,
    ],
];
?>

<div class="checklist-detail-page">
    <?php if (!empty($_SESSION['sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show checklist-detail-alert" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= htmlspecialchars($_SESSION['sucesso']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show checklist-detail-alert" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?= htmlspecialchars($_SESSION['erro']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <header class="checklist-detail-header">
        <div class="checklist-detail-heading">
            <div class="checklist-detail-icon" aria-hidden="true">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>

            <div class="checklist-detail-title">
                <span class="checklist-detail-eyebrow">LEVANTAMENTO EM CAMPO</span>
                <h1><?= htmlspecialchars($c['agenda_titulo'] ?? 'Check-list de visita técnica') ?></h1>
                <p>
                    <?= htmlspecialchars($empresaNome) ?>
                    <span aria-hidden="true">•</span>
                    <?= htmlspecialchars($unidadeNome) ?>
                </p>
            </div>
        </div>

        <div class="checklist-detail-header-actions">
            <span class="checklist-detail-status <?= $statusClasse ?>">
                <i class="fa-solid fa-circle"></i>
                <?= htmlspecialchars($statusLabel) ?>
            </span>

            <a href="<?= BASE_URL ?>/checklists" class="btn btn-outline-secondary checklist-detail-back">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar
            </a>

            <button
                class="btn btn-primary checklist-detail-finish"
                type="button"
                disabled
                title="A finalização será habilitada após a implementação das demais etapas operacionais."
            >
                <i class="fa-solid fa-flag-checkered"></i>
                Finalizar check-list
            </button>
        </div>
    </header>

    <?php if (!$estruturaPronta): ?>
        <div class="alert alert-warning checklist-detail-migration" role="alert">
            <i class="fa-solid fa-database"></i>
            <div>
                <strong>Atualização do banco necessária</strong>
                <span>
                    Execute a migration
                    <code>2026_07_19_checklist_hierarquia_funcionarios_ghe.sql</code>
                    para liberar Hierarquia, Funcionários e GHE/Riscos.
                </span>
            </div>
        </div>
    <?php endif; ?>

    <section class="checklist-detail-context" aria-label="Resumo do check-list">
        <article class="checklist-detail-context-card is-blue">
            <div class="checklist-detail-context-icon">
                <i class="fa-regular fa-calendar"></i>
            </div>
            <div>
                <span>Visita técnica</span>
                <strong><?= htmlspecialchars($dataVisita) ?></strong>
                <small><?= htmlspecialchars($horarioVisita) ?> · #<?= $visitaId ?></small>
            </div>
        </article>

        <article class="checklist-detail-context-card is-orange">
            <div class="checklist-detail-context-icon">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <span>Prioridade</span>
                <strong><?= htmlspecialchars($prioridadeLabel) ?></strong>
                <small>Check-list #<?= $checklistId ?></small>
            </div>
        </article>

        <article class="checklist-detail-context-card is-purple">
            <div class="checklist-detail-context-icon">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <span>Técnico responsável</span>
                <strong><?= htmlspecialchars($c['tecnico_nome'] ?? '-') ?></strong>
                <small><?= htmlspecialchars($c['tecnico_registro'] ?? 'Registro não informado') ?></small>
            </div>
        </article>

        <article class="checklist-detail-context-card is-green">
            <div class="checklist-detail-context-icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="checklist-detail-progress-copy">
                <span>Progresso operacional</span>
                <strong><?= $percentual ?>%</strong>
                <progress value="<?= $percentual ?>" max="100" aria-label="Progresso do check-list: <?= $percentual ?>%">
                    <?= $percentual ?>%
                </progress>
            </div>
        </article>
    </section>

    <section class="checklist-detail-workspace">
        <div class="checklist-detail-workspace-header">
            <div>
                <span class="checklist-detail-eyebrow">FLUXO OPERACIONAL</span>
                <h2>Etapas do check-list</h2>
                <p>Preencha cada etapa de forma independente. Os dados são preservados durante o levantamento.</p>
            </div>

            <span class="checklist-detail-save-state">
                <i class="fa-regular fa-circle-check"></i>
                Salvamento por etapa
            </span>
        </div>

        <nav class="checklist-detail-tabs" aria-label="Etapas do check-list">
            <?php $numeroEtapa = 0; ?>
            <?php foreach ($abas as $chave => $aba): ?>
                <?php
                $numeroEtapa++;
                $ativa = $abaAtiva === $chave;
                $disponivel = (bool)$aba['disponivel'];
                $contador = $aba['contador'];
                ?>

                <?php if ($disponivel): ?>
                    <a
                        class="checklist-detail-tab <?= $ativa ? 'is-active' : '' ?>"
                        href="<?= BASE_URL ?>/checklists/visualizar/<?= $checklistId ?>?aba=<?= urlencode($chave) ?>"
                        <?= $ativa ? 'aria-current="page"' : '' ?>
                    >
                        <span class="checklist-detail-tab-number"><?= $numeroEtapa ?></span>
                        <i class="<?= htmlspecialchars($aba['icone']) ?>"></i>
                        <span class="checklist-detail-tab-label"><?= htmlspecialchars($aba['label']) ?></span>
                        <?php if ($contador !== null && $contador > 0): ?>
                            <b><?= (int)$contador ?></b>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <span class="checklist-detail-tab is-disabled" title="Próxima etapa do desenvolvimento">
                        <span class="checklist-detail-tab-number"><?= $numeroEtapa ?></span>
                        <i class="<?= htmlspecialchars($aba['icone']) ?>"></i>
                        <span class="checklist-detail-tab-label"><?= htmlspecialchars($aba['label']) ?></span>
                        <small>Em breve</small>
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <main class="checklist-detail-content">
            <?php
            $partial = match ($abaAtiva) {
                'hierarquia' => 'hierarquia',
                'funcionarios' => 'funcionarios',
                'ghe-riscos' => 'ghe-riscos',
                default => 'dados-visita',
            };

            require __DIR__ . '/partials/' . $partial . '.php';
            ?>
        </main>
    </section>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>