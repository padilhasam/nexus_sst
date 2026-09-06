<?php
$rotaAtual = 'setores';
require_once dirname(__DIR__) . '/templates/header.php';

$setores = $setores ?? [];
$total = count($setores);
$ativos = count(array_filter($setores, fn(array $s): bool => !empty($s['ativo'])));
$emUso = count(array_filter($setores, fn(array $s): bool => (int)($s['total_unidades'] ?? 0) > 0));
$funcionarios = array_sum(array_map(fn(array $s): int => (int)($s['total_funcionarios'] ?? 0), $setores));
?>

<div class="org-page">
    <div class="org-container">
        <?php foreach (['sucesso' => 'success', 'erro' => 'danger'] as $chave => $tipo): ?>
            <?php if (!empty($_SESSION[$chave])): ?>
                <div class="alert alert-<?= $tipo ?> org-alert alert-dismissible fade show" role="alert">
                    <i class="fa-solid <?= $tipo === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2"></i>
                    <?= htmlspecialchars($_SESSION[$chave]) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION[$chave]); ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <header class="org-header">
            <div class="org-header-main">
                <div class="org-header-icon"><i class="fa-solid fa-layer-group"></i></div>
                <div class="org-header-copy">
                    <div class="org-eyebrow">Estrutura organizacional</div>
                    <h1>Setores</h1>
                    <p>Catálogo global utilizado na composição das hierarquias empresariais.</p>
                </div>
            </div>
            <div class="org-header-actions">
                <a href="<?= BASE_URL ?>/hierarquias" class="btn btn-outline-primary">
                    <i class="fa-solid fa-sitemap"></i> Ver hierarquias
                </a>
                <a href="<?= BASE_URL ?>/setores/criar" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Novo setor
                </a>
            </div>
        </header>

        <section class="org-kpis">
            <article class="org-kpi"><div class="org-kpi-icon"><i class="fa-solid fa-layer-group"></i></div><div><span>Total de setores</span><strong><?= $total ?></strong></div></article>
            <article class="org-kpi org-kpi-green"><div class="org-kpi-icon"><i class="fa-solid fa-circle-check"></i></div><div><span>Ativos</span><strong><?= $ativos ?></strong></div></article>
            <article class="org-kpi org-kpi-purple"><div class="org-kpi-icon"><i class="fa-solid fa-sitemap"></i></div><div><span>Utilizados</span><strong><?= $emUso ?></strong></div></article>
            <article class="org-kpi org-kpi-orange"><div class="org-kpi-icon"><i class="fa-solid fa-users"></i></div><div><span>Funcionários ativos</span><strong><?= $funcionarios ?></strong></div></article>
        </section>

        <section class="org-toolbar">
            <div class="org-toolbar-grid">
                <div class="org-field">
                    <label for="setorBusca">Buscar setor</label>
                    <div class="org-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input id="setorBusca" class="form-control" type="search" placeholder="Nome, código ou descrição">
                    </div>
                </div>
                <div class="org-field">
                    <label for="setorStatus">Status</label>
                    <select id="setorStatus" class="form-select">
                        <option value="">Todos</option>
                        <option value="ativo">Ativos</option>
                        <option value="inativo">Inativos</option>
                    </select>
                </div>
                <div class="org-field">
                    <label for="setorUso">Uso</label>
                    <select id="setorUso" class="form-select">
                        <option value="">Todos</option>
                        <option value="usado">Em uso</option>
                        <option value="livre">Sem vínculo</option>
                    </select>
                </div>
            </div>
            <div class="org-toolbar-actions">
                <button type="button" id="limparFiltrosSetores" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate-left"></i> Limpar
                </button>
            </div>
        </section>

        <div class="org-results-count"><strong id="contadorSetores"><?= $total ?></strong> setor(es) exibido(s)</div>

        <?php if ($setores): ?>
            <section class="org-grid" id="setoresGrid">
                <?php foreach ($setores as $setor): ?>
                    <?php
                    $ativo = !empty($setor['ativo']);
                    $usado = (int)($setor['total_unidades'] ?? 0) > 0;
                    $textoBusca = mb_strtolower(implode(' ', [
                        $setor['nome'] ?? '',
                        $setor['codigo'] ?? '',
                        $setor['codigo_externo'] ?? '',
                        $setor['descricao'] ?? '',
                    ]));
                    ?>
                    <article class="org-card <?= $ativo ? '' : 'org-card-muted' ?>"
                             data-search="<?= htmlspecialchars($textoBusca) ?>"
                             data-status="<?= $ativo ? 'ativo' : 'inativo' ?>"
                             data-uso="<?= $usado ? 'usado' : 'livre' ?>">
                        <header class="org-card-header">
                            <div class="org-card-icon"><i class="fa-solid fa-layer-group"></i></div>
                            <div class="org-card-title">
                                <h2><?= htmlspecialchars($setor['nome'] ?? 'Setor sem nome') ?></h2>
                                <p><?= htmlspecialchars($setor['codigo'] ?: 'Sem código interno') ?></p>
                            </div>
                            <span class="org-status <?= $ativo ? 'org-status-active' : 'org-status-inactive' ?>">
                                <i class="fa-solid fa-circle"></i><?= $ativo ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </header>

                        <div class="org-card-body">
                            <div class="org-info"><i class="fa-solid fa-hashtag"></i><div><span>Código externo</span><strong><?= htmlspecialchars($setor['codigo_externo'] ?: '-') ?></strong></div></div>
                            <div class="org-info"><i class="fa-solid fa-diagram-project"></i><div><span>Vínculo</span><strong><?= $usado ? 'Utilizado na estrutura' : 'Disponível para vínculo' ?></strong></div></div>
                            <div class="org-info org-info-wide"><i class="fa-regular fa-file-lines"></i><div><span>Descrição</span><strong><?= htmlspecialchars($setor['descricao'] ?: 'Sem descrição cadastrada') ?></strong></div></div>
                            <div class="org-metrics">
                                <div class="org-metric"><strong><?= (int)($setor['total_unidades'] ?? 0) ?></strong><span>Unidades</span></div>
                                <div class="org-metric"><strong><?= (int)($setor['total_cargos'] ?? 0) ?></strong><span>Cargos</span></div>
                                <div class="org-metric"><strong><?= (int)($setor['total_funcionarios'] ?? 0) ?></strong><span>Funcionários</span></div>
                            </div>
                        </div>

                        <footer class="org-card-actions">
                            <a href="<?= BASE_URL ?>/setores/editar/<?= (int)$setor['id'] ?>" class="btn btn-outline-primary"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                            <?php if ($ativo): ?>
                                <a href="<?= BASE_URL ?>/setores/excluir/<?= (int)$setor['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Desativar este setor? Os vínculos existentes serão preservados.')"><i class="fa-solid fa-ban"></i> Desativar</a>
                            <?php else: ?>
                                <span class="btn btn-outline-secondary disabled"><i class="fa-solid fa-lock"></i> Inativo</span>
                            <?php endif; ?>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </section>
            <div id="setoresSemResultado" class="org-empty d-none"><div class="org-empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div><h2>Nenhum setor encontrado</h2><p>Ajuste os filtros para localizar outro registro.</p></div>
        <?php else: ?>
            <div class="org-empty"><div class="org-empty-icon"><i class="fa-solid fa-layer-group"></i></div><h2>Nenhum setor cadastrado</h2><p>Crie o catálogo de setores antes de montar as hierarquias.</p><a href="<?= BASE_URL ?>/setores/criar" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Novo setor</a></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const busca = document.getElementById('setorBusca');
    const status = document.getElementById('setorStatus');
    const uso = document.getElementById('setorUso');
    const limpar = document.getElementById('limparFiltrosSetores');
    const cards = Array.from(document.querySelectorAll('#setoresGrid .org-card'));
    const contador = document.getElementById('contadorSetores');
    const vazio = document.getElementById('setoresSemResultado');

    function filtrar() {
        const termo = (busca?.value || '').toLocaleLowerCase('pt-BR').trim();
        let visiveis = 0;
        cards.forEach(function (card) {
            const mostrar = (!termo || (card.dataset.search || '').includes(termo))
                && (!status?.value || card.dataset.status === status.value)
                && (!uso?.value || card.dataset.uso === uso.value);
            card.classList.toggle('d-none', !mostrar);
            if (mostrar) visiveis++;
        });
        if (contador) contador.textContent = String(visiveis);
        if (vazio) vazio.classList.toggle('d-none', visiveis > 0);
    }

    [busca, status, uso].forEach(function (campo) { campo?.addEventListener(campo.tagName === 'INPUT' ? 'input' : 'change', filtrar); });
    limpar?.addEventListener('click', function () { if (busca) busca.value = ''; if (status) status.value = ''; if (uso) uso.value = ''; filtrar(); });
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
