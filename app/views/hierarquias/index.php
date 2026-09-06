<?php
$rotaAtual = 'hierarquias';
require_once dirname(__DIR__) . '/templates/header.php';

$empresasEstruturadas = $empresasEstruturadas ?? [];
$total_empresas = (int)($total_empresas ?? 0);
$total_unidades = (int)($total_unidades ?? 0);
$total_setores = (int)($total_setores ?? 0);
$total_cargos = (int)($total_cargos ?? 0);
?>
<div class="org-page"><div class="org-container">
    <?php foreach (['sucesso' => 'success', 'erro' => 'danger'] as $chave => $tipo): ?>
        <?php if (!empty($_SESSION[$chave])): ?><div class="alert alert-<?= $tipo ?> org-alert alert-dismissible fade show"><i class="fa-solid <?= $tipo === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2"></i><?= htmlspecialchars($_SESSION[$chave]) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION[$chave]); endif; ?>
    <?php endforeach; ?>

    <header class="org-header">
        <div class="org-header-main"><div class="org-header-icon"><i class="fa-solid fa-sitemap"></i></div><div class="org-header-copy"><div class="org-eyebrow">Fonte oficial dos vínculos</div><h1>Hierarquias</h1><p>Monte Empresa → Unidade → Setor → Cargo e aloque os Funcionários.</p></div></div>
        <div class="org-header-actions"><a href="<?= BASE_URL ?>/hierarquias/importar" class="btn btn-outline-secondary"><i class="fa-solid fa-file-import"></i> Importar</a><a href="<?= BASE_URL ?>/hierarquias/criar" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Montar estrutura</a></div>
    </header>

    <section class="org-kpis">
        <article class="org-kpi"><div class="org-kpi-icon"><i class="fa-regular fa-building"></i></div><div><span>Empresas estruturadas</span><strong><?= $total_empresas ?></strong></div></article>
        <article class="org-kpi org-kpi-green"><div class="org-kpi-icon"><i class="fa-solid fa-industry"></i></div><div><span>Unidades vinculadas</span><strong><?= $total_unidades ?></strong></div></article>
        <article class="org-kpi org-kpi-purple"><div class="org-kpi-icon"><i class="fa-solid fa-layer-group"></i></div><div><span>Setores utilizados</span><strong><?= $total_setores ?></strong></div></article>
        <article class="org-kpi org-kpi-orange"><div class="org-kpi-icon"><i class="fa-solid fa-briefcase"></i></div><div><span>Cargos utilizados</span><strong><?= $total_cargos ?></strong></div></article>
    </section>

    <section class="org-toolbar">
        <div class="org-toolbar-grid">
            <div class="org-field"><label for="hierarquiaBusca">Buscar empresa</label><div class="org-search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input id="hierarquiaBusca" class="form-control" type="search" placeholder="Nome da empresa ou código"></div></div>
            <div class="org-field"><label>Modelo oficial</label><input class="form-control" value="Empresa → Unidade → Setor → Cargo" readonly></div>
            <div class="org-field"><label>Empresas exibidas</label><input id="hierarquiaContadorCampo" class="form-control" value="<?= count($empresasEstruturadas) ?>" readonly></div>
        </div>
        <div class="org-toolbar-actions"><button type="button" id="limparBuscaHierarquia" class="btn btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i> Limpar</button></div>
    </section>

    <?php if ($empresasEstruturadas): ?>
        <section class="org-company-grid" id="hierarquiasGrid">
            <?php foreach ($empresasEstruturadas as $empresa): ?>
                <?php $nome = $empresa['empresa_nome'] ?? 'Empresa não informada'; $texto = mb_strtolower($nome . ' ' . ($empresa['codigo'] ?? '')); ?>
                <a href="<?= BASE_URL ?>/hierarquias/estrutura/<?= (int)$empresa['id'] ?>" class="org-company-card" data-search="<?= htmlspecialchars($texto) ?>">
                    <div class="org-company-top"><div class="org-company-icon"><i class="fa-regular fa-building"></i></div><div><h2><?= htmlspecialchars($nome) ?></h2><p><?= (int)($empresa['total_hierarquias'] ?? 0) ?> linha(s) hierárquica(s)</p></div></div>
                    <div class="org-company-metrics"><div class="org-metric"><strong><?= (int)($empresa['total_unidades'] ?? 0) ?></strong><span>Unidades</span></div><div class="org-metric"><strong><?= (int)($empresa['total_setores'] ?? 0) ?></strong><span>Setores</span></div><div class="org-metric"><strong><?= (int)($empresa['total_cargos'] ?? 0) ?></strong><span>Cargos</span></div></div>
                    <div class="org-company-footer"><span>Visualizar estrutura</span><i class="fa-solid fa-arrow-right"></i></div>
                </a>
            <?php endforeach; ?>
        </section>
        <div id="hierarquiaSemResultado" class="org-empty d-none"><div class="org-empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div><h2>Nenhuma empresa encontrada</h2><p>Ajuste a busca para localizar outra estrutura.</p></div>
    <?php else: ?>
        <div class="org-empty"><div class="org-empty-icon"><i class="fa-solid fa-sitemap"></i></div><h2>Nenhuma hierarquia cadastrada</h2><p>Monte a primeira combinação oficial entre empresa, unidade, setor e cargo.</p><a href="<?= BASE_URL ?>/hierarquias/criar" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Montar estrutura</a></div>
    <?php endif; ?>
</div></div>
<script>
document.addEventListener('DOMContentLoaded',function(){const busca=document.getElementById('hierarquiaBusca'),limpar=document.getElementById('limparBuscaHierarquia'),cards=Array.from(document.querySelectorAll('#hierarquiasGrid .org-company-card')),vazio=document.getElementById('hierarquiaSemResultado'),contador=document.getElementById('hierarquiaContadorCampo');function filtrar(){const termo=(busca?.value||'').toLocaleLowerCase('pt-BR').trim();let total=0;cards.forEach(function(card){const mostrar=!termo||(card.dataset.search||'').includes(termo);card.classList.toggle('d-none',!mostrar);if(mostrar)total++;});if(contador)contador.value=String(total);if(vazio)vazio.classList.toggle('d-none',total>0);}busca?.addEventListener('input',filtrar);limpar?.addEventListener('click',function(){if(busca)busca.value='';filtrar();});});
</script>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
