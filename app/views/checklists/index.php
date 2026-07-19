<?php
$checklists = $checklists ?? [];
$indicadores = $indicadores ?? [];
$abaAtual = $abaAtual ?? 'andamento';
$filtros = $filtros ?? [];
$usuarioAdministrador = (bool)($usuarioAdministrador ?? false);

$css = 'checklists.css';
$pageTitle = 'Check-lists';
$pageSubtitle = 'Histórico operacional dos levantamentos de riscos iniciados nas Visitas Técnicas';
$pageBadge = (int)($indicadores['andamento'] ?? 0) . ' em andamento';
$pageActionUrl = BASE_URL . '/visitas';
$pageActionLabel = 'Abrir Visitas Técnicas';
$pageActionIcon = 'fa-solid fa-route';
$rotaAtual = 'checklists';

require_once dirname(__DIR__) . '/templates/header.php';

if (!function_exists('checklistIndexPrioridadeLabel')) {
    function checklistIndexPrioridadeLabel(string $prioridade): string
    {
        return match (strtoupper($prioridade)) {
            'CRITICA' => 'Crítica',
            'URGENTE' => 'Urgente',
            default => 'Padrão',
        };
    }
}

if (!function_exists('checklistIndexPrioridadeClasse')) {
    function checklistIndexPrioridadeClasse(string $prioridade): string
    {
        return match (strtoupper($prioridade)) {
            'CRITICA' => 'critica',
            'URGENTE' => 'urgente',
            default => 'padrao',
        };
    }
}

if (!function_exists('checklistIndexStatusLabel')) {
    function checklistIndexStatusLabel(string $status): string
    {
        return match (strtoupper($status)) {
            'EM_ANDAMENTO' => 'Em andamento',
            'CONCLUIDO' => 'Concluído',
            'CANCELADO' => 'Cancelado',
            default => 'Aberto',
        };
    }
}

if (!function_exists('checklistIndexStatusClasse')) {
    function checklistIndexStatusClasse(string $status): string
    {
        return match (strtoupper($status)) {
            'CONCLUIDO' => 'concluida',
            'CANCELADO' => 'cancelada',
            'EM_ANDAMENTO' => 'andamento',
            default => 'aguardando',
        };
    }
}

if (!function_exists('checklistIndexStatusIcone')) {
    function checklistIndexStatusIcone(string $status): string
    {
        return match (strtoupper($status)) {
            'CONCLUIDO' => 'fa-circle-check',
            'CANCELADO' => 'fa-circle-xmark',
            'EM_ANDAMENTO' => 'fa-spinner',
            default => 'fa-clipboard-list',
        };
    }
}

if (!function_exists('checklistIndexMesAbreviado')) {
    function checklistIndexMesAbreviado(int $mes): string
    {
        return [
            1 => 'JAN', 2 => 'FEV', 3 => 'MAR', 4 => 'ABR',
            5 => 'MAI', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
            9 => 'SET', 10 => 'OUT', 11 => 'NOV', 12 => 'DEZ',
        ][$mes] ?? '---';
    }
}

if (!function_exists('checklistIndexFormatarCnpj')) {
    function checklistIndexFormatarCnpj(?string $cnpj): string
    {
        $numeros = preg_replace('/\D+/', '', (string)$cnpj);
        if (strlen($numeros) !== 14) {
            return trim((string)$cnpj);
        }

        return substr($numeros, 0, 2) . '.'
            . substr($numeros, 2, 3) . '.'
            . substr($numeros, 5, 3) . '/'
            . substr($numeros, 8, 4) . '-'
            . substr($numeros, 12, 2);
    }
}

if (!function_exists('checklistIndexEtapaLabel')) {
    function checklistIndexEtapaLabel(?string $aba, string $status): string
    {
        if (strtoupper($status) === 'CONCLUIDO') {
            return 'Levantamento finalizado';
        }

        return match ((string)$aba) {
            'hierarquia' => 'Hierarquia da empresa',
            'funcionarios' => 'Funcionários',
            'ghe-riscos' => 'GHE e riscos',
            default => 'Dados da visita',
        };
    }
}

if (!function_exists('checklistIndexDataHora')) {
    function checklistIndexDataHora(?string $valor): string
    {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return 'Não registrada';
        }

        $timestamp = strtotime($valor);
        return $timestamp ? date('d/m/Y H:i', $timestamp) : $valor;
    }
}

$abas = [
    'andamento' => [
        'rotulo' => 'Em andamento',
        'icone' => 'fa-solid fa-person-walking-arrow-right',
        'contador' => (int)($indicadores['andamento'] ?? 0),
    ],
    'concluidos' => [
        'rotulo' => 'Concluídos',
        'icone' => 'fa-regular fa-circle-check',
        'contador' => (int)($indicadores['concluidos'] ?? 0),
    ],
    'cancelados' => [
        'rotulo' => 'Cancelados',
        'icone' => 'fa-regular fa-circle-xmark',
        'contador' => (int)($indicadores['cancelados'] ?? 0),
    ],
    'todos' => [
        'rotulo' => 'Todos',
        'icone' => 'fa-solid fa-list-check',
        'contador' => (int)($indicadores['total'] ?? 0),
    ],
];

$queryFiltros = array_filter([
    'prioridade' => $filtros['prioridade'] ?? '',
    'data_inicio' => $filtros['data_inicio'] ?? '',
    'data_fim' => $filtros['data_fim'] ?? '',
], static fn($valor): bool => $valor !== '' && $valor !== null);

$urlAba = static function (string $aba) use ($queryFiltros): string {
    return BASE_URL . '/checklists?' . http_build_query(array_merge($queryFiltros, ['aba' => $aba]));
};

$haFiltros = $queryFiltros !== [];
?>

<div class="checklists-index-page">
    <?php if (!empty($_SESSION['sucesso'])): ?>
        <div class="alert alert-success rounded-4 alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= htmlspecialchars($_SESSION['sucesso']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['info'])): ?>
        <div class="alert alert-info rounded-4 alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-info me-2"></i>
            <?= htmlspecialchars($_SESSION['info']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php unset($_SESSION['info']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger rounded-4 alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?= htmlspecialchars($_SESSION['erro']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <section class="checklists-index-kpis" aria-label="Indicadores dos check-lists">
        <article class="checklists-index-kpi-card">
            <span class="checklists-index-kpi-icon total"><i class="fa-regular fa-square-check"></i></span>
            <div>
                <span>Total de check-lists</span>
                <strong><?= (int)($indicadores['total'] ?? 0) ?></strong>
                <small>Levantamentos iniciados</small>
            </div>
        </article>

        <article class="checklists-index-kpi-card">
            <span class="checklists-index-kpi-icon andamento"><i class="fa-solid fa-person-walking-arrow-right"></i></span>
            <div>
                <span>Em andamento</span>
                <strong><?= (int)($indicadores['andamento'] ?? 0) ?></strong>
                <small>Disponíveis para continuar</small>
            </div>
        </article>

        <article class="checklists-index-kpi-card">
            <span class="checklists-index-kpi-icon concluidos"><i class="fa-solid fa-circle-check"></i></span>
            <div>
                <span>Concluídos</span>
                <strong><?= (int)($indicadores['concluidos'] ?? 0) ?></strong>
                <small>Levantamentos finalizados</small>
            </div>
        </article>

        <article class="checklists-index-kpi-card">
            <span class="checklists-index-kpi-icon cancelados"><i class="fa-solid fa-ban"></i></span>
            <div>
                <span>Cancelados</span>
                <strong><?= (int)($indicadores['cancelados'] ?? 0) ?></strong>
                <small>Preservados no histórico</small>
            </div>
        </article>
    </section>

    <section class="checklists-index-filter-card">
        <div class="checklists-index-filter-heading">
            <div>
                <span class="checklists-index-eyebrow">Histórico dos levantamentos</span>
                <h2>Localize um check-list</h2>
                <p>Filtre por prioridade ou data da visita sem perder a etapa selecionada.</p>
            </div>

            <?php if ($haFiltros): ?>
                <span class="checklists-index-filter-active">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                    Filtro ativo
                </span>
            <?php endif; ?>
        </div>

        <form method="GET" action="<?= BASE_URL ?>/checklists" class="checklists-index-filter-grid">
            <input type="hidden" name="aba" value="<?= htmlspecialchars($abaAtual) ?>">

            <div class="checklists-index-filter-field">
                <label for="prioridade">Prioridade</label>
                <select name="prioridade" id="prioridade" class="form-select">
                    <option value="">Todas as prioridades</option>
                    <option value="PADRAO" <?= (($filtros['prioridade'] ?? '') === 'PADRAO') ? 'selected' : '' ?>>Padrão</option>
                    <option value="URGENTE" <?= (($filtros['prioridade'] ?? '') === 'URGENTE') ? 'selected' : '' ?>>Urgente</option>
                    <option value="CRITICA" <?= (($filtros['prioridade'] ?? '') === 'CRITICA') ? 'selected' : '' ?>>Crítica</option>
                </select>
            </div>

            <div class="checklists-index-filter-field">
                <label for="data_inicio">Data inicial</label>
                <input
                    type="date"
                    name="data_inicio"
                    id="data_inicio"
                    class="form-control"
                    value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>"
                >
            </div>

            <div class="checklists-index-filter-field">
                <label for="data_fim">Data final</label>
                <input
                    type="date"
                    name="data_fim"
                    id="data_fim"
                    class="form-control"
                    value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>"
                >
            </div>

            <div class="checklists-index-filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Filtrar
                </button>

                <a href="<?= BASE_URL ?>/checklists?aba=<?= urlencode($abaAtual) ?>" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate-left"></i>
                    Limpar
                </a>
            </div>
        </form>
    </section>

    <section class="checklists-index-list-panel">
        <nav class="checklists-index-tabs" aria-label="Status dos check-lists">
            <?php foreach ($abas as $chave => $aba): ?>
                <a
                    href="<?= htmlspecialchars($urlAba($chave)) ?>"
                    class="<?= $abaAtual === $chave ? 'active' : '' ?>"
                    <?= $abaAtual === $chave ? 'aria-current="page"' : '' ?>
                >
                    <i class="<?= htmlspecialchars($aba['icone']) ?>"></i>
                    <span><?= htmlspecialchars($aba['rotulo']) ?></span>
                    <b><?= (int)$aba['contador'] ?></b>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="checklists-index-list-toolbar">
            <div>
                <strong><?= count($checklists) ?> check-list<?= count($checklists) === 1 ? '' : 's' ?> exibido<?= count($checklists) === 1 ? '' : 's' ?></strong>
                <span>
                    <?= $abaAtual === 'andamento'
                        ? 'Ordenados por prioridade e data da visita.'
                        : 'Registros organizados dos mais recentes para os mais antigos.' ?>
                </span>
            </div>

            <span class="checklists-index-scope-badge">
                <i class="fa-solid <?= $usuarioAdministrador ? 'fa-users' : 'fa-user-shield' ?>"></i>
                <?= $usuarioAdministrador ? 'Visão administrativa' : 'Meus check-lists' ?>
            </span>
        </div>

        <div class="checklists-index-grid">
            <?php foreach ($checklists as $item): ?>
                <?php
                $empresa = trim((string)($item['empresa_fantasia'] ?? ''));
                if ($empresa === '') {
                    $empresa = trim((string)($item['empresa_nome'] ?? 'Empresa não informada'));
                }

                $unidade = trim((string)($item['unidade_fantasia'] ?? $item['unidade_nome'] ?? ''));
                if ($unidade === '') {
                    $unidade = 'Matriz / unidade principal';
                }

                $status = strtoupper((string)($item['status'] ?? 'ABERTO'));
                $statusClasse = checklistIndexStatusClasse($status);
                $prioridade = strtoupper((string)($item['prioridade'] ?? 'PADRAO'));
                $prioridadeClasse = checklistIndexPrioridadeClasse($prioridade);

                $timestamp = !empty($item['data_visita']) ? strtotime((string)$item['data_visita']) : false;
                $dia = $timestamp ? date('d', $timestamp) : '--';
                $mes = $timestamp ? checklistIndexMesAbreviado((int)date('n', $timestamp)) : '---';
                $dataCompleta = $timestamp ? date('d/m/Y', $timestamp) : 'Data não informada';

                $horaInicio = !empty($item['hora_visita']) ? substr((string)$item['hora_visita'], 0, 5) : '--:--';
                $horaFim = !empty($item['hora_fim']) ? substr((string)$item['hora_fim'], 0, 5) : '';
                $periodo = $horaInicio . ($horaFim !== '' ? ' às ' . $horaFim : '');

                $cnpjBruto = trim((string)($item['unidade_cnpj'] ?? ''));
                if ($cnpjBruto === '') {
                    $cnpjBruto = trim((string)($item['empresa_cnpj'] ?? ''));
                }
                $cnpj = checklistIndexFormatarCnpj($cnpjBruto);

                $titulo = trim((string)($item['agenda_titulo'] ?? $item['objetivo'] ?? 'Levantamento de riscos ocupacionais'));
                $responsavel = trim((string)($item['responsavel_acompanhamento'] ?? ''));
                $etapaAtual = checklistIndexEtapaLabel($item['ultima_aba'] ?? null, $status);
                $totalGhes = (int)($item['total_ghes'] ?? 0);
                $totalRiscos = (int)($item['total_riscos'] ?? 0);
                $dataReferencia = $item['atualizado_em'] ?? $item['data_inicio'] ?? null;
                ?>

                <article class="checklist-index-card prioridade-<?= htmlspecialchars($prioridadeClasse) ?> status-<?= htmlspecialchars($statusClasse) ?>">
                    <header class="checklist-index-card-header">
                        <time class="checklist-index-data" datetime="<?= htmlspecialchars((string)($item['data_visita'] ?? '')) ?>">
                            <strong><?= htmlspecialchars($dia) ?></strong>
                            <span><?= htmlspecialchars($mes) ?></span>
                        </time>

                        <div class="checklist-index-identificacao">
                            <div class="checklist-index-card-title-row">
                                <h3 title="<?= htmlspecialchars($empresa) ?>"><?= htmlspecialchars($empresa) ?></h3>
                                <span class="checklist-index-prioridade <?= htmlspecialchars($prioridadeClasse) ?>">
                                    <i class="fa-solid fa-flag"></i>
                                    <?= htmlspecialchars(checklistIndexPrioridadeLabel($prioridade)) ?>
                                </span>
                            </div>

                            <p title="<?= htmlspecialchars($titulo) ?>"><?= htmlspecialchars($titulo) ?></p>
                        </div>
                    </header>

                    <div class="checklist-index-status-line">
                        <span class="checklist-index-status <?= htmlspecialchars($statusClasse) ?>">
                            <i class="fa-solid <?= htmlspecialchars(checklistIndexStatusIcone($status)) ?>"></i>
                            <?= htmlspecialchars(checklistIndexStatusLabel($status)) ?>
                        </span>
                        <small>CHECK-LIST #<?= str_pad((string)(int)$item['id'], 4, '0', STR_PAD_LEFT) ?></small>
                    </div>

                    <div class="checklist-index-card-body">
                        <div class="checklist-index-info-item checklist-index-info-wide">
                            <i class="fa-regular fa-building"></i>
                            <div>
                                <span>Unidade</span>
                                <strong title="<?= htmlspecialchars($unidade) ?>"><?= htmlspecialchars($unidade) ?></strong>
                                <?php if ($cnpj !== ''): ?>
                                    <small><?= htmlspecialchars($cnpj) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="checklist-index-info-item">
                            <i class="fa-regular fa-calendar"></i>
                            <div>
                                <span>Data da visita</span>
                                <strong><?= htmlspecialchars($dataCompleta) ?></strong>
                            </div>
                        </div>

                        <div class="checklist-index-info-item">
                            <i class="fa-regular fa-clock"></i>
                            <div>
                                <span>Horário</span>
                                <strong><?= htmlspecialchars($periodo) ?></strong>
                            </div>
                        </div>

                        <div class="checklist-index-info-item">
                            <i class="fa-solid fa-user-shield"></i>
                            <div>
                                <span>Técnico responsável</span>
                                <strong title="<?= htmlspecialchars($item['tecnico_nome'] ?? '-') ?>">
                                    <?= htmlspecialchars($item['tecnico_nome'] ?? '-') ?>
                                </strong>
                            </div>
                        </div>

                        <div class="checklist-index-info-item">
                            <i class="fa-solid fa-user-tie"></i>
                            <div>
                                <span>Acompanhante</span>
                                <strong title="<?= htmlspecialchars($responsavel !== '' ? $responsavel : 'Não informado') ?>">
                                    <?= htmlspecialchars($responsavel !== '' ? $responsavel : 'Não informado') ?>
                                </strong>
                            </div>
                        </div>

                        <div class="checklist-index-info-item checklist-index-info-wide">
                            <i class="fa-solid fa-list-check"></i>
                            <div>
                                <span>Etapa atual</span>
                                <strong><?= htmlspecialchars($etapaAtual) ?></strong>
                                <small><?= $totalGhes ?> GHE<?= $totalGhes === 1 ? '' : 's' ?> · <?= $totalRiscos ?> risco<?= $totalRiscos === 1 ? '' : 's' ?> aplicado<?= $totalRiscos === 1 ? '' : 's' ?></small>
                            </div>
                        </div>

                        <div class="checklist-index-info-item checklist-index-info-wide checklist-index-update-row">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <div>
                                <span>Última movimentação</span>
                                <strong><?= htmlspecialchars(checklistIndexDataHora($dataReferencia)) ?></strong>
                            </div>
                        </div>
                    </div>

                    <footer class="checklist-index-card-actions">
                        <a
                            href="<?= BASE_URL ?>/visitas/visualizar/<?= (int)$item['visita_id'] ?>"
                            class="btn btn-outline-secondary checklist-index-action-secondary"
                        >
                            <i class="fa-regular fa-eye"></i>
                            Ver visita
                        </a>

                        <a
                            href="<?= BASE_URL ?>/checklists/visualizar/<?= (int)$item['id'] ?>"
                            class="btn btn-primary checklist-index-action-primary"
                        >
                            <i class="fa-solid <?= $status === 'CONCLUIDO' ? 'fa-file-circle-check' : ($status === 'CANCELADO' ? 'fa-file-lines' : 'fa-clipboard-check') ?>"></i>
                            <?= $status === 'CONCLUIDO'
                                ? 'Visualizar Check-list'
                                : ($status === 'CANCELADO' ? 'Visualizar registro' : 'Continuar Check-list') ?>
                        </a>
                    </footer>
                </article>
            <?php endforeach; ?>

            <?php if (empty($checklists)): ?>
                <div class="checklists-index-empty-state">
                    <span class="checklists-index-empty-icon">
                        <i class="fa-regular fa-square-check"></i>
                    </span>
                    <span class="checklists-index-eyebrow">Histórico operacional</span>
                    <h3>Nenhum check-list encontrado nesta etapa</h3>
                    <p>
                        <?= $haFiltros
                            ? 'Não há registros que correspondam aos filtros aplicados.'
                            : 'Inicie uma visita técnica para gerar o primeiro check-list.' ?>
                    </p>
                    <div class="checklists-index-empty-actions">
                        <?php if ($haFiltros): ?>
                            <a href="<?= BASE_URL ?>/checklists?aba=<?= urlencode($abaAtual) ?>" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-filter-circle-xmark"></i>
                                Remover filtros
                            </a>
                        <?php endif; ?>

                        <a href="<?= BASE_URL ?>/visitas" class="btn btn-primary">
                            <i class="fa-solid fa-route"></i>
                            Abrir Visitas Técnicas
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
