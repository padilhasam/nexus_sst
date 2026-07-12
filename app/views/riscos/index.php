<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>
<div class="mobile-header"><i class="fa-solid fa-arrow-left"></i><span>GHE / Riscos</span><i class="fa-regular fa-calendar"></i></div>
<div class="tabs"><a class="active">GHEs</a><a>Riscos</a></div>
<div style="padding:16px">
  <div class="ghe-card"><div class="line"><div><strong>GHE 01 - Produção</strong><br><small>3 Riscos associados</small></div><span class="pill p-med">Médio</span></div></div>
  <div class="ghe-card"><div class="line"><div><strong>GHE 02 - Manutenção</strong><br><small>4 Riscos associados</small></div><span class="pill p-high">Alto</span></div></div>
  <div class="ghe-card"><div class="line"><div><strong>GHE 03 - Almoxarifado</strong><br><small>2 Riscos associados</small></div><span class="pill p-low">Baixo</span></div></div>
  <div class="ghe-card"><div class="line"><div><strong>GHE 04 - Administrativo</strong><br><small>1 Risco associado</small></div><span class="pill p-low">Baixo</span></div></div>
  <div style="text-align:right;margin-top:20px"><button class="btn-blue"><i class="fa-solid fa-plus"></i> Novo GHE</button></div>
</div>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
