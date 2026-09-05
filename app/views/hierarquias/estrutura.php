<?php
$rotaAtual = 'hierarquias';
$css = 'hierarquias.css';
require_once dirname(__DIR__) . '/templates/header.php';

$estrutura = $estrutura ?? [];
$funcionariosEmpresa = $funcionariosEmpresa ?? [];
$agrupada = [];
$funcionariosPorHierarquia = [];
$funcionariosPorUnidade = [];

foreach ($estrutura as $linha) {
    $agrupada[$linha['unidade_nome']][$linha['setor_nome']][] = $linha;
}
foreach ($funcionariosEmpresa as $funcionario) {
    $funcionariosPorHierarquia[(int)$funcionario['hierarquia_id']][] = $funcionario;
    $funcionariosPorUnidade[(int)$funcionario['unidade_id']][] = $funcionario;
}

$totalFuncionarios = array_sum(array_map(fn(array $linha): int => (int)($linha['total_funcionarios'] ?? 0), $estrutura));
$totalGhes = array_sum(array_map(fn(array $linha): int => (int)($linha['total_ghes'] ?? 0), $estrutura));
$totalSetores = count(array_unique(array_map(fn(array $linha): string => (string)$linha['setor_nome'], $estrutura)));
?>
<div class="org-page"><div class="org-container">
    <?php foreach (['sucesso' => 'success', 'erro' => 'danger'] as $chave => $tipo): ?>
        <?php if (!empty($_SESSION[$chave])): ?>
            <div class="alert alert-<?= $tipo ?> org-alert alert-dismissible fade show">
                <i class="fa-solid <?= $tipo === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2"></i><?= htmlspecialchars($_SESSION[$chave]) ?>
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION[$chave]); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <header class="org-header">
        <div class="org-header-main">
            <div class="org-header-icon"><i class="fa-regular fa-building"></i></div>
            <div class="org-header-copy">
                <div class="org-eyebrow">Estrutura oficial da empresa</div>
                <h1><?= htmlspecialchars($empresa['empresa_nome'] ?? 'Empresa') ?></h1>
                <p>CNPJ: <?= htmlspecialchars((string)($empresa['cnpj'] ?? '-')) ?> · Empresa → Unidade → Setor → Cargo → Funcionário</p>
            </div>
        </div>
        <div class="org-header-actions">
            <a href="<?= BASE_URL ?>/hierarquias" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            <a href="<?= BASE_URL ?>/hierarquias/criar?empresa_id=<?= (int)($empresa['id'] ?? 0) ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Expandir estrutura</a>
        </div>
    </header>

    <section class="org-flow-banner">
        <div class="org-flow-step is-complete"><span>1</span><div><strong>Empresa</strong><small>Cadastrada</small></div></div>
        <i class="fa-solid fa-chevron-right"></i>
        <div class="org-flow-step is-complete"><span>2</span><div><strong>Unidades</strong><small>Vinculadas</small></div></div>
        <i class="fa-solid fa-chevron-right"></i>
        <div class="org-flow-step is-complete"><span>3</span><div><strong>Setores</strong><small>Catálogo global</small></div></div>
        <i class="fa-solid fa-chevron-right"></i>
        <div class="org-flow-step is-complete"><span>4</span><div><strong>Cargos</strong><small>Alocados</small></div></div>
        <i class="fa-solid fa-chevron-right"></i>
        <div class="org-flow-step is-current"><span>5</span><div><strong>Funcionários</strong><small>Selecionar por cargo</small></div></div>
    </section>

    <section class="org-kpis">
        <article class="org-kpi"><div class="org-kpi-icon"><i class="fa-solid fa-sitemap"></i></div><div><span>Vínculos hierárquicos</span><strong><?= count($estrutura) ?></strong></div></article>
        <article class="org-kpi org-kpi-green"><div class="org-kpi-icon"><i class="fa-solid fa-layer-group"></i></div><div><span>Setores</span><strong><?= $totalSetores ?></strong></div></article>
        <article class="org-kpi org-kpi-purple"><div class="org-kpi-icon"><i class="fa-solid fa-users"></i></div><div><span>Funcionários ativos</span><strong><?= $totalFuncionarios ?></strong></div></article>
        <article class="org-kpi org-kpi-orange"><div class="org-kpi-icon"><i class="fa-solid fa-people-group"></i></div><div><span>Vínculos com GHE</span><strong><?= $totalGhes ?></strong></div></article>
    </section>

    <div class="org-callout">
        <i class="fa-solid fa-circle-info"></i>
        <div>Os funcionários selecionados abaixo serão <strong>adicionados ou movidos</strong> para o cargo escolhido. Funcionários já alocados aparecem bloqueados no próprio cargo.</div>
    </div>

    <?php if ($agrupada): ?>
        <?php foreach ($agrupada as $unidade => $setores): ?>
            <?php
            $primeiraLinha = reset($setores);
            $primeiraLinha = is_array($primeiraLinha) ? reset($primeiraLinha) : null;
            $unidadeId = (int)($primeiraLinha['unidade_id'] ?? 0);
            $funcionariosUnidade = $funcionariosPorUnidade[$unidadeId] ?? [];
            ?>
            <section class="org-tree-unit">
                <header class="org-tree-unit-header">
                    <div class="org-tree-unit-title"><i class="fa-solid fa-industry"></i><div><h2><?= htmlspecialchars($unidade) ?></h2><small class="text-muted"><?= count($setores) ?> setor(es) · <?= count($funcionariosUnidade) ?> funcionário(s)</small></div></div>
                    <span class="org-status org-status-active"><i class="fa-solid fa-circle"></i>Estrutura ativa</span>
                </header>

                <div class="org-tree-unit-body">
                    <?php foreach ($setores as $setor => $linhas): ?>
                        <article class="org-tree-sector">
                            <div class="org-tree-sector-title"><i class="fa-solid fa-layer-group text-primary"></i><?= htmlspecialchars($setor) ?></div>

                            <?php foreach ($linhas as $linha): ?>
                                <?php
                                $hierarquiaId = (int)$linha['id'];
                                $atuais = $funcionariosPorHierarquia[$hierarquiaId] ?? [];
                                $collapseId = 'alocarFuncionarios' . $hierarquiaId;
                                ?>
                                <div class="org-tree-role org-tree-role-expanded">
                                    <div class="org-tree-role-main">
                                        <div>
                                            <h3><?= htmlspecialchars($linha['cargo_nome']) ?></h3>
                                            <p><?= !empty($linha['cbo']) ? 'CBO ' . htmlspecialchars($linha['cbo']) . ' · ' : '' ?><?= count($atuais) ?> funcionário(s) · <?= (int)$linha['total_ghes'] ?> GHE(s)</p>
                                        </div>
                                        <div class="org-tree-actions">
                                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                                                <i class="fa-solid fa-user-plus"></i> Alocar funcionários
                                            </button>
                                            <a href="<?= BASE_URL ?>/hierarquias/editar/<?= $hierarquiaId ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                                            <a href="<?= BASE_URL ?>/hierarquias/excluir/<?= $hierarquiaId ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir esta linha da hierarquia? A operação será bloqueada se houver vínculos.')"><i class="fa-solid fa-trash-can"></i></a>
                                        </div>
                                    </div>

                                    <div class="org-employee-chips">
                                        <?php if ($atuais): ?>
                                            <?php foreach ($atuais as $funcionario): ?>
                                                <span><i class="fa-regular fa-user"></i><?= htmlspecialchars($funcionario['nome']) ?><?= !empty($funcionario['matricula']) ? ' · ' . htmlspecialchars($funcionario['matricula']) : '' ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="is-empty"><i class="fa-solid fa-user-slash"></i>Nenhum funcionário alocado</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="collapse org-allocation-panel" id="<?= $collapseId ?>">
                                        <form action="<?= BASE_URL ?>/hierarquias/alocar-funcionarios/<?= $hierarquiaId ?>" method="post" data-allocation-form>
                                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                            <div class="org-allocation-header">
                                                <div><strong>Selecionar funcionários da unidade</strong><small>Ao selecionar alguém de outro cargo, o sistema realizará a transferência.</small></div>
                                                <input type="search" class="form-control" placeholder="Buscar funcionário" data-employee-search>
                                            </div>

                                            <?php if ($funcionariosUnidade): ?>
                                                <div class="org-employee-selector">
                                                    <?php foreach ($funcionariosUnidade as $funcionario): ?>
                                                        <?php $jaAlocado = (int)$funcionario['hierarquia_id'] === $hierarquiaId; ?>
                                                        <label class="org-employee-option <?= $jaAlocado ? 'is-current' : '' ?>" data-employee-name="<?= htmlspecialchars(strtolower((string)$funcionario['nome'])) ?>">
                                                            <input type="checkbox" name="funcionarios[]" value="<?= (int)$funcionario['id'] ?>" <?= $jaAlocado ? 'checked disabled' : '' ?>>
                                                            <span class="org-employee-avatar"><i class="fa-regular fa-user"></i></span>
                                                            <span><strong><?= htmlspecialchars($funcionario['nome']) ?></strong><small><?= htmlspecialchars(($funcionario['setor_nome'] ?? '-') . ' · ' . ($funcionario['cargo_nome'] ?? '-')) ?></small></span>
                                                            <?php if ($jaAlocado): ?><em>Já alocado</em><?php else: ?><i class="fa-solid fa-arrow-right"></i><?php endif; ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="org-allocation-actions">
                                                    <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-check"></i> Alocar selecionados</button>
                                                </div>
                                            <?php else: ?>
                                                <div class="org-builder-empty org-builder-empty-small">
                                                    <i class="fa-solid fa-users-slash"></i><strong>Nenhum funcionário cadastrado nesta unidade</strong>
                                                    <a href="<?= BASE_URL ?>/funcionarios/criar" class="btn btn-sm btn-primary">Cadastrar funcionário</a>
                                                </div>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="org-empty"><div class="org-empty-icon"><i class="fa-solid fa-sitemap"></i></div><h2>Empresa sem estrutura montada</h2><p>Selecione a unidade, os setores globais e os cargos que existirão em cada setor.</p><a href="<?= BASE_URL ?>/hierarquias/criar" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Montar estrutura</a></div>
    <?php endif; ?>
</div></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-employee-search]').forEach(function (input) {
        input.addEventListener('input', function () {
            const termo = this.value.toLocaleLowerCase('pt-BR').trim();
            const form = this.closest('form');
            form?.querySelectorAll('[data-employee-name]').forEach(function (item) {
                item.classList.toggle('d-none', termo !== '' && !(item.dataset.employeeName || '').includes(termo));
            });
        });
    });

    document.querySelectorAll('[data-allocation-form]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const selecionados = form.querySelectorAll('input[name="funcionarios[]"]:checked:not(:disabled)');
            if (selecionados.length === 0) {
                event.preventDefault();
                window.alert('Selecione ao menos um funcionário para adicionar ou mover para este cargo.');
                return;
            }
            const button = form.querySelector('button[type="submit"]');
            if (button && !button.disabled) {
                button.disabled = true;
                button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Alocando...';
            }
        });
    });
});
</script>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
