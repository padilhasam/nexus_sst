<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">

    <div class="container-fluid px-2 px-lg-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 border-bottom">
            <div>
                <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill px-3 py-1.5 mb-2 fw-semibold">
                    <i class="fas fa-times-circle me-1"></i> Gestão Técnica
                </span>
                <h2 class="fw-bold text-dark mb-1 page-header-title">Não Conformidades & Planos de Ação</h2>
                <p class="text-muted small mb-0">Registre desvios identificados, abra RNCs e defina responsáveis e prazos de mitigação.</p>
            </div>
            <div>
                <button class="btn btn-danger d-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-sm">
                    <i class="fas fa-exclamation-circle"></i> Abrir RNC Manual
                </button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-2 px-lg-4">
        <div class="row g-3">
            
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-white rounded-3 overflow-hidden">
                    <div class="border-start border-danger border-4 p-4">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                            <div>
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 me-2 font-monospace">RNC #1042</span>
                                <span class="text-muted small"><i class="fas fa-building me-1"></i> Alfa Logística | Setor: Almoxarifado</span>
                            </div>
                            <span class="badge bg-light text-danger border border-danger-subtle px-3 py-1.5 fw-semibold">Atrasado</span>
                        </div>
                        
                        <h5 class="fw-bold text-dark mb-1">Empilhadeira operando sem sinalização sonora de ré</h5>
                        <p class="text-secondary small mb-3">Risco iminente de atropelamento na rota interna de paletes. Identificado durante a auditoria da NR-11.</p>
                        
                        <div class="row g-2 pt-3 border-top text-muted small">
                            <div class="col-12 col-sm-4"><strong>Responsável:</strong> Marcos Silva (Manutenção)</div>
                            <div class="col-12 col-sm-4"><strong>Prazo de Correção:</strong> 05/06/2026</div>
                            <div class="col-12 col-sm-4 text-sm-end">
                                <button class="btn btn-sm btn-link text-decoration-none text-primary p-0 fw-semibold">
                                    Atualizar Tratativa <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm bg-white rounded-3 overflow-hidden">
                    <div class="border-start border-warning border-4 p-4">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                            <div>
                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2.5 py-1 me-2 font-monospace">RNC #1043</span>
                                <span class="text-muted small"><i class="fas fa-building me-1"></i> Construtora Beta | Setor: Fachada Sul</span>
                            </div>
                            <span class="badge bg-light text-warning-emphasis border border-warning-subtle px-3 py-1.5 fw-semibold">Em Análise</span>
                        </div>
                        
                        <h5 class="fw-bold text-dark mb-1">Extintor PQS vencido e obstruído por materiais</h5>
                        <p class="text-secondary small mb-3">O extintor posicionado próximo ao quadro elétrico principal está com carga vencida e bloqueado por caixas de ferramentas.</p>
                        
                        <div class="row g-2 pt-3 border-top text-muted small">
                            <div class="col-12 col-sm-4"><strong>Responsável:</strong> Eng. Roberto Barros</div>
                            <div class="col-12 col-sm-4"><strong>Prazo de Correção:</strong> 20/06/2026</div>
                            <div class="col-12 col-sm-4 text-sm-end">
                                <button class="btn btn-sm btn-link text-decoration-none text-primary p-0 fw-semibold">
                                    Atualizar Tratativa <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</main>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>