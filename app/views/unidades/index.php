<?php
$rotaAtual = 'unidades';
require_once dirname(__DIR__) . '/templates/header.php';

$unidades = $unidades ?? [];
$total = count($unidades);
$ativas = count(array_filter($unidades, fn(array $u): bool => !empty($u['ativo'])));
$empresasVinculadas = count(array_unique(array_filter(array_map(fn(array $u): int => (int)($u['empresa_id'] ?? 0), $unidades))));
$cidades = count(array_unique(array_filter(array_map(fn(array $u): string => trim((string)($u['cidade'] ?? '')), $unidades))));
?>
<div class="org-page"><div class="org-container">
    <?php foreach (['sucesso' => 'success', 'erro' => 'danger'] as $chave => $tipo): ?>
        <?php if (!empty($_SESSION[$chave])): ?><div class="alert alert-<?= $tipo ?> org-alert alert-dismissible fade show"><i class="fa-solid <?= $tipo === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2"></i><?= htmlspecialchars($_SESSION[$chave]) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION[$chave]); endif; ?>
    <?php endforeach; ?>

    <header class="org-header">
        <div class="org-header-main"><div class="org-header-icon"><i class="fa-solid fa-map-location-dot"></i></div><div class="org-header-copy"><div class="org-eyebrow">Cadastro empresarial</div><h1>Unidades</h1><p>Filiais, matrizes, plantas e locais operacionais vinculados às empresas.</p></div></div>
        <div class="org-header-actions"><a href="<?= BASE_URL ?>/empresas" class="btn btn-outline-primary"><i class="fa-regular fa-building"></i> Empresas</a><a href="<?= BASE_URL ?>/unidades/criar" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nova unidade</a></div>
    </header>

    <section class="org-kpis">
        <article class="org-kpi"><div class="org-kpi-icon"><i class="fa-solid fa-industry"></i></div><div><span>Total de unidades</span><strong><?= $total ?></strong></div></article>
        <article class="org-kpi org-kpi-green"><div class="org-kpi-icon"><i class="fa-solid fa-circle-check"></i></div><div><span>Ativas</span><strong><?= $ativas ?></strong></div></article>
        <article class="org-kpi org-kpi-purple"><div class="org-kpi-icon"><i class="fa-regular fa-building"></i></div><div><span>Empresas vinculadas</span><strong><?= $empresasVinculadas ?></strong></div></article>
        <article class="org-kpi org-kpi-orange"><div class="org-kpi-icon"><i class="fa-solid fa-city"></i></div><div><span>Cidades atendidas</span><strong><?= $cidades ?></strong></div></article>
    </section>

    <section class="org-toolbar">
        <div class="org-toolbar-grid">
            <div class="org-field"><label for="unidadeBusca">Buscar unidade</label><div class="org-search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input id="unidadeBusca" class="form-control" type="search" placeholder="Unidade, empresa, CNPJ, cidade ou código"></div></div>
            <div class="org-field"><label for="unidadeStatus">Status</label><select id="unidadeStatus" class="form-select"><option value="">Todos</option><option value="ativo">Ativas</option><option value="inativo">Inativas</option></select></div>
            <div class="org-field"><label for="unidadeUf">UF</label><select id="unidadeUf" class="form-select"><option value="">Todas</option><?php $ufs = array_values(array_unique(array_filter(array_map(fn(array $u): string => strtoupper(trim((string)($u['estado'] ?? ''))), $unidades)))); sort($ufs); foreach($ufs as $uf): ?><option value="<?= htmlspecialchars(mb_strtolower($uf)) ?>"><?= htmlspecialchars($uf) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="org-toolbar-actions"><button id="limparFiltrosUnidades" type="button" class="btn btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i> Limpar</button></div>
    </section>
    <div class="org-results-count"><strong id="contadorUnidades"><?= $total ?></strong> unidade(s) exibida(s)</div>

    <?php if ($unidades): ?>
        <section class="org-grid" id="unidadesGrid">
            <?php foreach ($unidades as $unidade): ?>
                <?php
                $ativa = !empty($unidade['ativo']);
                $uf = mb_strtolower(trim((string)($unidade['estado'] ?? '')));
                $empresaNome = $unidade['empresa_nome'] ?? 'Empresa não informada';
                $textoBusca = mb_strtolower(implode(' ', [$unidade['nome'] ?? '', $empresaNome, $unidade['cnpj'] ?? '', $unidade['cidade'] ?? '', $unidade['estado'] ?? '', $unidade['codigo'] ?? '']));
                ?>
                <article class="org-card <?= $ativa ? '' : 'org-card-muted' ?>" data-search="<?= htmlspecialchars($textoBusca) ?>" data-status="<?= $ativa ? 'ativo' : 'inativo' ?>" data-uf="<?= htmlspecialchars($uf) ?>">
                    <header class="org-card-header"><div class="org-card-icon"><i class="fa-solid fa-map-location-dot"></i></div><div class="org-card-title"><h2><?= htmlspecialchars($unidade['nome'] ?? 'Unidade sem nome') ?></h2><p><?= htmlspecialchars($empresaNome) ?></p></div><span class="org-status <?= $ativa ? 'org-status-active' : 'org-status-inactive' ?>"><i class="fa-solid fa-circle"></i><?= $ativa ? 'Ativa' : 'Inativa' ?></span></header>
                    <div class="org-card-body">
                        <div class="org-info"><i class="fa-solid fa-location-dot"></i><div><span>Localização</span><strong><?= htmlspecialchars(trim(($unidade['cidade'] ?? '') . (!empty($unidade['estado']) ? ' / ' . $unidade['estado'] : '')) ?: '-') ?></strong></div></div>
                        <div class="org-info"><i class="fa-regular fa-id-card"></i><div><span>CNPJ</span><strong><?= htmlspecialchars($unidade['cnpj'] ?: '-') ?></strong></div></div>
                        <div class="org-info"><i class="fa-solid fa-hashtag"></i><div><span>Código</span><strong><?= htmlspecialchars($unidade['codigo'] ?: '-') ?></strong></div></div>
                        <div class="org-info"><i class="fa-solid fa-phone"></i><div><span>Telefone</span><strong><?= htmlspecialchars($unidade['telefone'] ?: '-') ?></strong></div></div>
                        <div class="org-info org-info-wide"><i class="fa-solid fa-user-tie"></i><div><span>Responsável</span><strong><?= htmlspecialchars($unidade['responsavel'] ?: 'Não informado') ?></strong></div></div>
                    </div>
                    <footer class="org-card-actions org-card-actions-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalUnidade<?= (int)$unidade['id'] ?>"><i class="fa-solid fa-circle-info"></i> Ficha</button>
                        <a href="<?= BASE_URL ?>/unidades/editar/<?= (int)$unidade['id'] ?>" class="btn btn-outline-primary"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                        <?php if ($ativa): ?><a href="<?= BASE_URL ?>/unidades/excluir/<?= (int)$unidade['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Deseja desativar esta unidade? Os vínculos históricos serão preservados.')"><i class="fa-solid fa-ban"></i> Desativar</a><?php else: ?><span class="btn btn-outline-secondary disabled"><i class="fa-solid fa-lock"></i> Inativa</span><?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        </section>
        <div id="unidadesSemResultado" class="org-empty d-none"><div class="org-empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div><h2>Nenhuma unidade encontrada</h2><p>Ajuste a busca ou os filtros para localizar outro registro.</p></div>
    <?php else: ?>
        <div class="org-empty"><div class="org-empty-icon"><i class="fa-solid fa-map-location-dot"></i></div><h2>Nenhuma unidade cadastrada</h2><p>Cadastre a primeira unidade operacional vinculada a uma empresa.</p><a href="<?= BASE_URL ?>/unidades/criar" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Nova unidade</a></div>
    <?php endif; ?>
</div></div>

<?php foreach ($unidades as $unidade): ?>
<div class="modal fade org-modal" id="modalUnidade<?= (int)$unidade['id'] ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><div class="modal-header"><div><h5 class="modal-title fw-bold mb-1"><?= htmlspecialchars($unidade['nome'] ?? 'Unidade') ?></h5><small class="text-muted"><?= htmlspecialchars($unidade['empresa_nome'] ?? '-') ?></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="org-detail-grid">
<div class="org-detail"><span>Código interno</span><strong><?= htmlspecialchars($unidade['codigo'] ?: '-') ?></strong></div><div class="org-detail"><span>CNPJ</span><strong><?= htmlspecialchars($unidade['cnpj'] ?: '-') ?></strong></div>
<div class="org-detail"><span>CNAE</span><strong><?= htmlspecialchars($unidade['cnae'] ?: '-') ?></strong></div><div class="org-detail"><span>Grau de risco</span><strong><?= htmlspecialchars((string)($unidade['grau_risco'] ?: '-')) ?></strong></div>
<div class="org-detail org-detail-wide"><span>Endereço</span><strong><?= htmlspecialchars($unidade['endereco'] ?: trim(implode(', ', array_filter([$unidade['logradouro'] ?? '', $unidade['numero'] ?? '', $unidade['bairro'] ?? '', $unidade['cidade'] ?? '', $unidade['estado'] ?? '']))) ?: '-') ?></strong></div>
<div class="org-detail"><span>Responsável</span><strong><?= htmlspecialchars($unidade['responsavel'] ?: '-') ?></strong></div><div class="org-detail"><span>Contato</span><strong><?= htmlspecialchars($unidade['telefone'] ?: ($unidade['email'] ?: '-')) ?></strong></div>
<div class="org-detail org-detail-wide"><span>Observações</span><strong><?= nl2br(htmlspecialchars($unidade['observacoes'] ?: 'Sem observações.')) ?></strong></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button><a href="<?= BASE_URL ?>/unidades/editar/<?= (int)$unidade['id'] ?>" class="btn btn-primary"><i class="fa-regular fa-pen-to-square me-1"></i>Editar unidade</a></div></div></div></div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const busca=document.getElementById('unidadeBusca'),status=document.getElementById('unidadeStatus'),uf=document.getElementById('unidadeUf'),limpar=document.getElementById('limparFiltrosUnidades');
    const cards=Array.from(document.querySelectorAll('#unidadesGrid .org-card')),contador=document.getElementById('contadorUnidades'),vazio=document.getElementById('unidadesSemResultado');
    function filtrar(){const termo=(busca?.value||'').toLocaleLowerCase('pt-BR').trim();let visiveis=0;cards.forEach(function(card){const mostrar=(!termo||(card.dataset.search||'').includes(termo))&&(!status?.value||card.dataset.status===status.value)&&(!uf?.value||card.dataset.uf===uf.value);card.classList.toggle('d-none',!mostrar);if(mostrar)visiveis++;});if(contador)contador.textContent=String(visiveis);if(vazio)vazio.classList.toggle('d-none',visiveis>0);}
    [busca,status,uf].forEach(function(campo){campo?.addEventListener(campo.tagName==='INPUT'?'input':'change',filtrar);});
    limpar?.addEventListener('click',function(){if(busca)busca.value='';if(status)status.value='';if(uf)uf.value='';filtrar();});
});
</script>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
