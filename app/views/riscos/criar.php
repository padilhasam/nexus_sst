<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">

        <?php
        $erro = $_SESSION['erro'] ?? null;
        unset($_SESSION['erro']);

        $codigoInterno = 'RIS-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $codigoExterno = 'EXT-RIS-' . date('YmdHis');

        $cor = $cor ?? '#0d6efd';
        ?>

        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
            <?php if ($erro): ?>
                <div id="toastErro" class="toast text-bg-danger border-0 shadow-lg">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-circle-exclamation me-2"></i>
                            <?= htmlspecialchars($erro) ?>
                        </div>
                        <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <header class="page-header-riscos mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size:1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                          style="width:38px;height:38px;background:linear-gradient(135deg,<?= $cor ?>,<?= $cor ?>dd);border-radius:8px;">
                        <i class="fas <?= htmlspecialchars($icone ?? 'fa-triangle-exclamation') ?> text-white"></i>
                    </span>
                    <?= htmlspecialchars($titulo ?? 'Cadastrar Risco') ?>
                </h3>
                <small class="text-muted d-block mt-1">
                    Cadastre o risco padrão que será utilizado nos levantamentos em campo.
                </small>
            </div>

            <a href="<?= BASE_URL ?>/riscos/listar/<?= htmlspecialchars($categoria ?? '') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </header>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">

                <form action="<?= BASE_URL ?>/riscos/salvar" method="POST" class="needs-validation" novalidate>

                    <input type="hidden" name="categoria" value="<?= htmlspecialchars($categoria_url ?? '') ?>">

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color:<?= $cor ?>;">
                            <i class="fas fa-circle-info"></i> Identificação do Risco
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold text-secondary small">Código Interno</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle text-muted">
                                        <i class="fas fa-hashtag"></i>
                                    </span>
                                    <input type="text" name="codigo" id="codigo" class="form-control bg-light border-dark-subtle text-uppercase" value="<?= htmlspecialchars($codigoInterno) ?>" readonly>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold text-secondary small">Código Externo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle text-muted">
                                        <i class="fas fa-link"></i>
                                    </span>
                                    <input type="text" name="codigo_externo" id="codigo_externo" class="form-control bg-light border-dark-subtle text-uppercase" value="<?= htmlspecialchars($codigoExterno) ?>" readonly>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="nome" class="form-label fw-semibold text-secondary small">Nome do Risco *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle text-muted">
                                        <i class="fas <?= htmlspecialchars($icone ?? 'fa-triangle-exclamation') ?>"></i>
                                    </span>
                                    <input type="text" name="nome" id="nome" class="form-control border-dark-subtle" maxlength="150" required>
                                    <div class="invalid-feedback">O nome do risco é obrigatório.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="tipo_avaliacao" class="form-label fw-semibold text-secondary small">Tipo de Avaliação</label>
                                <select name="tipo_avaliacao" id="tipo_avaliacao" class="form-select border-dark-subtle">
                                    <option value="Qualitativo">Qualitativo</option>
                                    <option value="Quantitativo">Quantitativo</option>
                                    <option value="Qualitativo/Quantitativo">Qualitativo/Quantitativo</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="unidade_medida" class="form-label fw-semibold text-secondary small">Unidade de Medida</label>
                                <input type="text" name="unidade_medida" id="unidade_medida" class="form-control border-dark-subtle" placeholder="Ex: dB(A), ppm, mg/m³, °C">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-secondary small">Exige Quantificação?</label>
                                <input type="hidden" name="exige_quantificacao" value="0">
                                <div class="form-check form-switch d-flex align-items-center gap-3 ps-0 mt-2">
                                    <input class="form-check-input m-0 border-dark-subtle" type="checkbox" id="exige_quantificacao" name="exige_quantificacao" value="1">
                                    <label class="form-check-label small text-secondary" for="exige_quantificacao">
                                        Sim, exige avaliação quantitativa
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="descricao" class="form-label fw-semibold text-secondary small">Descrição</label>
                                <textarea name="descricao" id="descricao" rows="3" class="form-control border-dark-subtle" placeholder="Descrição geral do risco."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color:<?= $cor ?>;">
                            <i class="fas fa-scale-balanced"></i> Critérios Técnicos
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="limite_nr15" class="form-label fw-semibold text-secondary small">Limite NR-15</label>
                                <input type="text" name="limite_nr15" id="limite_nr15" class="form-control border-dark-subtle">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="limite_acgih" class="form-label fw-semibold text-secondary small">Limite ACGIH</label>
                                <input type="text" name="limite_acgih" id="limite_acgih" class="form-control border-dark-subtle">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="nivel_acao" class="form-label fw-semibold text-secondary small">Nível de Ação</label>
                                <input type="text" name="nivel_acao" id="nivel_acao" class="form-control border-dark-subtle">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="normas_aplicaveis" class="form-label fw-semibold text-secondary small">Normas Aplicáveis</label>
                                <textarea name="normas_aplicaveis" id="normas_aplicaveis" rows="3" class="form-control border-dark-subtle" placeholder="Ex: NR-15, NHO-01, ACGIH, Decreto 3.048/99..."></textarea>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="metodologia" class="form-label fw-semibold text-secondary small">Metodologia</label>
                                <textarea name="metodologia" id="metodologia" rows="3" class="form-control border-dark-subtle" placeholder="Ex: NHO-01, dosimetria, avaliação qualitativa, amostragem..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color:<?= $cor ?>;">
                            <i class="fas fa-chart-simple"></i> Classificação Padrão
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="severidade_padrao" class="form-label fw-semibold text-secondary small">Severidade Padrão</label>
                                <select name="severidade_padrao" id="severidade_padrao" class="form-select border-dark-subtle">
                                    <option value="1">1 - Muito Baixa</option>
                                    <option value="2">2 - Baixa</option>
                                    <option value="3">3 - Média</option>
                                    <option value="4">4 - Alta</option>
                                    <option value="5">5 - Muito Alta</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="probabilidade_padrao" class="form-label fw-semibold text-secondary small">Probabilidade Padrão</label>
                                <select name="probabilidade_padrao" id="probabilidade_padrao" class="form-select border-dark-subtle">
                                    <option value="1">1 - Muito Baixa</option>
                                    <option value="2">2 - Baixa</option>
                                    <option value="3">3 - Média</option>
                                    <option value="4">4 - Alta</option>
                                    <option value="5">5 - Muito Alta</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-secondary small">Situação</label>
                                <input type="hidden" name="ativo" value="0">
                                <div class="form-check form-switch d-flex align-items-center gap-3 ps-0 mt-2">
                                    <input class="form-check-input m-0 border-dark-subtle" type="checkbox" id="ativo" name="ativo" value="1" checked>
                                    <div>
                                        <label class="form-check-label fw-semibold text-secondary small d-block" for="ativo">Status do Risco</label>
                                        <div class="status-text">
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Risco Ativo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="<?= BASE_URL ?>/riscos/listar/<?= htmlspecialchars($categoria ?? '') ?>" class="btn btn-outline-danger rounded-pill px-4 fw-medium">Cancelar</a>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-medium shadow-sm">
                            <i class="fas fa-check me-1"></i> Salvar Risco
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toast').forEach(toast => {
        new bootstrap.Toast(toast, { delay: 4000 }).show();
    });

    const switchInput = document.getElementById('ativo');
    const statusText = document.querySelector('.status-text');

    function updateStatus() {
        if (!switchInput || !statusText) return;

        statusText.innerHTML = switchInput.checked
            ? `<span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Risco Ativo</span>`
            : `<span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Risco Inativo</span>`;
    }

    if (switchInput) {
        switchInput.addEventListener('change', updateStatus);
        updateStatus();
    }

    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    });
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>