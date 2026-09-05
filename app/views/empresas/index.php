<?php
$rotaAtual = 'empresas';
$css = 'empresas.css';
require_once dirname(__DIR__) . '/templates/header.php';

$empresas = $empresas ?? [];
$total = count($empresas);
$ativas = count(array_filter($empresas, static fn(array $empresa): bool => !empty($empresa['ativo'])));
$totalUnidades = array_sum(array_map(static fn(array $empresa): int => (int)($empresa['total_unidades'] ?? 0), $empresas));
$totalFuncionarios = array_sum(array_map(static fn(array $empresa): int => (int)($empresa['total_funcionarios_ativos'] ?? 0), $empresas));

$normalizar = static function (string $valor): string {
    return function_exists('mb_strtolower') ? mb_strtolower($valor, 'UTF-8') : strtolower($valor);
};

$ufs = array_values(array_unique(array_filter(array_map(
    static fn(array $empresa): string => strtoupper(trim((string)($empresa['estado'] ?? ''))),
    $empresas
))));
sort($ufs);

$nomeEmpresa = static function (array $empresa): string {
    return trim((string)($empresa['nome_fantasia'] ?? ''))
        ?: trim((string)($empresa['razao_social'] ?? ''))
        ?: 'Empresa sem nome';
};

$enderecoEmpresa = static function (array $empresa): string {
    $consolidado = trim((string)($empresa['endereco'] ?? ''));
    if ($consolidado !== '') return $consolidado;

    $partes = [];
    $logradouro = trim((string)($empresa['logradouro'] ?? ''));
    $numero = trim((string)($empresa['numero'] ?? ''));
    if ($logradouro !== '' || $numero !== '') $partes[] = implode(', ', array_filter([$logradouro, $numero]));
    foreach (['complemento', 'bairro'] as $campo) {
        $valor = trim((string)($empresa[$campo] ?? ''));
        if ($valor !== '') $partes[] = $valor;
    }
    $cidadeUf = implode(' / ', array_filter([
        trim((string)($empresa['cidade'] ?? '')),
        trim((string)($empresa['estado'] ?? '')),
    ]));
    if ($cidadeUf !== '') $partes[] = $cidadeUf;
    if (!empty($empresa['cep'])) $partes[] = 'CEP ' . trim((string)$empresa['cep']);
    return implode(' - ', $partes);
};
?>
<div class="org-page"><div class="org-container">
    <?php foreach (['sucesso' => 'success', 'erro' => 'danger'] as $chave => $tipo): ?>
        <?php if (!empty($_SESSION[$chave])): ?><div class="alert alert-<?= $tipo ?> org-alert alert-dismissible fade show" role="alert"><i class="fa-solid <?= $tipo === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2"></i><?= htmlspecialchars((string)$_SESSION[$chave]) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button></div><?php unset($_SESSION[$chave]); endif; ?>
    <?php endforeach; ?>

    <header class="org-header">
        <div class="org-header-main"><div class="org-header-icon"><i class="fa-regular fa-building"></i></div><div class="org-header-copy"><div class="org-eyebrow">Cadastro empresarial</div><h1>Empresas</h1><p>Clientes e organizações que originam as Unidades e toda a estrutura operacional.</p></div></div>
        <div class="org-header-actions"><a href="<?= BASE_URL ?>/unidades" class="btn btn-outline-primary"><i class="fa-solid fa-map-location-dot"></i> Unidades</a><a href="<?= BASE_URL ?>/empresas/criar" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nova empresa</a></div>
    </header>

    <section class="org-kpis" aria-label="Indicadores das empresas">
        <article class="org-kpi"><div class="org-kpi-icon"><i class="fa-regular fa-building"></i></div><div><span>Total de empresas</span><strong><?= $total ?></strong></div></article>
        <article class="org-kpi org-kpi-green"><div class="org-kpi-icon"><i class="fa-solid fa-circle-check"></i></div><div><span>Ativas</span><strong><?= $ativas ?></strong></div></article>
        <article class="org-kpi org-kpi-purple"><div class="org-kpi-icon"><i class="fa-solid fa-industry"></i></div><div><span>Unidades vinculadas</span><strong><?= $totalUnidades ?></strong></div></article>
        <article class="org-kpi org-kpi-orange"><div class="org-kpi-icon"><i class="fa-solid fa-users"></i></div><div><span>Funcionários ativos</span><strong><?= $totalFuncionarios ?></strong></div></article>
    </section>

    <section class="org-toolbar">
        <div class="org-toolbar-grid">
            <div class="org-field"><label for="empresaBusca">Buscar empresa</label><div class="org-search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input id="empresaBusca" class="form-control" type="search" placeholder="Empresa, CNPJ, cidade, código ou responsável" autocomplete="off"></div></div>
            <div class="org-field"><label for="empresaStatus">Status</label><select id="empresaStatus" class="form-select"><option value="">Todos</option><option value="ativo">Ativas</option><option value="inativo">Inativas</option></select></div>
            <div class="org-field"><label for="empresaUf">UF</label><select id="empresaUf" class="form-select"><option value="">Todas</option><?php foreach ($ufs as $uf): ?><option value="<?= htmlspecialchars($normalizar($uf)) ?>"><?= htmlspecialchars($uf) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="org-toolbar-actions"><button id="limparFiltrosEmpresas" type="button" class="btn btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i> Limpar</button></div>
    </section>
    <div class="org-results-count"><strong id="contadorEmpresas"><?= $total ?></strong> empresa(s) exibida(s)</div>

    <?php if ($empresas): ?>
        <section class="org-grid" id="empresasGrid">
            <?php foreach ($empresas as $empresa): ?>
                <?php
                $ativa = !empty($empresa['ativo']);
                $nome = $nomeEmpresa($empresa);
                $razao = trim((string)($empresa['razao_social'] ?? ''));
                $uf = $normalizar(trim((string)($empresa['estado'] ?? '')));
                $localizacao = implode(' / ', array_filter([
                    trim((string)($empresa['cidade'] ?? '')),
                    trim((string)($empresa['estado'] ?? '')),
                ])) ?: '-';
                $textoBusca = $normalizar(implode(' ', [
                    $nome,
                    $razao,
                    $empresa['cnpj'] ?? '',
                    $empresa['codigo'] ?? '',
                    $empresa['codigo_externo'] ?? '',
                    $empresa['cidade'] ?? '',
                    $empresa['estado'] ?? '',
                    $empresa['responsavel'] ?? '',
                    $empresa['tecnico_responsavel'] ?? '',
                ]));
                ?>
                <article class="org-card <?= $ativa ? '' : 'org-card-muted' ?>" data-search="<?= htmlspecialchars($textoBusca) ?>" data-status="<?= $ativa ? 'ativo' : 'inativo' ?>" data-uf="<?= htmlspecialchars($uf) ?>">
                    <header class="org-card-header">
                        <div class="org-card-icon"><i class="fa-regular fa-building"></i></div>
                        <div class="org-card-title"><h2 title="<?= htmlspecialchars($nome) ?>"><?= htmlspecialchars($nome) ?></h2><p title="<?= htmlspecialchars($razao ?: 'Razão social não informada') ?>"><?= htmlspecialchars($razao !== '' && $razao !== $nome ? $razao : (($empresa['codigo'] ?? '') ?: 'Código não informado')) ?></p></div>
                        <span class="org-status <?= $ativa ? 'org-status-active' : 'org-status-inactive' ?>"><i class="fa-solid fa-circle"></i><?= $ativa ? 'Ativa' : 'Inativa' ?></span>
                    </header>
                    <div class="org-card-body">
                        <div class="org-info"><i class="fa-regular fa-id-card"></i><div><span>CNPJ</span><strong><?= htmlspecialchars((string)(($empresa['cnpj'] ?? '') ?: '-')) ?></strong></div></div>
                        <div class="org-info"><i class="fa-solid fa-location-dot"></i><div><span>Localização</span><strong><?= htmlspecialchars($localizacao) ?></strong></div></div>
                        <div class="org-info"><i class="fa-solid fa-hashtag"></i><div><span>Código</span><strong><?= htmlspecialchars((string)(($empresa['codigo'] ?? '') ?: '-')) ?></strong></div></div>
                        <div class="org-info"><i class="fa-solid fa-phone"></i><div><span>Telefone</span><strong><?= htmlspecialchars((string)(($empresa['telefone'] ?? '') ?: '-')) ?></strong></div></div>
                        <div class="org-info org-info-wide"><i class="fa-solid fa-user-tie"></i><div><span>Responsável</span><strong><?= htmlspecialchars((string)(($empresa['responsavel'] ?? '') ?: 'Não informado')) ?></strong></div></div>
                        <div class="org-metrics"><div class="org-metric"><strong><?= (int)($empresa['total_unidades'] ?? 0) ?></strong><span>Unidades</span></div><div class="org-metric"><strong><?= (int)($empresa['total_setores'] ?? 0) ?></strong><span>Setores</span></div><div class="org-metric"><strong><?= (int)($empresa['total_funcionarios_ativos'] ?? 0) ?></strong><span>Funcionários</span></div></div>
                    </div>
                    <footer class="org-card-actions org-card-actions-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEmpresa<?= (int)$empresa['id'] ?>"><i class="fa-solid fa-circle-info"></i> Ficha</button>
                        <a href="<?= BASE_URL ?>/empresas/editar/<?= (int)$empresa['id'] ?>" class="btn btn-outline-primary"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                        <?php if ($ativa): ?><a href="<?= BASE_URL ?>/empresas/excluir/<?= (int)$empresa['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Deseja desativar esta empresa? Os vínculos históricos serão preservados.')"><i class="fa-solid fa-ban"></i> Desativar</a><?php else: ?><span class="btn btn-outline-secondary disabled"><i class="fa-solid fa-lock"></i> Inativa</span><?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        </section>
        <div id="empresasSemResultado" class="org-empty d-none"><div class="org-empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div><h2>Nenhuma empresa encontrada</h2><p>Ajuste a busca ou os filtros para localizar outro registro.</p></div>
    <?php else: ?>
        <div class="org-empty"><div class="org-empty-icon"><i class="fa-regular fa-building"></i></div><h2>Nenhuma empresa cadastrada</h2><p>Cadastre a primeira empresa para iniciar a estrutura de Unidades, Hierarquias e Visitas.</p><a href="<?= BASE_URL ?>/empresas/criar" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Nova empresa</a></div>
    <?php endif; ?>
</div></div>

<?php foreach ($empresas as $empresa): ?>
    <?php $nome = $nomeEmpresa($empresa); ?>
    <div class="modal fade org-modal" id="modalEmpresa<?= (int)$empresa['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
            <div class="modal-header"><div><h5 class="modal-title fw-bold mb-1"><?= htmlspecialchars($nome) ?></h5><small class="text-muted"><?= htmlspecialchars((string)(($empresa['razao_social'] ?? '') ?: 'Razão social não informada')) ?></small></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
            <div class="modal-body"><div class="org-detail-grid">
                <div class="org-detail"><span>Código interno</span><strong><?= htmlspecialchars((string)(($empresa['codigo'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail"><span>Código externo</span><strong><?= htmlspecialchars((string)(($empresa['codigo_externo'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail"><span>CNPJ</span><strong><?= htmlspecialchars((string)(($empresa['cnpj'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail"><span>Inscrição estadual</span><strong><?= htmlspecialchars((string)(($empresa['inscricao_estadual'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail"><span>CNAE</span><strong><?= htmlspecialchars((string)(($empresa['cnae'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail"><span>Grau de risco</span><strong><?= htmlspecialchars((string)(($empresa['grau_risco'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail org-detail-wide"><span>Atividade econômica</span><strong><?= htmlspecialchars((string)(($empresa['descricao_cnae'] ?? '') ?: 'Não informada')) ?></strong></div>
                <div class="org-detail org-detail-wide"><span>Endereço</span><strong><?= htmlspecialchars($enderecoEmpresa($empresa) ?: '-') ?></strong></div>
                <div class="org-detail"><span>Responsável</span><strong><?= htmlspecialchars((string)(($empresa['responsavel'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail"><span>Cargo</span><strong><?= htmlspecialchars((string)(($empresa['cargo_responsavel'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail"><span>Telefone</span><strong><?= htmlspecialchars((string)(($empresa['telefone'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail"><span>E-mail</span><strong><?= htmlspecialchars((string)(($empresa['email'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail"><span>Técnico responsável</span><strong><?= htmlspecialchars((string)(($empresa['tecnico_responsavel'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail"><span>Supervisor</span><strong><?= htmlspecialchars((string)(($empresa['supervisor_responsavel'] ?? '') ?: '-')) ?></strong></div>
                <div class="org-detail org-detail-wide"><span>Observações</span><strong><?= nl2br(htmlspecialchars((string)(($empresa['observacoes'] ?? '') ?: 'Sem observações.'))) ?></strong></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button><a href="<?= BASE_URL ?>/empresas/editar/<?= (int)$empresa['id'] ?>" class="btn btn-primary"><i class="fa-regular fa-pen-to-square"></i> Editar empresa</a></div>
        </div></div>
    </div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const busca = document.getElementById('empresaBusca');
    const status = document.getElementById('empresaStatus');
    const uf = document.getElementById('empresaUf');
    const limpar = document.getElementById('limparFiltrosEmpresas');
    const cards = Array.from(document.querySelectorAll('#empresasGrid .org-card'));
    const contador = document.getElementById('contadorEmpresas');
    const vazio = document.getElementById('empresasSemResultado');

    function filtrar() {
        const termo = (busca?.value || '').toLocaleLowerCase('pt-BR').trim();
        let visiveis = 0;
        cards.forEach(function (card) {
            const mostrar = (!termo || (card.dataset.search || '').includes(termo))
                && (!status?.value || card.dataset.status === status.value)
                && (!uf?.value || card.dataset.uf === uf.value);
            card.classList.toggle('d-none', !mostrar);
            if (mostrar) visiveis++;
        });
        if (contador) contador.textContent = String(visiveis);
        if (vazio) vazio.classList.toggle('d-none', visiveis > 0);
    }

    [busca, status, uf].forEach(function (campo) {
        campo?.addEventListener(campo.tagName === 'INPUT' ? 'input' : 'change', filtrar);
    });
    limpar?.addEventListener('click', function () {
        if (busca) busca.value = '';
        if (status) status.value = '';
        if (uf) uf.value = '';
        filtrar();
    });
});
</script>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
