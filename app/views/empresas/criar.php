<?php
$rotaAtual = 'empresas';
require_once dirname(__DIR__) . '/templates/header.php';
?>
<div class="org-page"><div class="org-container">
    <header class="org-header">
        <div class="org-header-main">
            <div class="org-header-icon"><i class="fa-regular fa-building"></i></div>
            <div class="org-header-copy"><div class="org-eyebrow">Cadastro empresarial</div><h1>Nova empresa</h1><p>Cadastre o primeiro nível da estrutura organizacional do cliente.</p></div>
        </div>
        <div class="org-header-actions"><a href="<?= BASE_URL ?>/empresas" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a></div>
    </header>

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger org-alert alert-dismissible fade show" role="alert"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars((string)$_SESSION['erro']); unset($_SESSION['erro']); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button></div>
    <?php endif; ?>

    <div class="org-form-layout">
        <form action="<?= BASE_URL ?>/empresas/armazenar" method="post" class="org-form org-form-card needs-validation" novalidate>
            <?php require __DIR__ . '/formulario.php'; ?>
            <div class="org-form-actions"><a href="<?= BASE_URL ?>/empresas" class="btn btn-outline-secondary">Cancelar</a><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar empresa</button></div>
        </form>
        <aside class="org-form-aside">
            <div class="org-side-card"><h3>Ordem do cadastro</h3><p>Depois da empresa, cadastre as unidades vinculadas. Setores e Cargos permanecem como catálogos reutilizáveis.</p></div>
            <div class="org-side-card"><h3>Preenchimento assistido</h3><ul><li>O CNPJ pode preencher dados públicos.</li><li>O CEP pode completar o endereço.</li><li>Revise as informações antes de salvar.</li></ul></div>
            <div class="org-side-card"><h3>Próximo passo</h3><p>Após salvar, acesse <strong>Unidades</strong> para cadastrar a matriz, filial, planta ou local operacional.</p></div>
        </aside>
    </div>
</div></div>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
