<?php
$rotaAtual = 'funcionarios';
$css = 'funcionarios.css';
require_once dirname(__DIR__) . '/templates/header.php';

$funcionarios = $funcionarios ?? [];
$indicadores = $indicadores ?? [];
$empresas = $empresas ?? [];
$unidades = $unidades ?? [];
$filtros = $filtros ?? [];
$csrfToken = $csrfToken ?? '';
?>
<div class="org-page"><div class="org-container">
    <?php foreach (['sucesso' => 'success', 'erro' => 'danger'] as $chave => $tipo): ?>
        <?php if (!empty($_SESSION[$chave])): ?><div class="alert alert-<?= $tipo ?> org-alert alert-dismissible fade show"><i class="fa-solid <?= $tipo === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2"></i><?= htmlspecialchars($_SESSION[$chave]) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION[$chave]); endif; ?>
    <?php endforeach; ?>

    <header class="org-header">
        <div class="org-header-main"><div class="org-header-icon"><i class="fa-solid fa-id-badge"></i></div><div class="org-header-copy"><div class="org-eyebrow">Força de trabalho</div><h1>Funcionários</h1><p>Trabalhadores vinculados à hierarquia oficial das empresas.</p></div></div>
        <div class="org-header-actions"><a href="<?= BASE_URL ?>/hierarquias" class="btn btn-outline-primary"><i class="fa-solid fa-sitemap"></i> Hierarquias</a><a href="<?= BASE_URL ?>/funcionarios/criar" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Novo funcionário</a></div>
    </header>

    <section class="org-kpis">
        <article class="org-kpi"><div class="org-kpi-icon"><i class="fa-solid fa-users"></i></div><div><span>Total</span><strong><?= (int)($indicadores['total'] ?? 0) ?></strong></div></article>
        <article class="org-kpi org-kpi-green"><div class="org-kpi-icon"><i class="fa-solid fa-user-check"></i></div><div><span>Ativos</span><strong><?= (int)($indicadores['ativos'] ?? 0) ?></strong></div></article>
        <article class="org-kpi org-kpi-red"><div class="org-kpi-icon"><i class="fa-solid fa-user-slash"></i></div><div><span>Inativos</span><strong><?= (int)($indicadores['inativos'] ?? 0) ?></strong></div></article>
        <article class="org-kpi org-kpi-purple"><div class="org-kpi-icon"><i class="fa-regular fa-calendar-plus"></i></div><div><span>Admitidos no mês</span><strong><?= (int)($indicadores['admitidos_mes'] ?? 0) ?></strong></div></article>
    </section>

    <section class="org-toolbar">
        <form method="GET" action="<?= BASE_URL ?>/funcionarios" class="org-toolbar-grid">
            <div class="org-field"><label for="busca">Buscar funcionário</label><div class="org-search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input type="search" id="busca" name="busca" class="form-control" placeholder="Nome, CPF, matrícula, empresa ou cargo" value="<?= htmlspecialchars((string)($filtros['busca'] ?? '')) ?>"></div></div>
            <div class="org-field"><label for="empresa_id">Empresa</label><select id="empresa_id" name="empresa_id" class="form-select"><option value="">Todas</option><?php foreach ($empresas as $empresa): ?><option value="<?= (int)$empresa['id'] ?>" <?= (string)($filtros['empresa_id'] ?? '') === (string)$empresa['id'] ? 'selected' : '' ?>><?= htmlspecialchars($empresa['nome_fantasia'] ?: $empresa['razao_social']) ?></option><?php endforeach; ?></select></div>
            <div class="org-field"><label for="unidade_id">Unidade</label><select id="unidade_id" name="unidade_id" class="form-select"><option value="">Todas</option><?php foreach ($unidades as $unidade): ?><option value="<?= (int)$unidade['id'] ?>" data-empresa="<?= (int)$unidade['empresa_id'] ?>" <?= (string)($filtros['unidade_id'] ?? '') === (string)$unidade['id'] ? 'selected' : '' ?>><?= htmlspecialchars($unidade['nome']) ?></option><?php endforeach; ?></select></div>
            <div class="org-field"><label for="status">Status</label><select id="status" name="status" class="form-select"><option value="">Todos</option><option value="ATIVO" <?= ($filtros['status'] ?? '') === 'ATIVO' ? 'selected' : '' ?>>Ativos</option><option value="INATIVO" <?= ($filtros['status'] ?? '') === 'INATIVO' ? 'selected' : '' ?>>Inativos</option></select></div>
            <div class="org-toolbar-actions"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Aplicar</button><a href="<?= BASE_URL ?>/funcionarios" class="btn btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i> Limpar</a></div>
        </form>
    </section>
    <div class="org-results-count"><strong><?= count($funcionarios) ?></strong> funcionário(s) encontrado(s)</div>

    <?php if ($funcionarios): ?>
        <section class="org-grid">
            <?php foreach ($funcionarios as $funcionario): ?>
                <?php $ativo = (int)($funcionario['ativo'] ?? 0) === 1; ?>
                <article class="org-card <?= $ativo ? '' : 'org-card-muted' ?>">
                    <header class="org-card-header"><div class="org-card-icon"><strong><?= htmlspecialchars(mb_strtoupper(mb_substr((string)$funcionario['nome'], 0, 1))) ?></strong></div><div class="org-card-title"><h2><?= htmlspecialchars($funcionario['nome']) ?></h2><p><?= htmlspecialchars($funcionario['cargo_nome'] ?? 'Cargo não informado') ?></p></div><span class="org-status <?= $ativo ? 'org-status-active' : 'org-status-inactive' ?>"><i class="fa-solid fa-circle"></i><?= $ativo ? 'Ativo' : 'Inativo' ?></span></header>
                    <div class="org-card-body">
                        <div class="org-info"><i class="fa-regular fa-building"></i><div><span>Empresa</span><strong><?= htmlspecialchars($funcionario['empresa_nome'] ?? '-') ?></strong></div></div>
                        <div class="org-info"><i class="fa-solid fa-industry"></i><div><span>Unidade</span><strong><?= htmlspecialchars($funcionario['unidade_nome'] ?? '-') ?></strong></div></div>
                        <div class="org-info"><i class="fa-solid fa-layer-group"></i><div><span>Setor</span><strong><?= htmlspecialchars($funcionario['setor_nome'] ?? '-') ?></strong></div></div>
                        <div class="org-info"><i class="fa-regular fa-id-card"></i><div><span>Matrícula / CPF</span><strong><?= htmlspecialchars($funcionario['matricula'] ?: ($funcionario['cpf'] ?: '-')) ?></strong></div></div>
                        <div class="org-info"><i class="fa-regular fa-calendar-check"></i><div><span>Admissão</span><strong><?= !empty($funcionario['data_admissao']) ? date('d/m/Y', strtotime($funcionario['data_admissao'])) : '-' ?></strong></div></div>
                        <?php if (!$ativo): ?><div class="org-info org-info-wide"><i class="fa-solid fa-circle-info"></i><div><span>Motivo da inativação</span><strong><?= htmlspecialchars($funcionario['motivo_inativacao'] ?? '-') ?></strong></div></div><?php endif; ?>
                    </div>
                    <footer class="org-card-actions">
                        <a href="<?= BASE_URL ?>/funcionarios/editar/<?= (int)$funcionario['id'] ?>" class="btn btn-outline-primary"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                        <?php if ($ativo): ?><button type="button" class="btn btn-outline-danger btn-inativar-funcionario" data-bs-toggle="modal" data-bs-target="#modalInativarFuncionario" data-id="<?= (int)$funcionario['id'] ?>" data-nome="<?= htmlspecialchars($funcionario['nome'], ENT_QUOTES) ?>"><i class="fa-solid fa-user-slash"></i> Inativar</button><?php else: ?><form method="POST" action="<?= BASE_URL ?>/funcionarios/reativar/<?= (int)$funcionario['id'] ?>"><input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>"><button type="submit" class="btn btn-outline-success"><i class="fa-solid fa-user-check"></i> Reativar</button></form><?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <div class="org-empty"><div class="org-empty-icon"><i class="fa-solid fa-users-slash"></i></div><h2>Nenhum funcionário encontrado</h2><p>Não existem registros correspondentes aos filtros selecionados.</p><a href="<?= BASE_URL ?>/funcionarios/criar" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i>Novo funcionário</a></div>
    <?php endif; ?>
</div></div>

<div class="modal fade org-modal" id="modalInativarFuncionario" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><form method="POST" class="modal-content" id="formInativarFuncionario"><input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>"><div class="modal-header"><h5 class="modal-title"><i class="fa-solid fa-user-slash me-2 text-danger"></i>Inativar funcionário</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Informe o motivo da inativação de <strong id="nomeFuncionarioInativar"></strong>.</p><div class="mb-3"><label for="motivo_inativacao" class="form-label">Motivo *</label><textarea id="motivo_inativacao" name="motivo" class="form-control" rows="3" required></textarea></div><div><label for="data_desligamento" class="form-label">Data do desligamento</label><input type="date" id="data_desligamento" name="data_desligamento" class="form-control"></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger">Confirmar inativação</button></div></form></div></div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const empresa=document.getElementById('empresa_id'),unidade=document.getElementById('unidade_id');
    function filtrarUnidades(){const empresaId=empresa?.value||'';Array.from(unidade?.options||[]).forEach(function(option){if(!option.value)return;option.hidden=empresaId!==''&&option.dataset.empresa!==empresaId;if(option.hidden&&option.selected)unidade.value='';});}
    empresa?.addEventListener('change',filtrarUnidades);filtrarUnidades();
    document.querySelectorAll('.btn-inativar-funcionario').forEach(function(botao){botao.addEventListener('click',function(){const form=document.getElementById('formInativarFuncionario');if(form)form.action='<?= BASE_URL ?>/funcionarios/inativar/'+botao.dataset.id;const nome=document.getElementById('nomeFuncionarioInativar');if(nome)nome.textContent=botao.dataset.nome||'';});});
});
</script>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
