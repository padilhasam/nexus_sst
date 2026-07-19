<?php
    $css = 'agenda.css';
    require_once dirname(__DIR__) . '/templates/header.php';
?>

<?php
$agendamentos = $agendamentos ?? [];
$empresas = $empresas ?? [];
$usuarios = $usuarios ?? [];
$filtros = $filtros ?? [];
$eventosJson = $eventosJson ?? '[]';

$indicadores = $indicadores ?? [];
$paginacao = $paginacao ?? [];

$totalAgendamentos = (int)($indicadores['total'] ?? count($agendamentos));
$totalAgendados = (int)($indicadores['agendados'] ?? 0);
$totalConfirmados = (int)($indicadores['confirmados'] ?? 0);
$totalCancelados = (int)($indicadores['cancelados'] ?? 0);

$paginaAtual = max(1, (int)($paginacao['pagina_atual'] ?? 1));
$porPagina = (int)($paginacao['por_pagina'] ?? 10);
$totalRegistros = (int)($paginacao['total_registros'] ?? $totalAgendamentos);
$totalPaginas = max(1, (int)($paginacao['total_paginas'] ?? 1));
$opcoesPorPagina = $paginacao['opcoes_por_pagina'] ?? [10, 20, 50];

$primeiroRegistro = $totalRegistros > 0
    ? (($paginaAtual - 1) * $porPagina) + 1
    : 0;

$ultimoRegistro = min(
    $paginaAtual * $porPagina,
    $totalRegistros
);

$queryBase = array_filter([
    'status' => $filtros['status'] ?? '',
    'data_inicio' => $filtros['data_inicio'] ?? '',
    'data_fim' => $filtros['data_fim'] ?? '',
    'empresa_id' => $filtros['empresa_id'] ?? '',
    'tecnico_id' => $filtros['tecnico_id'] ?? '',
    'por_pagina' => $porPagina,
    'aba' => 'lista',
], static fn($valor): bool => $valor !== '' && $valor !== null);

$urlPagina = static function (int $pagina) use ($queryBase): string {
    return BASE_URL . '/agenda?' . http_build_query(
        array_merge($queryBase, ['pagina' => $pagina])
    );
};

function badgeStatusAgenda(string $status): string
{
    return match ($status) {
        'AGENDADO'   => 'agenda-status agenda-status-agendado',
        'CONFIRMADO' => 'agenda-status agenda-status-confirmado',
        'REAGENDADO' => 'agenda-status agenda-status-reagendado',
        'CANCELADO'  => 'agenda-status agenda-status-cancelado',
        'EM_ANDAMENTO' => 'agenda-status agenda-status-em-andamento',
        'CONCLUIDO'  => 'agenda-status agenda-status-concluido',
        'EXCLUIDO'   => 'agenda-status agenda-status-excluido',
        default      => 'agenda-status agenda-status-padrao',
    };
}

function labelStatusAgenda(string $status): string
{
    return match ($status) {
        'AGENDADO'   => 'Agendado',
        'CONFIRMADO' => 'Confirmado',
        'REAGENDADO' => 'Reagendado',
        'CANCELADO'  => 'Cancelado',
        'EM_ANDAMENTO' => 'Em andamento',
        'CONCLUIDO'  => 'Concluído',
        'EXCLUIDO'   => 'Excluído',
        default      => ucfirst(strtolower($status)),
    };
}


function agendaMesAbreviado(int $mes): string
{
    return [
        1 => 'JAN', 2 => 'FEV', 3 => 'MAR', 4 => 'ABR',
        5 => 'MAI', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
        9 => 'SET', 10 => 'OUT', 11 => 'NOV', 12 => 'DEZ',
    ][$mes] ?? '---';
}

function agendaPrioridadeLabel(string $prioridade): string
{
    return match (strtoupper($prioridade)) {
        'CRITICA' => 'Crítica',
        'URGENTE' => 'Urgente',
        default => 'Padrão',
    };
}

function agendaPrioridadeClasse(string $prioridade): string
{
    return match (strtoupper($prioridade)) {
        'CRITICA' => 'critica',
        'URGENTE' => 'urgente',
        default => 'padrao',
    };
}

function agendaStatusCardClasse(string $status): string
{
    return match (strtoupper($status)) {
        'EM_ANDAMENTO' => 'andamento',
        'CONCLUIDO' => 'concluida',
        'CANCELADO', 'EXCLUIDO' => 'cancelada',
        'REAGENDADO' => 'reagendada',
        default => 'aguardando',
    };
}

function agendaStatusIcone(string $classe): string
{
    return match ($classe) {
        'concluida' => 'fa-circle-check',
        'andamento' => 'fa-spinner',
        'cancelada' => 'fa-circle-xmark',
        'reagendada' => 'fa-calendar-days',
        default => 'fa-hourglass-half',
    };
}
?>

<div class="agenda-page">

    <?php if (!empty($_SESSION['sucesso'])): ?>
        <div class="alert alert-success rounded-4 alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= htmlspecialchars($_SESSION['sucesso']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger rounded-4 alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?= htmlspecialchars($_SESSION['erro']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <div class="agenda-kpis">
        <div class="agenda-kpi-card">
            <div class="agenda-kpi-icon agenda-kpi-blue">
                <i class="fa-regular fa-calendar"></i>
            </div>
            <div>
                <span>Total</span>
                <strong><?= $totalAgendamentos ?></strong>
            </div>
        </div>

        <div class="agenda-kpi-card">
            <div class="agenda-kpi-icon agenda-kpi-orange">
                <i class="fa-regular fa-clock"></i>
            </div>
            <div>
                <span>Agendados</span>
                <strong><?= $totalAgendados ?></strong>
            </div>
        </div>

        <div class="agenda-kpi-card">
            <div class="agenda-kpi-icon agenda-kpi-green">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span>Confirmados</span>
                <strong><?= $totalConfirmados ?></strong>
            </div>
        </div>

        <div class="agenda-kpi-card">
            <div class="agenda-kpi-icon agenda-kpi-red">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <span>Cancelados</span>
                <strong><?= $totalCancelados ?></strong>
            </div>
        </div>
    </div>

    <div class="agenda-filter-card">
        <form method="GET" action="<?= BASE_URL ?>/agenda" class="agenda-filter-grid">

            <div class="agenda-filter-field">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todos os status</option>

                    <?php foreach (
                        ['AGENDADO', 'CONFIRMADO', 'REAGENDADO', 'CANCELADO', 'CONCLUIDO']
                        as $status
                    ): ?>
                        <option
                            value="<?= $status ?>"
                            <?= (($filtros['status'] ?? '') === $status) ? 'selected' : '' ?>
                        >
                            <?= labelStatusAgenda($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="agenda-filter-field">
                <label for="data_inicio">Data inicial</label>
                <input
                    type="date"
                    name="data_inicio"
                    id="data_inicio"
                    value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>"
                    class="form-control"
                >
            </div>

            <div class="agenda-filter-field">
                <label for="data_fim">Data final</label>
                <input
                    type="date"
                    name="data_fim"
                    id="data_fim"
                    value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>"
                    class="form-control"
                >
            </div>

            <div class="agenda-filter-field">
                <label for="empresa_id">Empresa</label>
                <select name="empresa_id" id="empresa_id" class="form-select">
                    <option value="">Todas as empresas</option>

                    <?php foreach ($empresas as $empresa): ?>
                        <?php
                        $nomeEmpresa = !empty($empresa['nome_fantasia'])
                            ? $empresa['nome_fantasia']
                            : $empresa['razao_social'];
                        ?>

                        <option
                            value="<?= (int)$empresa['id'] ?>"
                            <?= (
                                (string)($filtros['empresa_id'] ?? '') ===
                                (string)$empresa['id']
                            ) ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($nomeEmpresa) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="agenda-filter-field">
                <label for="tecnico_id">Técnico</label>
                <select name="tecnico_id" id="tecnico_id" class="form-select">
                    <option value="">Todos os técnicos</option>

                    <?php foreach ($usuarios as $tecnico): ?>
                        <option
                            value="<?= (int)$tecnico['id'] ?>"
                            <?= (
                                (string)($filtros['tecnico_id'] ?? '') ===
                                (string)$tecnico['id']
                            ) ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($tecnico['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="agenda-filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i>
                    Filtrar
                </button>

                <a href="<?= BASE_URL ?>/agenda" class="btn btn-light">
                    <i class="fa-solid fa-rotate-left"></i>
                    Limpar
                </a>
            </div>

        </form>
    </div>

    <div class="agenda-tabs-wrapper">
        <ul class="nav nav-tabs agenda-tabs" id="agendaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link active"
                    id="calendario-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#tabCalendario"
                    type="button"
                    role="tab"
                >
                    <i class="fa-regular fa-calendar-days"></i>
                    Calendário
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button
                    class="nav-link"
                    id="lista-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#tabLista"
                    type="button"
                    role="tab"
                >
                    <i class="fa-solid fa-list"></i>
                    Lista completa
                    <span class="agenda-tab-count"><?= $totalAgendamentos ?></span>
                </button>
            </li>
        </ul>

        <div class="tab-content agenda-tab-content">

            <div
                class="tab-pane fade show active"
                id="tabCalendario"
                role="tabpanel"
            >
                <div class="agenda-calendar-card">
                    <div id="agendaCalendar"></div>
                </div>
            </div>

            <div
                class="tab-pane fade"
                id="tabLista"
                role="tabpanel"
            >
                <div class="agenda-list-card">
                    <div class="visitas-list-toolbar">
                        <div>
                            <strong><?= count($agendamentos) ?> agendamento<?= count($agendamentos) === 1 ? '' : 's' ?> exibido<?= count($agendamentos) === 1 ? '' : 's' ?></strong>
                            <span>Compromissos organizados por data, prioridade e horário.</span>
                        </div>

                        <span class="visitas-scope-badge">
                            <i class="fa-solid fa-layer-group"></i>
                            Lista completa
                        </span>
                    </div>

                    <div class="visitas-grid">
                        <?php foreach ($agendamentos as $item): ?>
                            <?php
                            $empresa = trim((string)($item['empresa_fantasia'] ?? ''));
                            if ($empresa === '') {
                                $empresa = trim((string)($item['empresa_nome'] ?? 'Empresa não informada'));
                            }

                            $unidade = trim((string)($item['unidade_nome'] ?? ''));
                            if ($unidade === '') {
                                $unidade = 'Matriz / unidade principal';
                            }

                            $statusOriginal = strtoupper((string)($item['status'] ?? 'AGENDADO'));
                            $statusVisual = strtoupper((string)($item['status_visual'] ?? $statusOriginal));
                            $statusClasse = agendaStatusCardClasse($statusVisual);
                            $prioridade = strtoupper((string)($item['prioridade'] ?? 'PADRAO'));
                            $prioridadeClasse = agendaPrioridadeClasse($prioridade);

                            $timestamp = !empty($item['data_agendada'])
                                ? strtotime((string)$item['data_agendada'])
                                : false;
                            $dia = $timestamp ? date('d', $timestamp) : '--';
                            $mes = $timestamp ? agendaMesAbreviado((int)date('n', $timestamp)) : '---';
                            $dataCompleta = $timestamp ? date('d/m/Y', $timestamp) : 'Data não informada';

                            $horaInicio = !empty($item['hora_inicio'])
                                ? substr((string)$item['hora_inicio'], 0, 5)
                                : '--:--';
                            $horaFim = !empty($item['hora_fim'])
                                ? substr((string)$item['hora_fim'], 0, 5)
                                : '';
                            $periodo = $horaInicio . ($horaFim !== '' ? ' às ' . $horaFim : '');

                            $titulo = trim((string)($item['titulo'] ?? ''));
                            if ($titulo === '') {
                                $titulo = trim((string)($item['objetivo'] ?? 'Visita técnica programada'));
                            }

                            $objetivo = trim((string)($item['objetivo'] ?? ''));
                            if ($objetivo === '') {
                                $objetivo = 'Levantamento de riscos ocupacionais';
                            }

                            $tecnico = trim((string)($item['tecnico_nome'] ?? 'Técnico não informado'));
                            $responsavel = trim((string)($item['responsavel_acompanhamento'] ?? ''));
                            $veiculo = trim((string)($item['veiculo_modelo'] ?? ''));
                            $placa = trim((string)($item['veiculo_placa'] ?? ''));
                            $veiculoResumo = $veiculo !== ''
                                ? $veiculo . ($placa !== '' ? ' · ' . $placa : '')
                                : 'Não definido';

                            $editavel = !in_array(
                                $statusOriginal,
                                ['CANCELADO', 'EXCLUIDO', 'CONCLUIDO'],
                                true
                            ) && !in_array($statusVisual, ['EM_ANDAMENTO', 'CONCLUIDO'], true);
                            ?>

                            <article class="visita-card prioridade-<?= htmlspecialchars($prioridadeClasse) ?> status-<?= htmlspecialchars($statusClasse) ?>">
                                <header class="visita-card-header">
                                    <time class="visita-data" datetime="<?= htmlspecialchars((string)($item['data_agendada'] ?? '')) ?>">
                                        <strong><?= htmlspecialchars($dia) ?></strong>
                                        <span><?= htmlspecialchars($mes) ?></span>
                                    </time>

                                    <div class="visita-identificacao">
                                        <div class="visita-card-title-row">
                                            <h3 title="<?= htmlspecialchars($empresa) ?>"><?= htmlspecialchars($empresa) ?></h3>
                                            <span class="visita-prioridade <?= htmlspecialchars($prioridadeClasse) ?>">
                                                <i class="fa-solid fa-flag"></i>
                                                <?= htmlspecialchars(agendaPrioridadeLabel($prioridade)) ?>
                                            </span>
                                        </div>

                                        <p title="<?= htmlspecialchars($titulo) ?>"><?= htmlspecialchars($titulo) ?></p>
                                    </div>
                                </header>

                                <div class="visita-status-line">
                                    <span class="visita-status <?= htmlspecialchars($statusClasse) ?>">
                                        <i class="fa-solid <?= htmlspecialchars(agendaStatusIcone($statusClasse)) ?>"></i>
                                        <?= htmlspecialchars(labelStatusAgenda($statusVisual)) ?>
                                    </span>
                                    <small>#<?= str_pad((string)(int)$item['id'], 4, '0', STR_PAD_LEFT) ?></small>
                                </div>

                                <div class="visita-card-body">
                                    <div class="visita-info-item visita-info-wide">
                                        <i class="fa-regular fa-building"></i>
                                        <div>
                                            <span>Unidade</span>
                                            <strong title="<?= htmlspecialchars($unidade) ?>"><?= htmlspecialchars($unidade) ?></strong>
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
                                            <strong title="<?= htmlspecialchars($tecnico) ?>"><?= htmlspecialchars($tecnico) ?></strong>
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
                                        <i class="fa-solid fa-bullseye"></i>
                                        <div>
                                            <span>Objetivo</span>
                                            <strong title="<?= htmlspecialchars($objetivo) ?>"><?= htmlspecialchars($objetivo) ?></strong>
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

                                <footer class="visita-card-actions <?= $editavel ? '' : 'is-single' ?>">
                                    <a
                                        href="<?= BASE_URL ?>/agenda/visualizar/<?= (int)$item['id'] ?>"
                                        class="btn btn-outline-secondary visita-action-secondary"
                                    >
                                        <i class="fa-regular fa-eye"></i>
                                        Visualizar
                                    </a>

                                    <?php if ($editavel): ?>
                                        <a
                                            href="<?= BASE_URL ?>/agenda/editar/<?= (int)$item['id'] ?>"
                                            class="btn btn-primary visita-action-primary"
                                        >
                                            <i class="fa-regular fa-pen-to-square"></i>
                                            Editar agenda
                                        </a>
                                    <?php endif; ?>
                                </footer>
                            </article>
                        <?php endforeach; ?>

                        <?php if (empty($agendamentos)): ?>
                            <div class="visitas-empty-state">
                                <span class="visitas-empty-icon">
                                    <i class="fa-regular fa-calendar-xmark"></i>
                                </span>
                                <h3>Nenhum agendamento encontrado</h3>
                                <p>Não existem agendamentos correspondentes aos filtros selecionados.</p>

                                <div class="visitas-empty-actions">
                                    <a href="<?= BASE_URL ?>/agenda/criar" class="btn btn-primary">
                                        <i class="fa-solid fa-plus"></i>
                                        Criar agendamento
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($totalRegistros > 0): ?>
                        <div class="agenda-pagination-bar">
                            <div class="agenda-pagination-summary">
                                Exibindo
                                <strong><?= $primeiroRegistro ?></strong>
                                a
                                <strong><?= $ultimoRegistro ?></strong>
                                de
                                <strong><?= $totalRegistros ?></strong>
                                agendamentos
                            </div>

                            <form
                                method="GET"
                                action="<?= BASE_URL ?>/agenda"
                                class="agenda-page-size"
                            >
                                <?php foreach ($filtros as $nome => $valor): ?>
                                    <?php if ($valor !== null && $valor !== ''): ?>
                                        <input
                                            type="hidden"
                                            name="<?= htmlspecialchars($nome) ?>"
                                            value="<?= htmlspecialchars((string)$valor) ?>"
                                        >
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <input type="hidden" name="aba" value="lista">

                                <label for="por_pagina">Itens por página</label>
                                <select
                                    name="por_pagina"
                                    id="por_pagina"
                                    class="form-select form-select-sm"
                                    onchange="this.form.submit()"
                                >
                                    <?php foreach ($opcoesPorPagina as $opcao): ?>
                                        <option
                                            value="<?= (int)$opcao ?>"
                                            <?= $porPagina === (int)$opcao ? 'selected' : '' ?>
                                        >
                                            <?= (int)$opcao ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($totalPaginas > 1): ?>
                                <nav aria-label="Paginação dos agendamentos">
                                    <ul class="pagination agenda-pagination mb-0">
                                        <li class="page-item <?= $paginaAtual <= 1 ? 'disabled' : '' ?>">
                                            <a
                                                class="page-link"
                                                href="<?= $paginaAtual > 1 ? $urlPagina($paginaAtual - 1) : '#' ?>"
                                                aria-label="Página anterior"
                                            >
                                                <i class="fa-solid fa-chevron-left"></i>
                                            </a>
                                        </li>

                                        <?php
                                        $inicioPagina = max(1, $paginaAtual - 2);
                                        $fimPagina = min($totalPaginas, $paginaAtual + 2);
                                        ?>

                                        <?php if ($inicioPagina > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= $urlPagina(1) ?>">1</a>
                                            </li>
                                            <?php if ($inicioPagina > 2): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">…</span>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php for ($pagina = $inicioPagina; $pagina <= $fimPagina; $pagina++): ?>
                                            <li class="page-item <?= $pagina === $paginaAtual ? 'active' : '' ?>">
                                                <a class="page-link" href="<?= $urlPagina($pagina) ?>">
                                                    <?= $pagina ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($fimPagina < $totalPaginas): ?>
                                            <?php if ($fimPagina < $totalPaginas - 1): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">…</span>
                                                </li>
                                            <?php endif; ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= $urlPagina($totalPaginas) ?>">
                                                    <?= $totalPaginas ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <li class="page-item <?= $paginaAtual >= $totalPaginas ? 'disabled' : '' ?>">
                                            <a
                                                class="page-link"
                                                href="<?= $paginaAtual < $totalPaginas ? $urlPagina($paginaAtual + 1) : '#' ?>"
                                                aria-label="Próxima página"
                                            >
                                                <i class="fa-solid fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

</div>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css"
>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const parametros = new URLSearchParams(window.location.search);

    if (parametros.get('aba') === 'lista') {
        const listaTab = document.getElementById('lista-tab');

        if (listaTab && typeof bootstrap !== 'undefined') {
            bootstrap.Tab.getOrCreateInstance(listaTab).show();
        }
    }

    const calendarElement = document.getElementById('agendaCalendar');

    if (!calendarElement || typeof FullCalendar === 'undefined') {
        return;
    }

    let eventos = [];

    try {
        eventos = <?= $eventosJson ?>;
    } catch (error) {
        console.error('Erro ao carregar eventos da agenda:', error);
        eventos = [];
    }

    const calendar = new FullCalendar.Calendar(calendarElement, {
        locale: 'pt-br',
        initialView: 'dayGridMonth',
        height: 'auto',
        expandRows: true,
        nowIndicator: true,
        navLinks: true,
        editable: false,
        selectable: false,
        dayMaxEvents: 3,
        eventDisplay: 'block',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },

        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            list: 'Lista'
        },

        events: eventos,

        eventClick: function (info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        },

        eventDidMount: function (info) {
            const status = info.event.extendedProps.status || '';
            const prioridade = info.event.extendedProps.prioridade || '';
            const tecnico = info.event.extendedProps.tecnico || '';
            const unidade = info.event.extendedProps.unidade || '';

            info.el.title =
                info.event.title +
                '\nTécnico: ' + tecnico +
                '\nUnidade: ' + unidade +
                '\nStatus: ' + status +
                '\nPrioridade: ' + prioridade;

            info.el.classList.add(
                'fc-event-status-' + status.toLowerCase()
            );
        },

        windowResize: function () {
            calendar.updateSize();
        }
    });

    calendar.render();

    const calendarioTab = document.getElementById('calendario-tab');

    if (calendarioTab) {
        calendarioTab.addEventListener('shown.bs.tab', function () {
            calendar.updateSize();
        });
    }
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>