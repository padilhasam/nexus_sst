<?php
$rotaAtual = 'unidades';
require_once dirname(__DIR__) . '/templates/header.php';
?>
<div class="org-page"><div class="org-container">
<header class="org-header"><div class="org-header-main"><div class="org-header-icon"><i class="fa-solid fa-map-location-dot"></i></div><div class="org-header-copy"><div class="org-eyebrow">Cadastro empresarial</div><h1>Nova unidade</h1><p>Cadastre uma filial, matriz, planta ou endereço operacional.</p></div></div><div class="org-header-actions"><a href="<?= BASE_URL ?>/unidades" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a></div></header>
<?php if(!empty($_SESSION['erro'])): ?><div class="alert alert-danger org-alert"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?></div><?php endif; ?>
<div class="org-form-layout"><form action="<?= BASE_URL ?>/unidades/salvar" method="post" class="org-form org-form-card needs-validation" novalidate><?php require __DIR__.'/formulario.php'; ?><div class="org-form-actions"><a href="<?= BASE_URL ?>/unidades" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar unidade</button></div></form><aside class="org-form-aside"><div class="org-side-card"><h3>Vínculo empresarial</h3><p>A unidade pertence a uma única empresa. Setores, cargos, funcionários e GHEs serão relacionados por meio da Hierarquia.</p></div><div class="org-side-card"><h3>Preenchimento assistido</h3><ul><li>O CNPJ pode preencher dados públicos.</li><li>O CEP pode preencher o endereço.</li><li>Revise os dados antes de salvar.</li></ul></div></aside></div>
</div></div><?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
