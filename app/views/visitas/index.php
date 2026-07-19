<?php
$visitas = $visitas ?? [];
$indicadores = $indicadores ?? [];
$abaAtual = $abaAtual ?? 'abertas';
$filtros = $filtros ?? [];
$usuarioAdministrador = (bool)($usuarioAdministrador ?? false);

$css = 'visitas.css';
$pageTitle = 'Visitas Técnicas';
$pageSubtitle = 'Fila operacional dos levantamentos programados pela Agenda';
$pageBadge = (int)($indicadores['abertas'] ?? 0) . ' aguardando';
$pageActionUrl = BASE_URL . '/agenda';
$pageActionLabel = 'Abrir Agenda';
$pageActionIcon = 'fa-regular fa-calendar';
$rotaAtual = 'visitas';

require_once dirname(__DIR__) . '/templates/header.php';

if (!function_exists('visitaPrioridadeLabel')) {
    function visitaPrioridadeLabel(string $prioridade): string
    {
        return match (strtoupper($prioridade)) {
            'CRITICA' => 'Crítica',
            'URGENTE' => 'Urgente',
            default => 'Padrão',
        };
    }
}

if (!function_exists('visitaPrioridadeClasse')) {
    function visitaPrioridadeClasse(string $prioridade): string
    {
        return match (strtoupper($prioridade)) {
            'CRITICA' => 'critica',
            'URGENTE' => 'urgente',
            default => 'padrao',
        };
    }
}

if (!function_exists('visitaStatusLabel')) {
    function visitaStatusLabel(string $status, string $checklistStatus = ''): string
    {
        $status = strtoupper($status);
        $checklistStatus = strtoupper($checklistStatus);

        if ($checklistStatus === 'CONCLUIDO' || $status === 'FINALIZADA') {
            return 'Concluída';
        }

        if ($checklistStatus === 'EM_ANDAMENTO' || in_array($status, ['EM_ANDAMENTO', 'CHECKLIST_INICIADO'], true)) {
            return 'Check-list em andamento';
        }

        return match ($status) {
            'ABERTA', 'AGENDADA' => 'Aguardando check-list',
            'CONFIRMADA' => 'Visita confirmada',
            'CANCELADA' => 'Cancelada',
            default => ucfirst(strtolower(str_replace('_', ' ', $status))),
        };
    }
}

if (!function_exists('visitaStatusClasse')) {
    function visitaStatusClasse(string $status, string $checklistStatus = ''): string
    {
        $status = strtoupper($status);
        $checklistStatus = strtoupper($checklistStatus);

        if ($checklistStatus === 'CONCLUIDO' || $status === 'FINALIZADA') {
            return 'concluida';
        }

        if ($checklistStatus === 'EM_ANDAMENTO' || in_array($status, ['EM_ANDAMENTO', 'CHECKLIST_INICIADO'], true)) {
            return 'andamento';
        }

        if ($status === 'CANCELADA') {
            return 'cancelada';
        }

        return 'aguardando';
    }
}

if (!function_exists('visitaMesAbreviado')) {
    function visitaMesAbreviado(int $mes): string
    {
        return [
            1 => 'JAN', 2 => 'FEV', 3 => 'MAR', 4 => 'ABR',
            5 => 'MAI', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
            9 => 'SET', 10 => 'OUT', 11 => 'NOV', 12 => 'DEZ',
        ][$mes] ?? '';
    }
}

if (!function_exists('visitaFormatarCnpj')) {
    function visitaFormatarCnpj(?string $cnpj): string
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

if (!function_exists('visitaEnderecoResumo')) {
    function visitaEnderecoResumo(array $visita): string
    {
        $logradouroUnidade = trim((string)($visita['unidade_endereco'] ?? ''));
        $logradouroEmpresa = trim((string)($visita['empresa_logradouro'] ?? $visita['empresa_endereco'] ?? ''));
        $logradouro = $logradouroUnidade !== '' ? $logradouroUnidade : $logradouroEmpresa;

        $numero = trim((string)(
            $logradouroUnidade !== ''
                ? ($visita['unidade_numero'] ?? '')
                : ($visita['empresa_numero'] ?? '')
        ));

        $bairro = trim((string)(
            $logradouroUnidade !== ''
                ? ($visita['unidade_bairro'] ?? '')
                : ($visita['empresa_bairro'] ?? '')
        ));

        $cidade = trim((string)(
            $logradouroUnidade !== ''
                ? ($visita['unidade_cidade'] ?? '')
                : ($visita['empresa_cidade'] ?? '')
        ));

        $uf = trim((string)(
            $logradouroUnidade !== ''
                ? ($visita['unidade_uf'] ?? '')
                : ($visita['empresa_uf'] ?? '')
        ));

        $linha1 = trim($logradouro . ($numero !== '' ? ', ' . $numero : ''));
        $linha2 = trim($bairro . (($cidade !== '' || $uf !== '') && $bairro !== '' ? ' · ' : '') . $cidade . ($uf !== '' ? '/' . $uf : ''));
        $partes = array_values(array_filter([$linha1, $linha2], static fn(string $item): bool => $item !== ''));

        return $partes !== [] ? implode(' — ', $partes) : 'Endereço não informado';
    }
}

$abas = [
    'abertas' => [
        'rotulo' => 'Em aberto',
        'icone' => 'fa-regular fa-clock',
        'contador' => (int)($indicadores['abertas'] ?? 0),
    ],
    'concluidas' => [
        'rotulo' => 'Concluídas',
        'icone' => 'fa-regular fa-circle-check',
        'contador' => (int)($indicadores['concluidas'] ?? 0),
    ],
    'todas' => [
        'rotulo' => 'Todas',
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
    return BASE_URL . '/visitas?' . http_build_query(array_merge($queryFiltros, ['aba' => $aba]));
};

$haFiltros = $queryFiltros !== [];
?>

<div class="visitas-page">
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

    <section class="visitas-kpis" aria-label="Indicadores das visitas técnicas">
        <article class="visitas-kpi-card">
            <span class="visitas-kpi-icon total"><i class="fa-solid fa-route"></i></span>
            <div>
                <span>Total de visitas</span>
                <strong><?= (int)($indicadores['total'] ?? 0) ?></strong>
                <small>Registros vinculados à Agenda</small>
            </div>
        </article>

        <article class="visitas-kpi-card">
            <span class="visitas-kpi-icon abertas"><i class="fa-regular fa-clock"></i></span>
            <div>
                <span>Aguardando início</span>
                <strong><?= (int)($indicadores['abertas'] ?? 0) ?></strong>
                <small>Na fila operacional do TST</small>
            </div>
        </article>

        <article class="visitas-kpi-card">
            <span class="visitas-kpi-icon andamento"><i class="fa-solid fa-person-walking-arrow-right"></i></span>
            <div>
                <span>Em andamento</span>
                <strong><?= (int)($indicadores['andamento'] ?? 0) ?></strong>
                <small>Check-lists já iniciados</small>
            </div>
        </article>

        <article class="visitas-kpi-card">
            <span class="visitas-kpi-icon concluidas"><i class="fa-solid fa-circle-check"></i></span>
            <div>
                <span>Concluídas</span>
                <strong><?= (int)($indicadores['concluidas'] ?? 0) ?></strong>
                <small>Levantamentos finalizados</small>
            </div>
        </article>
    </section>

    <section class="visitas-filter-card">
        <div class="visitas-filter-heading">
            <div>
                <span class="visitas-section-eyebrow">Organização da fila</span>
                <h2>Localize os próximos atendimentos</h2>
                <p>Filtre por prioridade ou período sem perder a etapa selecionada.</p>
            </div>

            <?php if ($haFiltros): ?>
                <span class="visitas-filter-active">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                    Filtro ativo
                </span>
            <?php endif; ?>
        </div>

        <form method="GET" action="<?= BASE_URL ?>/visitas" class="visitas-filter-grid">
            <input type="hidden" name="aba" value="<?= htmlspecialchars($abaAtual) ?>">

            <div class="visitas-filter-field">
                <label for="prioridade">Prioridade</label>
                <select name="prioridade" id="prioridade" class="form-select">
                    <option value="">Todas as prioridades</option>
                    <option value="PADRAO" <?= (($filtros['prioridade'] ?? '') === 'PADRAO') ? 'selected' : '' ?>>Padrão</option>
                    <option value="URGENTE" <?= (($filtros['prioridade'] ?? '') === 'URGENTE') ? 'selected' : '' ?>>Urgente</option>
                    <option value="CRITICA" <?= (($filtros['prioridade'] ?? '') === 'CRITICA') ? 'selected' : '' ?>>Crítica</option>
                </select>
            </div>

            <div class="visitas-filter-field">
                <label for="data_inicio">Data inicial</label>
                <input
                    type="date"
                    name="data_inicio"
                    id="data_inicio"
                    class="form-control"
                    value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>"
                >
            </div>

            <div class="visitas-filter-field">
                <label for="data_fim">Data final</label>
                <input
                    type="date"
                    name="data_fim"
                    id="data_fim"
                    class="form-control"
                    value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>"
                >
            </div>

            <div class="visitas-filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Filtrar
                </button>

                <a href="<?= BASE_URL ?>/visitas?aba=<?= urlencode($abaAtual) ?>" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate-left"></i>
                    Limpar
                </a>
            </div>
        </form>
    </section>

    <section class="visitas-list-panel">
        <nav class="visitas-tabs" aria-label="Etapas das visitas">
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

        <div class="visitas-list-toolbar">
            <div>
                <strong><?= count($visitas) ?> visita<?= count($visitas) === 1 ? '' : 's' ?> exibida<?= count($visitas) === 1 ? '' : 's' ?></strong>
                <span>
                    <?= $abaAtual === 'abertas'
                        ? 'Ordenadas por prioridade, data e horário.'
                        : 'Registros organizados dos mais recentes para os mais antigos.' ?>
                </span>
            </div>

            <span class="visitas-scope-badge">
                <i class="fa-solid <?= $usuarioAdministrador ? 'fa-users' : 'fa-user-shield' ?>"></i>
                <?= $usuarioAdministrador ? 'Visão administrativa' : 'Minhas visitas' ?>
            </span>
        </div>

        <div class="visitas-grid">
            <?php foreach ($visitas as $visita): ?>
                <?php
                $empresa = trim((string)($visita['empresa_fantasia'] ?? ''));
                if ($empresa === '') {
                    $empresa = trim((string)($visita['empresa_nome'] ?? 'Empresa não informada'));
                }

                $unidade = trim((string)($visita['unidade_fantasia'] ?? $visita['unidade_nome'] ?? ''));
                if ($unidade === '') {
                    $unidade = 'Matriz / unidade principal';
                }

                $prioridade = strtoupper((string)($visita['prioridade'] ?? 'PADRAO'));
                $status = strtoupper((string)($visita['status'] ?? 'ABERTA'));
                $checklistStatus = strtoupper((string)($visita['checklist_status'] ?? ''));
                $statusClasse = visitaStatusClasse($status, $checklistStatus);
                $prioridadeClasse = visitaPrioridadeClasse($prioridade);

                $timestamp = !empty($visita['data_visita']) ? strtotime((string)$visita['data_visita']) : false;
                $dia = $timestamp ? date('d', $timestamp) : '--';
                $mes = $timestamp ? visitaMesAbreviado((int)date('n', $timestamp)) : '---';
                $dataCompleta = $timestamp ? date('d/m/Y', $timestamp) : 'Data não informada';

                $horaInicio = !empty($visita['hora_inicio'])
                    ? substr((string)$visita['hora_inicio'], 0, 5)
                    : (!empty($visita['hora_visita']) ? substr((string)$visita['hora_visita'], 0, 5) : '--:--');
                $horaFim = !empty($visita['hora_fim']) ? substr((string)$visita['hora_fim'], 0, 5) : '';
                $periodo = $horaInicio . ($horaFim !== '' ? ' às ' . $horaFim : '');

                $cnpjBruto = trim((string)($visita['unidade_cnpj'] ?? ''));
                if ($cnpjBruto === '') {
                    $cnpjBruto = trim((string)($visita['empresa_cnpj'] ?? ''));
                }
                $cnpj = visitaFormatarCnpj($cnpjBruto);
                $responsavel = trim((string)($visita['responsavel_acompanhamento'] ?? ''));
                $veiculo = trim((string)($visita['veiculo_modelo'] ?? ''));
                $placa = trim((string)($visita['veiculo_placa'] ?? ''));
                $veiculoResumo = $veiculo !== ''
                    ? $veiculo . ($placa !== '' ? ' · ' . $placa : '')
                    : 'Não definido';
                $tituloOperacional = trim((string)($visita['agenda_titulo'] ?? $visita['objetivo'] ?? 'Levantamento de riscos ocupacionais'));
                ?>

                <article class="visita-card prioridade-<?= htmlspecialchars($prioridadeClasse) ?> status-<?= htmlspecialchars($statusClasse) ?>">
                    <header class="visita-card-header">
                        <time class="visita-data" datetime="<?= htmlspecialchars((string)($visita['data_visita'] ?? '')) ?>">
                            <strong><?= htmlspecialchars($dia) ?></strong>
                            <span><?= htmlspecialchars($mes) ?></span>
                        </time>

                        <div class="visita-identificacao">
                            <div class="visita-card-title-row">
                                <h3 title="<?= htmlspecialchars($empresa) ?>"><?= htmlspecialchars($empresa) ?></h3>
                                <span class="visita-prioridade <?= htmlspecialchars($prioridadeClasse) ?>">
                                    <i class="fa-solid fa-flag"></i>
                                    <?= htmlspecialchars(visitaPrioridadeLabel($prioridade)) ?>
                                </span>
                            </div>

                            <p title="<?= htmlspecialchars($tituloOperacional) ?>">
                                <?= htmlspecialchars($tituloOperacional) ?>
                            </p>
                        </div>
                    </header>

                    <div class="visita-status-line">
                        <span class="visita-status <?= htmlspecialchars($statusClasse) ?>">
                            <i class="fa-solid <?= $statusClasse === 'concluida'
                                ? 'fa-circle-check'
                                : ($statusClasse === 'andamento' ? 'fa-spinner' : ($statusClasse === 'cancelada' ? 'fa-circle-xmark' : 'fa-hourglass-half')) ?>"></i>
                            <?= htmlspecialchars(visitaStatusLabel($status, $checklistStatus)) ?>
                        </span>
                        <small>#<?= str_pad((string)(int)$visita['id'], 4, '0', STR_PAD_LEFT) ?></small>
                    </div>

                    <div class="visita-card-body">
                        <div class="visita-info-item visita-info-wide">
                            <i class="fa-regular fa-building"></i>
                            <div>
                                <span>Unidade</span>
                                <strong title="<?= htmlspecialchars($unidade) ?>"><?= htmlspecialchars($unidade) ?></strong>
                                <?php if ($cnpj !== ''): ?>
                                    <small><?= htmlspecialchars($cnpj) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="visita-info-item">
                            <i class="fa-regular fa-calendar"></i>
                            <div>
                                <span>Data</span>
                                <strong><?= htmlspecialchars($dataCompleta) ?></strong>
                            </div>
                        </div>

                        <div class="visita-info-item">
                            <i class="fa-regular fa-clock"></i>
                            <div>
                                <span>Horário</span>
                                <strong><?= htmlspecialchars($periodo) ?></strong>
                            </div>
                        </div>

                        <div class="visita-info-item">
                            <i class="fa-solid fa-user-shield"></i>
                            <div>
                                <span>Técnico responsável</span>
                                <strong title="<?= htmlspecialchars($visita['tecnico_nome'] ?? '-') ?>">
                                    <?= htmlspecialchars($visita['tecnico_nome'] ?? '-') ?>
                                </strong>
                            </div>
                        </div>

                        <div class="visita-info-item">
                            <i class="fa-solid fa-user-tie"></i>
                            <div>
                                <span>Acompanhante</span>
                                <strong title="<?= htmlspecialchars($responsavel !== '' ? $responsavel : 'Não informado') ?>">
                                    <?= htmlspecialchars($responsavel !== '' ? $responsavel : 'Não informado') ?>
                                </strong>
                            </div>
                        </div>

                        <div class="visita-info-item visita-info-wide">
                            <i class="fa-solid fa-location-dot"></i>
                            <div>
                                <span>Local da visita</span>
                                <strong title="<?= htmlspecialchars(visitaEnderecoResumo($visita)) ?>">
                                    <?= htmlspecialchars(visitaEnderecoResumo($visita)) ?>
                                </strong>
                            </div>
                        </div>

                        <div class="visita-info-item visita-info-wide visita-vehicle-row">
                            <i class="fa-solid fa-car-side"></i>
                            <div>
                                <span>Veículo</span>
                                <strong><?= htmlspecialchars($veiculoResumo) ?></strong>
                            </div>
                        </div>
                    </div>

                    <footer class="visita-card-actions">
                        <a
                            href="<?= BASE_URL ?>/visitas/visualizar/<?= (int)$visita['id'] ?>"
                            class="btn btn-outline-secondary visita-action-secondary"
                        >
                            <i class="fa-regular fa-eye"></i>
                            Visualizar
                        </a>

                        <?php if ($abaAtual === 'abertas' && empty($visita['checklist_id'])): ?>
                            <form
                                action="<?= BASE_URL ?>/checklists/iniciar/<?= (int)$visita['id'] ?>"
                                method="POST"
                                class="visita-action-form"
                                onsubmit="return confirm('Deseja iniciar o check-list desta visita técnica?');"
                            >
                                <button type="submit" class="btn btn-primary visita-action-primary">
                                    <i class="fa-solid fa-play"></i>
                                    Iniciar Check-list
                                </button>
                            </form>
                        <?php elseif (!empty($visita['checklist_id'])): ?>
                            <a
                                href="<?= BASE_URL ?>/checklists/visualizar/<?= (int)$visita['checklist_id'] ?>"
                                class="btn btn-primary visita-action-primary"
                            >
                                <i class="fa-solid <?= $checklistStatus === 'CONCLUIDO' ? 'fa-file-circle-check' : 'fa-clipboard-check' ?>"></i>
                                <?= $checklistStatus === 'CONCLUIDO' ? 'Ver Check-list' : 'Continuar Check-list' ?>
                            </a>
                        <?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>

            <?php if (empty($visitas)): ?>
                <div class="visitas-empty-state">
                    <span class="visitas-empty-icon">
                        <i class="fa-regular fa-calendar-check"></i>
                    </span>
                    <span class="visitas-section-eyebrow">Fila organizada</span>
                    <h3>Nenhuma visita encontrada nesta etapa</h3>
                    <p>
                        <?= $haFiltros
                            ? 'Não há registros que correspondam aos filtros aplicados.'
                            : 'Os compromissos criados na Agenda aparecerão aqui conforme o técnico responsável e o status operacional.' ?>
                    </p>
                    <div class="visitas-empty-actions">
                        <?php if ($haFiltros): ?>
                            <a href="<?= BASE_URL ?>/visitas?aba=<?= urlencode($abaAtual) ?>" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-filter-circle-xmark"></i>
                                Remover filtros
                            </a>
                        <?php endif; ?>

                        <a href="<?= BASE_URL ?>/agenda" class="btn btn-primary">
                            <i class="fa-regular fa-calendar"></i>
                            Abrir Agenda
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>