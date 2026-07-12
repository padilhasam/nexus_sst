<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>
<div class="mobile-header"><i class="fa-solid fa-arrow-left"></i><span>Visitas Técnicas</span><i class="fa-regular fa-calendar"></i></div>
<div class="tabs"><a class="active">Em Aberto</a><a>Concluídas</a><a>Todas</a></div>
<div style="padding:16px">
  <article class="visit-card"><div class="line"><div><strong>Empresa ABC Ltda</strong><br><small>Unidade Matriz<br>22/05/2024 - 09:00</small></div><span class="badge-soft b-blue">Padrão</span></div><div style="text-align:right;margin-top:12px"><a class="btn-blue" href="<?= BASE_URL ?>/checklists/visualizar" style="text-decoration:none;display:inline-flex;align-items:center">Iniciar Check-list</a></div></article>
  <article class="visit-card"><div class="line"><div><strong>Indústria XYZ S.A.</strong><br><small>Unidade 02<br>22/05/2024 - 14:00</small></div><span class="badge-soft b-red">Urgente</span></div><div style="text-align:right;margin-top:12px"><a class="btn-blue" href="<?= BASE_URL ?>/checklists/visualizar" style="text-decoration:none;display:inline-flex;align-items:center">Iniciar Check-list</a></div></article>
  <article class="visit-card"><div class="line"><div><strong>Comércio 123 Ltda</strong><br><small>Unidade Centro<br>23/05/2024 - 08:30</small></div><span class="badge-soft b-blue">Padrão</span></div></article>
</div>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
