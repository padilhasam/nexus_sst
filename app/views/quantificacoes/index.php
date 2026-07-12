<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">

    <div class="container-fluid px-2 px-lg-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 border-bottom">
            <div>
                <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3 py-1.5 mb-2 fw-semibold">
                    <i class="fas fa-vials me-1"></i> Gestão Técnica
                </span>
                <h2 class="fw-bold text-dark mb-1 page-header-title">Higiene Ocupacional & Quantificações</h2>
                <p class="text-muted small mb-0">Registre laudos e medições quantitativas de agentes físicos, químicos e biológicos.</p>
            </div>
            <div>
                <button class="btn btn-info text-white d-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-sm">
                    <i class="fas fa-file-medical"></i> Registrar Nova Medição
                </button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-2 px-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary small fw-semibold">
                            <tr>
                                <th class="ps-4">Agente Monitorado</th>
                                <th>Local / Setor</th>
                                <th>Valor Encontrado</th>
                                <th>Limite de Tolerância (NR-15)</th>
                                <th>Condição</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="text-dark">
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><i class="fas fa-volume-up text-primary me-2"></i>Ruído Contínuo</div>
                                    <span class="text-muted small">Equipamento: Dosímetro de Ruído</span>
                                </td>
                                <td>Usinagem / Estamparia</td>
                                <td class="fw-semibold">83.4 dB(A)</td>
                                <td class="text-muted">85.0 dB(A) para 8h</td>
                                <td><span class="badge bg-success-subtle text-success-emphasis rounded-pill px-2.5 py-1"><i class="fas fa-shield-alt me-1"></i> Aceitável</span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-secondary" title="Ver Histórico"><i class="fas fa-chart-area"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><i class="fas fa-thermometer-half text-danger me-2"></i>Calor (IBUTG)</div>
                                    <span class="text-muted small">Equipamento: Termômetro de Globo</span>
                                </td>
                                <td>Fornalha / Fundição</td>
                                <td class="fw-semibold text-danger">31.2 °C</td>
                                <td class="text-muted">28.5 °C (Atividade Moderada)</td>
                                <td><span class="badge bg-danger-subtle text-danger-emphasis rounded-pill px-2.5 py-1"><i class="fas fa-exclamation-circle me-1"></i> Acima do Limite</span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-secondary" title="Ver Histórico"><i class="fas fa-chart-area"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>