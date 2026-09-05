<?php
$rotaAtual = 'setores';
$css = 'setores.css';
require_once dirname(__DIR__) . '/templates/header.php';
?>
<div class="org-page"><div class="org-container">
    <header class="org-header"><div class="org-header-main"><div class="org-header-icon"><i class="fa-solid fa-layer-group"></i></div><div class="org-header-copy"><div class="org-eyebrow">Catálogo organizacional</div><h1>Novo setor</h1><p>Cadastre um setor para utilização nas hierarquias das empresas.</p></div></div><div class="org-header-actions"><a href="<?= BASE_URL ?>/setores" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a></div></header>
    <?php if (!empty($_SESSION['erro'])): ?><div class="alert alert-danger org-alert"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?></div><?php endif; ?>
    <div class="org-form-layout">
        <form action="<?= BASE_URL ?>/setores/salvar" method="post" class="org-form org-form-card needs-validation" novalidate>
            <?php require __DIR__ . '/formulario.php'; ?>
            <div class="org-form-actions"><a href="<?= BASE_URL ?>/setores" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar setor</button></div>
        </form>
        <aside class="org-form-aside"><div class="org-side-card"><h3>Como funciona</h3><p>Depois do cadastro, associe este setor a uma Empresa, Unidade e Cargo na tela de Hierarquias.</p></div><div class="org-side-card"><h3>Boa prática</h3><ul><li>Use nomes objetivos.</li><li>Evite cadastrar unidades dentro do nome.</li><li>Mantenha códigos padronizados.</li></ul></div></aside>
    </div>
</div></div>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
