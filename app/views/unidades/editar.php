<?php
$rotaAtual = 'unidades';
require_once dirname(__DIR__) . '/templates/header.php';
$dados = $unidade ?? [];
?>
<div class="org-page"><div class="org-container">
<header class="org-header"><div class="org-header-main"><div class="org-header-icon"><i class="fa-solid fa-map-location-dot"></i></div><div class="org-header-copy"><div class="org-eyebrow">Cadastro empresarial</div><h1>Editar unidade</h1><p><?= htmlspecialchars($dados['nome'] ?? 'Atualize os dados cadastrais da unidade') ?></p></div></div><div class="org-header-actions"><a href="<?= BASE_URL ?>/unidades" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a></div></header>
<?php if(!empty($_SESSION['erro'])): ?><div class="alert alert-danger org-alert"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?></div><?php endif; ?>
<div class="org-form-layout"><form action="<?= BASE_URL ?>/unidades/atualizar/<?= (int)$dados['id'] ?>" method="post" class="org-form org-form-card needs-validation" novalidate><?php $unidade = $dados; require __DIR__.'/formulario.php'; ?><div class="org-form-actions"><a href="<?= BASE_URL ?>/unidades" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Salvar alterações</button></div></form><aside class="org-form-aside"><div class="org-side-card"><h3>Integridade da estrutura</h3><p>Quando a unidade já possui hierarquias, funcionários, GHEs ou Check-lists, a troca de empresa poderá ser bloqueada para preservar o histórico.</p></div><div class="org-side-card"><h3>Desativação segura</h3><p>Uma unidade inativa permanece disponível nos registros históricos e deixa de aparecer em novos vínculos.</p></div></aside></div>
</div></div><?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
