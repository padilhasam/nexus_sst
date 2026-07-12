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

$totalAgendamentos = count($agendamentos);

$totalAgendados = count(array_filter(
    $agendamentos,
    static fn(array $item): bool => ($item['status'] ?? '') === 'AGENDADO'
));

$totalConfirmados = count(array_filter(
    $agendamentos,
    static fn(array $item): bool => ($item['status'] ?? '') === 'CONFIRMADO'
));

$totalCancelados = count(array_filter(
    $agendamentos,
    static fn(array $item): bool => ($item['status'] ?? '') === 'CANCELADO'
));

function badgeStatusAgenda(string $status): string
{
    return match ($status) {
        'AGENDADO'   => 'agenda-status agenda-status-agendado',
        'CONFIRMADO' => 'agenda-status agenda-status-confirmado',
        'REAGENDADO' => 'agenda-status agenda-status-reagendado',
        'CANCELADO'  => 'agenda-status agenda-status-cancelado',
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
        'CONCLUIDO'  => 'Concluído',
        'EXCLUIDO'   => 'Excluído',
        default      => ucfirst(strtolower($status)),
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

                    <div class="table-responsive">
                        <table class="table agenda-table align-middle">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Horário</th>
                                    <th>Empresa / Unidade</th>
                                    <th>Técnico</th>
                                    <th>Prioridade</th>
                                    <th>Status</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($agendamentos as $item): ?>
                                    <?php
                                    $empresa = !empty($item['empresa_fantasia'])
                                        ? $item['empresa_fantasia']
                                        : ($item['empresa_nome'] ?? 'Empresa não informada');

                                    $unidade = !empty($item['unidade_nome'])
                                        ? $item['unidade_nome']
                                        : 'Matriz';

                                    $status = $item['status'] ?? '';
                                    $prioridade = $item['prioridade'] ?? 'PADRAO';
                                    ?>

                                    <tr>
                                        <td>
                                            <div class="agenda-date-cell">
                                                <strong>
                                                    <?= !empty($item['data_agendada'])
                                                        ? date('d/m/Y', strtotime($item['data_agendada']))
                                                        : '-' ?>
                                                </strong>

                                                <?php if (!empty($item['data_agendada'])): ?>
                                                    <span>
                                                        <?= date(
                                                            'D',
                                                            strtotime($item['data_agendada'])
                                                        ) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="agenda-time-cell">
                                                <i class="fa-regular fa-clock"></i>

                                                <span>
                                                    <?= !empty($item['hora_inicio'])
                                                        ? substr($item['hora_inicio'], 0, 5)
                                                        : '-' ?>

                                                    <?= !empty($item['hora_fim'])
                                                        ? ' às ' . substr($item['hora_fim'], 0, 5)
                                                        : '' ?>
                                                </span>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="agenda-company-cell">
                                                <strong><?= htmlspecialchars($empresa) ?></strong>
                                                <span><?= htmlspecialchars($unidade) ?></span>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="agenda-technician-cell">
                                                <div class="agenda-technician-avatar">
                                                    <?= strtoupper(substr(
                                                        $item['tecnico_nome'] ?? 'T',
                                                        0,
                                                        1
                                                    )) ?>
                                                </div>

                                                <span>
                                                    <?= htmlspecialchars(
                                                        $item['tecnico_nome'] ?? '-'
                                                    ) ?>
                                                </span>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="agenda-priority agenda-priority-<?= strtolower($prioridade) ?>">
                                                <?= htmlspecialchars(
                                                    $prioridade === 'PADRAO'
                                                        ? 'Padrão'
                                                        : ucfirst(strtolower($prioridade))
                                                ) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="<?= badgeStatusAgenda($status) ?>">
                                                <?= htmlspecialchars(labelStatusAgenda($status)) ?>
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <div class="agenda-actions">
                                                <a
                                                    href="<?= BASE_URL ?>/agenda/visualizar/<?= (int)$item['id'] ?>"
                                                    class="btn btn-sm btn-light"
                                                    title="Visualizar"
                                                >
                                                    <i class="fa-regular fa-eye"></i>
                                                </a>

                                                <?php if (!in_array(
                                                    $status,
                                                    ['CANCELADO', 'EXCLUIDO', 'CONCLUIDO'],
                                                    true
                                                )): ?>
                                                    <a
                                                        href="<?= BASE_URL ?>/agenda/editar/<?= (int)$item['id'] ?>"
                                                        class="btn btn-sm btn-primary"
                                                        title="Editar"
                                                    >
                                                        <i class="fa-regular fa-pen-to-square"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($agendamentos)): ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="agenda-empty-state">
                                                <i class="fa-regular fa-calendar-xmark"></i>
                                                <h3>Nenhum agendamento encontrado</h3>
                                                <p>
                                                    Não existem agendamentos correspondentes
                                                    aos filtros selecionados.
                                                </p>

                                                <a
                                                    href="<?= BASE_URL ?>/agenda/criar"
                                                    class="btn btn-primary"
                                                >
                                                    <i class="fa-solid fa-plus"></i>
                                                    Criar agendamento
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

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