<?php
$rotaAtual = 'cargos';
$css = 'cargos.css';
require_once dirname(__DIR__) . '/templates/header.php';

$cargos = $cargos ?? [];
$total = count($cargos);
$ativos = count(array_filter($cargos, fn(array $c): bool => !empty($c['ativo'])));
$emUso = count(array_filter($cargos, fn(array $c): bool => (int)($c['total_unidades'] ?? 0) > 0));
$funcionarios = array_sum(array_map(fn(array $c): int => (int)($c['total_funcionarios'] ?? 0), $cargos));
?>
<div class="org-page"><div class="org-container">
    <?php foreach (['sucesso' => 'success', 'erro' => 'danger'] as $chave => $tipo): ?>
        <?php if (!empty($_SESSION[$chave])): ?><div class="alert alert-<?= $tipo ?> org-alert alert-dismissible fade show"><i class="fa-solid <?= $tipo === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2"></i><?= htmlspecialchars($_SESSION[$chave]) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION[$chave]); endif; ?>
    <?php endforeach; ?>

    <header class="org-header">
        <div class="org-header-main"><div class="org-header-icon"><i class="fa-solid fa-briefcase"></i></div><div class="org-header-copy"><div class="org-eyebrow">Estrutura organizacional</div><h1>Cargos</h1><p>Catálogo global de funções utilizado na montagem das hierarquias.</p></div></div>
        <div class="org-header-actions"><a href="<?= BASE_URL ?>/hierarquias" class="btn btn-outline-primary"><i class="fa-solid fa-sitemap"></i> Ver hierarquias</a><a href="<?= BASE_URL ?>/cargos/criar" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Novo cargo</a></div>
    </header>

    <section class="org-kpis">
        <article class="org-kpi"><div class="org-kpi-icon"><i class="fa-solid fa-briefcase"></i></div><div><span>Total de cargos</span><strong><?= $total ?></strong></div></article>
        <article class="org-kpi org-kpi-green"><div class="org-kpi-icon"><i class="fa-solid fa-circle-check"></i></div><div><span>Ativos</span><strong><?= $ativos ?></strong></div></article>
        <article class="org-kpi org-kpi-purple"><div class="org-kpi-icon"><i class="fa-solid fa-sitemap"></i></div><div><span>Utilizados</span><strong><?= $emUso ?></strong></div></article>
        <article class="org-kpi org-kpi-orange"><div class="org-kpi-icon"><i class="fa-solid fa-users"></i></div><div><span>Funcionários ativos</span><strong><?= $funcionarios ?></strong></div></article>
    </section>

    <section class="org-toolbar">
        <div class="org-toolbar-grid">
            <div class="org-field"><label for="cargoBusca">Buscar cargo</label><div class="org-search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input id="cargoBusca" class="form-control" type="search" placeholder="Nome, CBO, código ou descrição"></div></div>
            <div class="org-field"><label for="cargoStatus">Status</label><select id="cargoStatus" class="form-select"><option value="">Todos</option><option value="ativo">Ativos</option><option value="inativo">Inativos</option></select></div>
            <div class="org-field"><label for="cargoUso">Uso</label><select id="cargoUso" class="form-select"><option value="">Todos</option><option value="usado">Em uso</option><option value="livre">Sem vínculo</option></select></div>
        </div>
        <div class="org-toolbar-actions"><button type="button" id="limparFiltrosCargos" class="btn btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i> Limpar</button></div>
    </section>
    <div class="org-results-count"><strong id="contadorCargos"><?= $total ?></strong> cargo(s) exibido(s)</div>

    <?php if ($cargos): ?>
        <section class="org-grid" id="cargosGrid">
            <?php foreach ($cargos as $cargo): ?>
                <?php
                $ativo = !empty($cargo['ativo']);
                $usado = (int)($cargo['total_unidades'] ?? 0) > 0;
                $textoBusca = mb_strtolower(implode(' ', [$cargo['nome'] ?? '', $cargo['cbo'] ?? '', $cargo['codigo'] ?? '', $cargo['codigo_externo'] ?? '', $cargo['descricao'] ?? '']));
                ?>
                <article class="org-card <?= $ativo ? '' : 'org-card-muted' ?>" data-search="<?= htmlspecialchars($textoBusca) ?>" data-status="<?= $ativo ? 'ativo' : 'inativo' ?>" data-uso="<?= $usado ? 'usado' : 'livre' ?>">
                    <header class="org-card-header"><div class="org-card-icon"><i class="fa-solid fa-briefcase"></i></div><div class="org-card-title"><h2><?= htmlspecialchars($cargo['nome'] ?? 'Cargo sem nome') ?></h2><p><?= !empty($cargo['cbo']) ? 'CBO ' . htmlspecialchars($cargo['cbo']) : 'CBO não informado' ?></p></div><span class="org-status <?= $ativo ? 'org-status-active' : 'org-status-inactive' ?>"><i class="fa-solid fa-circle"></i><?= $ativo ? 'Ativo' : 'Inativo' ?></span></header>
                    <div class="org-card-body">
                        <div class="org-info"><i class="fa-solid fa-hashtag"></i><div><span>Código interno</span><strong><?= htmlspecialchars($cargo['codigo'] ?: '-') ?></strong></div></div>
                        <div class="org-info"><i class="fa-solid fa-link"></i><div><span>Código externo</span><strong><?= htmlspecialchars($cargo['codigo_externo'] ?: '-') ?></strong></div></div>
                        <div class="org-info org-info-wide"><i class="fa-regular fa-file-lines"></i><div><span>Descrição das atividades</span><strong><?= htmlspecialchars($cargo['descricao'] ?: 'Sem descrição cadastrada') ?></strong></div></div>
                        <div class="org-metrics"><div class="org-metric"><strong><?= (int)($cargo['total_unidades'] ?? 0) ?></strong><span>Unidades</span></div><div class="org-metric"><strong><?= (int)($cargo['total_setores'] ?? 0) ?></strong><span>Setores</span></div><div class="org-metric"><strong><?= (int)($cargo['total_funcionarios'] ?? 0) ?></strong><span>Funcionários</span></div></div>
                    </div>
                    <footer class="org-card-actions"><a href="<?= BASE_URL ?>/cargos/editar/<?= (int)$cargo['id'] ?>" class="btn btn-outline-primary"><i class="fa-regular fa-pen-to-square"></i> Editar</a><?php if ($ativo): ?><a href="<?= BASE_URL ?>/cargos/excluir/<?= (int)$cargo['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Desativar este cargo? Os vínculos existentes serão preservados.')"><i class="fa-solid fa-ban"></i> Desativar</a><?php else: ?><span class="btn btn-outline-secondary disabled"><i class="fa-solid fa-lock"></i> Inativo</span><?php endif; ?></footer>
                </article>
            <?php endforeach; ?>
        </section>
        <div id="cargosSemResultado" class="org-empty d-none"><div class="org-empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div><h2>Nenhum cargo encontrado</h2><p>Ajuste os filtros para localizar outro registro.</p></div>
    <?php else: ?>
        <div class="org-empty"><div class="org-empty-icon"><i class="fa-solid fa-briefcase"></i></div><h2>Nenhum cargo cadastrado</h2><p>Cadastre os cargos antes de montar a hierarquia da empresa.</p><a href="<?= BASE_URL ?>/cargos/criar" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Novo cargo</a></div>
    <?php endif; ?>
</div></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const busca = document.getElementById('cargoBusca'), status = document.getElementById('cargoStatus'), uso = document.getElementById('cargoUso'), limpar = document.getElementById('limparFiltrosCargos');
    const cards = Array.from(document.querySelectorAll('#cargosGrid .org-card')), contador = document.getElementById('contadorCargos'), vazio = document.getElementById('cargosSemResultado');
    function filtrar() { const termo = (busca?.value || '').toLocaleLowerCase('pt-BR').trim(); let visiveis = 0; cards.forEach(function(card){ const mostrar = (!termo || (card.dataset.search || '').includes(termo)) && (!status?.value || card.dataset.status === status.value) && (!uso?.value || card.dataset.uso === uso.value); card.classList.toggle('d-none', !mostrar); if(mostrar) visiveis++; }); if(contador) contador.textContent = String(visiveis); if(vazio) vazio.classList.toggle('d-none', visiveis > 0); }
    [busca,status,uso].forEach(function(campo){ campo?.addEventListener(campo.tagName === 'INPUT' ? 'input' : 'change', filtrar); });
    limpar?.addEventListener('click', function(){ if(busca) busca.value=''; if(status) status.value=''; if(uso) uso.value=''; filtrar(); });
});
</script>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
