<?php
$rotaAtual = 'empresas';
require_once dirname(__DIR__) . '/templates/header.php';
$dados = $empresa ?? [];
$nomeExibicao = trim((string)($dados['nome_fantasia'] ?? '')) ?: trim((string)($dados['razao_social'] ?? ''));
?>
<div class="org-page"><div class="org-container">
    <header class="org-header">
        <div class="org-header-main">
            <div class="org-header-icon"><i class="fa-regular fa-building"></i></div>
            <div class="org-header-copy"><div class="org-eyebrow">Cadastro empresarial</div><h1>Editar empresa</h1><p><?= htmlspecialchars($nomeExibicao !== '' ? $nomeExibicao : 'Atualize os dados cadastrais da empresa') ?></p></div>
        </div>
        <div class="org-header-actions"><a href="<?= BASE_URL ?>/empresas" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a></div>
    </header>

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger org-alert alert-dismissible fade show" role="alert"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars((string)$_SESSION['erro']); unset($_SESSION['erro']); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button></div>
    <?php endif; ?>

    <div class="org-form-layout">
        <form action="<?= BASE_URL ?>/empresas/atualizar/<?= (int)($dados['id'] ?? 0) ?>" method="post" class="org-form org-form-card needs-validation" novalidate>
            <?php $empresa = $dados; require __DIR__ . '/formulario.php'; ?>
            <div class="org-form-actions"><a href="<?= BASE_URL ?>/empresas" class="btn btn-outline-secondary">Cancelar</a><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar alterações</button></div>
        </form>
        <aside class="org-form-aside">
            <div class="org-side-card"><h3>Integridade da estrutura</h3><p>As Unidades, Hierarquias, Funcionários, Agendas, Check-lists e GHEs já vinculados permanecem associados a esta empresa.</p></div>
            <div class="org-side-card"><h3>Desativação segura</h3><p>Empresas inativas continuam disponíveis no histórico e deixam de aparecer para novos vínculos operacionais.</p></div>
            <div class="org-side-card"><h3>Dados cadastrais</h3><p>Alterações de razão social, CNPJ ou atividade econômica devem ser revisadas antes de atualizar documentos técnicos.</p></div>
        </aside>
    </div>
</div></div>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
