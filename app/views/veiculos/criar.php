<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">

        <header class="mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #0a58ca); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.2);">
                        <i class="fas fa-car text-white" style="font-size: 1.10rem;"></i>
                    </span>
                    Cadastrar Veículo
                </h3>

                <small class="text-muted d-block mt-1">
                    Cadastre os veículos disponíveis para agendamento de visitas técnicas.
                </small>
            </div>

            <a href="<?= BASE_URL ?>/veiculos" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
                <i class="fas fa-arrow-left me-1"></i> Voltar à Lista
            </a>
        </header>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">

                <form action="<?= BASE_URL ?>/veiculos/salvar" method="POST" class="needs-validation" novalidate>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-id-card"></i> Identificação do Veículo
                        </h6>

                        <div class="row g-3">

                            <div class="col-12 col-md-6">
                                <label for="modelo" class="form-label fw-semibold text-secondary small">
                                    Modelo / Marca *
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-car"></i>
                                    </span>

                                    <input type="text"
                                           class="form-control rounded-end-3 border-dark-subtle"
                                           id="modelo"
                                           name="modelo"
                                           placeholder="Ex: Fiat Uno 1.0 / Toyota Hilux"
                                           maxlength="120"
                                           required>

                                    <div class="invalid-feedback">
                                        Informe o modelo ou marca do veículo.
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="placa" class="form-label fw-semibold text-secondary small">
                                    Placa *
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-car-side"></i>
                                    </span>

                                    <input type="text"
                                           class="form-control rounded-end-3 border-dark-subtle text-uppercase font-monospace"
                                           id="placa"
                                           name="placa"
                                           placeholder="ABC1D23"
                                           maxlength="7"
                                           required>

                                    <div class="invalid-feedback">
                                        Informe uma placa válida.
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="cor" class="form-label fw-semibold text-secondary small">
                                    Cor
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-palette"></i>
                                    </span>

                                    <input type="text"
                                           class="form-control rounded-end-3 border-dark-subtle"
                                           id="cor"
                                           name="cor"
                                           placeholder="Ex: Branco, Prata"
                                           maxlength="50">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-sliders-h"></i> Disponibilidade
                        </h6>

                        <input type="hidden" name="ativo" value="0">

                        <div class="form-check form-switch d-flex align-items-center gap-3 ps-0 ps-md-4">
                            <input class="form-check-input switch-lg m-0 border-dark-subtle"
                                   type="checkbox"
                                   id="ativo"
                                   name="ativo"
                                   value="1"
                                   checked
                                   style="cursor: pointer;">

                            <div>
                                <label class="form-check-label fw-semibold text-secondary small d-block" for="ativo">
                                    Status do Veículo
                                </label>

                                <div class="status-text mt-0.5">
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">
                                        Disponível
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="<?= BASE_URL ?>/veiculos" class="btn btn-outline-danger rounded-pill px-4 fw-medium">
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-medium shadow-sm">
                            <i class="fas fa-check me-1"></i> Salvar Veículo
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const switchInput = document.getElementById('ativo');
    const statusText = document.querySelector('.status-text');

    function updateStatus() {
        statusText.innerHTML = switchInput.checked
            ? `<span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Disponível</span>`
            : `<span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Inativo / Manutenção</span>`;
    }

    switchInput.addEventListener('change', updateStatus);
    updateStatus();

    const placaInput = document.getElementById('placa');

    if (placaInput) {
        placaInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/[^a-zA-Z0-9]/g, '');
            e.target.value = value.substring(0, 7).toUpperCase();
        });
    }

    const forms = document.querySelectorAll('.needs-validation');

    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>