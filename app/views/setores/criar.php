<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">

        <?php
        $sucesso = $_SESSION['sucesso'] ?? null;
        $erro = $_SESSION['erro'] ?? null;
        unset($_SESSION['sucesso'], $_SESSION['erro']);

        $codigoInterno = 'SET-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $codigoExterno = 'EXT-SET-' . date('YmdHis');

        ?>
        
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
            <?php if ($sucesso): ?>
                <div id="toastSucesso" class="toast text-bg-success border-0 shadow-lg">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-circle-check me-2"></i>
                            <?= htmlspecialchars($sucesso) ?>
                        </div>
                        <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            <?php endif; ?>

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

        <header class="mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #0a58ca); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.2);">
                        <i class="fas fa-sitemap text-white" style="font-size: 1.10rem;"></i>
                    </span>
                    Cadastrar Setor
                </h3>

                <small class="text-muted d-block mt-1">
                    Cadastre setores/departamentos que posteriormente serão vinculados às unidades na hierarquia.
                </small>
            </div>

            <a href="<?= BASE_URL ?>/setores" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
                <i class="fas fa-arrow-left me-1"></i> Voltar à Lista
            </a>
        </header>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                
                <form action="<?= BASE_URL ?>/setores/salvar" method="POST" class="needs-validation" novalidate>
                    
                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-layer-group"></i> Identificação do Setor
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="codigo" class="form-label fw-semibold text-secondary small">
                                    Código Interno
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-hashtag"></i>
                                    </span>

                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle text-uppercase bg-light" name="codigo" id="codigo" value="<?= htmlspecialchars($codigoInterno) ?>" readonly>

                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="codigo_externo" class="form-label fw-semibold text-secondary small">
                                    Código Externo
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-barcode"></i>
                                    </span>

                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle text-uppercase bg-light" name="codigo_externo" id="codigo_externo" value="<?= htmlspecialchars($codigoExterno) ?>" readonly>
                                    
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="ativo" class="form-label fw-semibold text-secondary small">
                                    Status do Setor
                                </label>

                                <input type="hidden" name="ativo" value="0">

                                <div class="form-check form-switch d-flex align-items-center gap-3 ps-0 ps-md-3 mt-2">
                                    <input class="form-check-input switch-lg m-0 border-dark-subtle"
                                           type="checkbox"
                                           id="ativo"
                                           name="ativo"
                                           value="1"
                                           checked
                                           style="cursor: pointer;">

                                    <div>
                                        <label class="form-check-label fw-semibold text-secondary small d-block" for="ativo">
                                            Situação cadastral
                                        </label>

                                        <div class="status-text mt-0.5">
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">
                                                Ativo
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="nome" class="form-label fw-semibold text-secondary small">
                                    Nome do Setor / Departamento *
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-sitemap"></i>
                                    </span>

                                    <input type="text"
                                           class="form-control rounded-end-3 border-dark-subtle"
                                           name="nome"
                                           id="nome"
                                           placeholder="Ex: Almoxarifado, Recursos Humanos, Produção"
                                           maxlength="150"
                                           required>

                                    <div class="invalid-feedback">
                                        O nome do setor é obrigatório.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-clipboard-list"></i> Descrição Operacional
                        </h6>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="descricao" class="form-label fw-semibold text-secondary small">
                                    Descrição do Setor
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted align-items-start pt-3">
                                        <i class="fas fa-align-left"></i>
                                    </span>

                                    <textarea class="form-control rounded-end-3 border-dark-subtle"
                                              name="descricao"
                                              id="descricao"
                                              rows="4"
                                              placeholder="Descreva brevemente as atividades, finalidade ou características gerais deste setor."></textarea>
                                </div>

                                <small class="text-muted d-block mt-2">
                                    Esta descrição poderá auxiliar futuramente na montagem da hierarquia e na caracterização do ambiente no levantamento técnico.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="<?= BASE_URL ?>/setores" class="btn btn-outline-danger rounded-pill px-4 fw-medium">
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-medium shadow-sm">
                            <i class="fas fa-check me-1"></i> Salvar Setor
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toastErro = document.getElementById('toastErro');

    if(toastErro){
        new bootstrap.Toast(toastErro,{
            delay:5000
        }).show();
    }

    const switchInput = document.getElementById('ativo');
    const statusText = document.querySelector('.status-text');

    function updateStatus() {
        statusText.innerHTML = switchInput.checked
            ? `<span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Ativo</span>`
            : `<span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Inativo</span>`;
    }

    switchInput.addEventListener('change', updateStatus);
    updateStatus();

    const codigo = document.getElementById('codigo');
    const codigoExterno = document.getElementById('codigo_externo');

    if (codigo) {
        codigo.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    }

    if (codigoExterno) {
        codigoExterno.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
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