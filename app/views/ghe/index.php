<?php
$css = 'ghe.css';
require_once dirname(__DIR__) . '/templates/header.php';

$ghes = $ghes ?? [];
$indicadores = $indicadores ?? [];
$empresas = $empresas ?? [];
$unidades = $unidades ?? [];
$filtros = $filtros ?? [];
$csrfToken = $csrfToken ?? '';

$statusChecklistLabel = static fn(string $status): string => match (strtoupper($status)) {
    'ABERTO' => 'Aberto',
    'EM_ANDAMENTO' => 'Em andamento',
    'CONCLUIDO' => 'Concluído',
    'CANCELADO' => 'Cancelado',
    default => ucfirst(strtolower($status)),
};
?>

<div class="ghe-page">
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

    <header class="ghe-page-header">
        <div>
            <h2>Grupos Homogêneos de Exposição</h2>
            <p>Gerencie cargos, exposições e riscos levantados em cada check-list.</p>
        </div>
        <a href="<?= BASE_URL ?>/ghe/criar" class="btn btn-primary ghe-new-button">
            <i class="fa-solid fa-people-group"></i>
            Novo GHE
        </a>
    </header>

    <section class="ghe-kpis">
        <article class="ghe-kpi-card">
            <div class="ghe-kpi-icon ghe-kpi-blue"><i class="fa-solid fa-people-group"></i></div>
            <div><span>Total</span><strong><?= (int)($indicadores['total'] ?? 0) ?></strong></div>
        </article>
        <article class="ghe-kpi-card">
            <div class="ghe-kpi-icon ghe-kpi-green"><i class="fa-solid fa-circle-check"></i></div>
            <div><span>Ativos</span><strong><?= (int)($indicadores['ativos'] ?? 0) ?></strong></div>
        </article>
        <article class="ghe-kpi-card">
            <div class="ghe-kpi-icon ghe-kpi-red"><i class="fa-solid fa-circle-xmark"></i></div>
            <div><span>Inativos</span><strong><?= (int)($indicadores['inativos'] ?? 0) ?></strong></div>
        </article>
        <article class="ghe-kpi-card">
            <div class="ghe-kpi-icon ghe-kpi-purple"><i class="fa-solid fa-chart-line"></i></div>
            <div><span>Riscos quantificáveis</span><strong><?= (int)($indicadores['quantificaveis'] ?? 0) ?></strong></div>
        </article>
    </section>

    <section class="ghe-filter-card">
        <form method="GET" action="<?= BASE_URL ?>/ghe" class="ghe-filter-grid">
            <div class="ghe-filter-field ghe-filter-search">
                <label for="busca">Busca</label>
                <input type="search" id="busca" name="busca" class="form-control"
                       placeholder="Código, GHE, empresa, unidade ou técnico"
                       value="<?= htmlspecialchars((string)($filtros['busca'] ?? '')) ?>">
            </div>

            <div class="ghe-filter-field">
                <label for="empresa_id">Empresa</label>
                <select id="empresa_id" name="empresa_id" class="form-select">
                    <option value="">Todas as empresas</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= (int)$empresa['id'] ?>"
                            <?= (string)($filtros['empresa_id'] ?? '') === (string)$empresa['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia'] ?: $empresa['razao_social']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="ghe-filter-field">
                <label for="unidade_id">Unidade</label>
                <select id="unidade_id" name="unidade_id" class="form-select">
                    <option value="">Todas as unidades</option>
                    <?php foreach ($unidades as $unidade): ?>
                        <option value="<?= (int)$unidade['id'] ?>"
                                data-empresa="<?= (int)$unidade['empresa_id'] ?>"
                            <?= (string)($filtros['unidade_id'] ?? '') === (string)$unidade['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($unidade['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="ghe-filter-field">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="ATIVO" <?= ($filtros['status'] ?? '') === 'ATIVO' ? 'selected' : '' ?>>Ativos</option>
                    <option value="INATIVO" <?= ($filtros['status'] ?? '') === 'INATIVO' ? 'selected' : '' ?>>Inativos</option>
                </select>
            </div>

            <div class="ghe-filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Filtrar
                </button>
                <a href="<?= BASE_URL ?>/ghe" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate-left"></i> Limpar
                </a>
            </div>
        </form>
    </section>

    <?php if (!empty($ghes)): ?>
        <section class="ghe-grid">
            <?php foreach ($ghes as $ghe): ?>
                <?php
                $ativo = (int)($ghe['ativo'] ?? 0) === 1;
                $checklistStatus = strtoupper((string)($ghe['checklist_status'] ?? ''));
                $editavel = $ativo && in_array($checklistStatus, ['ABERTO', 'EM_ANDAMENTO'], true);
                ?>
                <article class="ghe-card-item <?= $ativo ? '' : 'ghe-card-inativo' ?>">
                    <header class="ghe-card-header">
                        <div class="ghe-code-box"><?= htmlspecialchars($ghe['codigo']) ?></div>
                        <div class="ghe-card-title">
                            <h3><?= htmlspecialchars($ghe['nome']) ?></h3>
                            <p><?= htmlspecialchars($ghe['empresa_nome'] ?? '-') ?></p>
                        </div>
                        <span class="ghe-status <?= $ativo ? 'status-ativo' : 'status-inativo' ?>">
                            <?= $ativo ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </header>

                    <div class="ghe-checklist-line status-<?= strtolower($checklistStatus) ?>">
                        <span>Check-list #<?= (int)$ghe['checklist_id'] ?></span>
                        <strong><?= htmlspecialchars($statusChecklistLabel($checklistStatus)) ?></strong>
                    </div>

                    <div class="ghe-card-body">
                        <div class="ghe-info">
                            <i class="fa-solid fa-industry"></i>
                            <div><span>Unidade</span><strong><?= htmlspecialchars($ghe['unidade_nome'] ?? 'Matriz') ?></strong></div>
                        </div>
                        <div class="ghe-info">
                            <i class="fa-regular fa-calendar"></i>
                            <div><span>Visita</span><strong><?= !empty($ghe['data_visita']) ? date('d/m/Y', strtotime($ghe['data_visita'])) : '-' ?> <?= !empty($ghe['hora_visita']) ? ' às ' . substr($ghe['hora_visita'], 0, 5) : '' ?></strong></div>
                        </div>
                        <div class="ghe-info">
                            <i class="fa-solid fa-user-shield"></i>
                            <div><span>Técnico</span><strong><?= htmlspecialchars($ghe['tecnico_nome'] ?? '-') ?></strong></div>
                        </div>
                        <div class="ghe-info">
                            <i class="fa-solid fa-briefcase"></i>
                            <div><span>Cargos vinculados</span><strong><?= (int)($ghe['total_cargos'] ?? 0) ?></strong></div>
                        </div>
                        <div class="ghe-info">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <div><span>Riscos aplicados</span><strong><?= (int)($ghe['total_riscos'] ?? 0) ?></strong></div>
                        </div>
                        <div class="ghe-info">
                            <i class="fa-solid fa-chart-line"></i>
                            <div><span>Quantificáveis</span><strong><?= (int)($ghe['total_quantificaveis'] ?? 0) ?></strong></div>
                        </div>
                    </div>

                    <footer class="ghe-card-actions">
                        <a href="<?= BASE_URL ?>/ghe/visualizar/<?= (int)$ghe['id'] ?>" class="btn btn-outline-primary">
                            <i class="fa-regular fa-eye"></i> Visualizar
                        </a>

                        <?php if ($editavel): ?>
                            <a href="<?= BASE_URL ?>/ghe/editar/<?= (int)$ghe['id'] ?>" class="btn btn-primary">
                                <i class="fa-regular fa-pen-to-square"></i> Editar
                            </a>
                        <?php elseif (!$ativo && in_array($checklistStatus, ['ABERTO', 'EM_ANDAMENTO'], true)): ?>
                            <form method="POST" action="<?= BASE_URL ?>/ghe/reativar/<?= (int)$ghe['id'] ?>">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="btn btn-outline-success">
                                    <i class="fa-solid fa-rotate-left"></i> Reativar
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/checklists/visualizar/<?= (int)$ghe['checklist_id'] ?>?aba=ghe-riscos" class="btn btn-outline-secondary">
                                <i class="fa-regular fa-square-check"></i> Check-list
                            </a>
                        <?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <section class="ghe-empty-state">
            <i class="fa-solid fa-people-group"></i>
            <h3>Nenhum GHE encontrado</h3>
            <p>Crie o primeiro GHE em um check-list em andamento.</p>
            <a href="<?= BASE_URL ?>/ghe/criar" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Criar GHE
            </a>
        </section>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const empresa = document.getElementById('empresa_id');
    const unidade = document.getElementById('unidade_id');
    if (!empresa || !unidade) return;

    const filtrar = function () {
        const empresaId = empresa.value;
        Array.from(unidade.options).forEach(function (option, index) {
            if (index === 0) return;
            option.hidden = empresaId !== '' && option.dataset.empresa !== empresaId;
        });
        if (unidade.selectedOptions[0]?.hidden) unidade.value = '';
    };

    empresa.addEventListener('change', filtrar);
    filtrar();
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
