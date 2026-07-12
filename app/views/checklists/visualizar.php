<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>
<div class="tablet-shell">
  <div class="tablet-top"><a href="<?= BASE_URL ?>/visitas" style="text-decoration:none;color:#06102b"><i class="fa-solid fa-arrow-left"></i> &nbsp;Voltar</a><h2>Check-list de Visita</h2><a href="#" style="font-weight:800;text-decoration:none;color:#075eea">Salvar rascunho</a><button class="btn-blue">Finalizar Visita</button></div>
  <div class="tablet-content">
    <nav class="check-tabs">
      <a class="active" href="#"><i class="fa-solid fa-shield-halved"></i>Dados da Visita</a><a href="#"><i class="fa-solid fa-sitemap"></i>Hierarquia</a><a href="#"><i class="fa-regular fa-user"></i>Funcionários</a><a href="#"><i class="fa-solid fa-flask-vial"></i>GHE / Riscos</a><a href="#"><i class="fa-regular fa-square-check"></i>EPI / EPC</a><a href="#"><i class="fa-regular fa-image"></i>Evidências</a><a href="#"><i class="fa-solid fa-diamond"></i>Fiscalização</a><a href="#"><i class="fa-solid fa-pen"></i>Assinaturas</a>
    </nav>
    <section class="check-grid">
      <div class="panel"><h2>Dados da Visita</h2>
        <div class="info-lines"><div><div class="field-label">Empresa</div><div class="field-value">Empresa ABC Ltda</div></div><div><div class="field-label">CNPJ</div><div class="field-value">12.345.678/0001-90</div></div></div>
        <div class="info-lines"><div><div class="field-label">Unidade</div><div class="field-value">Unidade Matriz</div></div><div><div class="field-label">CNPJ</div><div class="field-value">12.345.678/0002-71</div></div></div>
        <div class="info-lines"><div><div class="field-label">Data da Visita</div><div class="field-value">22/05/2024</div></div><div><div class="field-label">Hora da Visita</div><div class="field-value">09:00</div></div></div>
        <div class="field-label">Prioridade</div><div class="select-like">Padrão <i style="float:right" class="fa-solid fa-chevron-down"></i></div>
        <div class="field-label">Endereço</div><p><strong>Rua das Indústrias, 1000</strong><br>Bairro Industrial - São Paulo/SP<br>CEP: 01234-567</p><hr>
        <div class="field-label">Responsável da Empresa</div><p><strong>Carlos Oliveira</strong></p><hr>
        <div class="field-label">Técnico Responsável (TST)</div><p><strong>João Silva</strong></p>
      </div>
      <div>
        <div class="panel"><h2>Resumo da Visita</h2>
          <div class="info-lines" style="grid-template-columns:1fr 1fr"><div><div class="field-label">Status</div></div><div><span class="badge-soft" style="background:#fff2cc;color:#b54708">Em Andamento</span></div></div>
          <div class="info-lines"><div><div class="field-label">Check-list iniciado em</div></div><div class="field-value">22/05/2024 08:45</div></div>
          <div class="info-lines"><div><div class="field-label">Último salvamento</div></div><div class="field-value">22/05/2024 10:30</div></div>
          <div style="display:flex;justify-content:space-between;font-weight:800;font-size:12px;margin:8px 0"><span>Progresso do Check-list</span><span>65%</span></div><div class="progress-line"><b style="width:65%"></b></div>
        </div>
        <div class="panel" style="margin-top:16px"><h2>Observações Gerais</h2><div style="border:1px solid var(--line);border-radius:10px;padding:16px;line-height:1.7">Ambiente produtivo com presença de agentes químicos e físicos. Necessário atenção especial ao setor de pintura.</div></div>
        <div class="panel" style="margin-top:16px"><h2>Ações Rápidas</h2><div class="quick-grid"><button><i class="fa-regular fa-square-plus"></i> Adicionar Cargo</button><button><i class="fa-regular fa-square-plus"></i> Adicionar Setor</button><button><i class="fa-regular fa-user"></i> Adicionar Funcionário</button><button><i class="fa-regular fa-square-minus"></i> Inativar Funcionário</button></div></div>
      </div>
    </section>
  </div>
</div>
<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
