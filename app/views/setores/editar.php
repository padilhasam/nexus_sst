<?php
$rotaAtual = 'setores';
$css = 'setores.css';
require_once dirname(__DIR__) . '/templates/header.php';
?>
<div class="org-page"><div class="org-container">
    <header class="org-header"><div class="org-header-main"><div class="org-header-icon"><i class="fa-solid fa-layer-group"></i></div><div class="org-header-copy"><div class="org-eyebrow">Catálogo organizacional</div><h1>Editar setor</h1><p>Atualize o catálogo sem alterar os vínculos já existentes.</p></div></div><div class="org-header-actions"><a href="<?= BASE_URL ?>/setores" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a></div></header>
    <?php if (!empty($_SESSION['erro'])): ?><div class="alert alert-danger org-alert"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?></div><?php endif; ?>
    <div class="org-form-layout">
        <form action="<?= BASE_URL ?>/setores/atualizar/<?= (int)$setor['id'] ?>" method="post" class="org-form org-form-card needs-validation" novalidate>
            <?php require __DIR__ . '/formulario.php'; ?>
            <div class="org-form-actions"><a href="<?= BASE_URL ?>/setores" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar alterações</button></div>
        </form>
        <aside class="org-form-aside"><div class="org-side-card"><h3>Vínculos preservados</h3><p>Alterar o nome ou a descrição não remove este setor das hierarquias, funcionários ou GHEs existentes.</p></div><div class="org-side-card"><h3>Inativação</h3><p>Ao inativar, o histórico permanece disponível, mas o setor deixa de aparecer em novos vínculos.</p></div></aside>
    </div>
</div></div>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
