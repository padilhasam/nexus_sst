<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">

    <div class="container-fluid px-2 px-lg-4 mb-4">
        <header class="px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-1.5 mb-2 fw-semibold d-inline-flex align-items-center gap-1">
                    <i class="fas fa-shield-alt"></i> Gestão Técnica
                </span>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #0a58ca); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.2);">
                        <i class="fas fa-clipboard-check text-white" style="font-size: 1.15rem;"></i>
                    </span>
                    Checklists de Inspeção
                </h3>
                <small class="text-muted d-block mt-1">Gerencie e aplique questionários normativos e rotinas de fiscalização em campo</small>
            </div>
            
            <div>
                <button class="btn btn-success rounded-pill d-flex align-items-center gap-2 px-4 py-2 fw-medium shadow-sm">
                    <i class="fas fa-plus"></i> Criar Modelo Personalizado
                </button>
            </div>
        </header>
    </div>

    <div class="container-fluid px-2 px-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-body p-3">
                <div class="row g-2">
                    <div class="col-12 col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted border-dark-subtle"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control border-start-0 border-dark-subtle rounded-end-3" placeholder="Buscar por NR, norma ou título do checklist...">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <select class="form-select border-dark-subtle rounded-3">
                            <option value="">Todas as Categorias</option>
                            <option value="nr">Normas Regulamentadoras (NR)</option>
                            <option value="interna">Auditoria Interna</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-2 px-lg-4">
        <div class="row g-4">
            
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border border-dark-subtle shadow-sm bg-white rounded-3">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-danger-subtle text-danger px-3 py-1.5 fw-bold rounded-2">NR-12</span>
                            <span class="text-muted small font-monospace bg-light px-2 py-0.5 rounded border">v2.1</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Segurança em Máquinas e Equipamentos</h5>
                        <p class="text-muted small flex-grow-1">Avaliação de pontos de esmagamento, proteções mecânicas, botões de emergência e intertravamentos de segurança.</p>
                        <div class="pt-3 border-top mt-3 d-flex justify-content-between align-items-center">
                            <span class="text-secondary small fw-medium"><i class="far fa-list-alt me-1 text-primary"></i> 42 Itens Normativos</span>
                            <button class="btn btn-sm btn-outline-success px-3 rounded-pill fw-medium">
                                <i class="fas fa-play me-1 small"></i> Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border border-dark-subtle shadow-sm bg-white rounded-3">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1.5 fw-bold rounded-2">NR-35</span>
                            <span class="text-muted small font-monospace bg-light px-2 py-0.5 rounded border">v1.0</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Trabalho em Altura</h5>
                        <p class="text-muted small flex-grow-1">Análise de pontos de ancoragem, integridade e inspeção de EPIs, linhas de vida e aptidão clínica da equipe técnica.</p>
                        <div class="pt-3 border-top mt-3 d-flex justify-content-between align-items-center">
                            <span class="text-secondary small fw-medium"><i class="far fa-list-alt me-1 text-primary"></i> 28 Itens Normativos</span>
                            <button class="btn btn-sm btn-outline-success px-3 rounded-pill fw-medium">
                                <i class="fas fa-play me-1 small"></i> Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border border-dark-subtle shadow-sm bg-white rounded-3">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-info-subtle text-info-emphasis px-3 py-1.5 fw-bold rounded-2">Interno</span>
                            <span class="text-muted small font-monospace bg-light px-2 py-0.5 rounded border">v3.4</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Inspeção Geral de EPI / EPC</h5>
                        <p class="text-muted small flex-grow-1">Verificação de estoque físico, prazos de validade do Certificado de Aprovação (C.A.), fichas de entrega e uso em campo.</p>
                        <div class="pt-3 border-top mt-3 d-flex justify-content-between align-items-center">
                            <span class="text-secondary small fw-medium"><i class="far fa-list-alt me-1 text-primary"></i> 15 Itens de Controle</span>
                            <button class="btn btn-sm btn-outline-success px-3 rounded-pill fw-medium">
                                <i class="fas fa-play me-1 small"></i> Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</main>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>