<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/tecnicos.css">

<div class="container py-4">

    <!-- HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">

        <div>
            <h3 class="mb-0 fw-bold">
                <i class="fas fa-user-gear me-2 text-primary"></i>
                Cadastrar Técnico
            </h3>
            <small class="text-muted">
                Preencha os dados do novo técnico do sistema
            </small>
        </div>

        <a href="<?= BASE_URL ?>/tecnicos" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Voltar
        </a>

    </div>

    <!-- CARD FORM -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form action="<?= BASE_URL ?>/tecnicos/salvar" method="POST">

                <div class="row g-3">

                    <!-- NOME -->
                    <div class="col-12">
                        <label class="form-label">Nome *</label>
                        <input type="text" class="form-control" name="nome" required>
                    </div>

                    <!-- REGISTRO -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Registro Profissional</label>
                        <input type="text" class="form-control" name="registro_profissional">
                    </div>

                    <!-- CONSELHO -->
                    <div class="col-12 col-md-3">
                        <label class="form-label">Conselho</label>
                        <input type="text" class="form-control" name="conselho" placeholder="CREA / CRM / MTE">
                    </div>

                    <!-- UF (NOVO) -->
                    <div class="col-12 col-md-3">
                        <label class="form-label">UF</label>
                        <select class="form-select" name="uf">
                            <option value="">UF</option>
                            <option>AC</option><option>AL</option><option>AP</option><option>AM</option>
                            <option>BA</option><option>CE</option><option>DF</option><option>ES</option>
                            <option>GO</option><option>MA</option><option>MT</option><option>MS</option>
                            <option>MG</option><option>PA</option><option>PB</option><option>PR</option>
                            <option>PE</option><option>PI</option><option>RJ</option><option>RN</option>
                            <option>RS</option><option>RO</option><option>RR</option><option>SC</option>
                            <option>SP</option><option>SE</option><option>TO</option>
                        </select>
                    </div>

                    <!-- CPF -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">CPF</label>
                        <input type="text" class="form-control" name="cpf" id="cpf">
                    </div>

                    <!-- TELEFONE -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Telefone</label>
                        <input type="text" class="form-control" name="telefone" id="telefone">
                    </div>

                    <!-- EMAIL -->
                    <div class="col-12">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control" name="email" id="email">
                    </div>

                    <!-- STATUS -->
                    <div class="col-12">

                        <input type="hidden" name="ativo" value="0">

                        <div class="form-check form-switch d-flex align-items-center gap-3">

                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" checked>

                            <div>
                                <label class="form-check-label fw-semibold mb-0" for="ativo">
                                    Status do técnico
                                </label>

                                <div class="status-text">
                                    <span class="badge bg-success text-white px-2 py-1 rounded-pill">
                                        Ativo
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- BOTÕES -->
                <div class="d-flex justify-content-end gap-2 mt-4">

                    <a href="<?= BASE_URL ?>/tecnicos" class="btn btn-outline-secondary px-4">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary px-4">
                        Salvar Técnico
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const switchInput = document.getElementById('ativo');
    const statusText = document.querySelector('.status-text');

    function updateStatus() {
        statusText.innerHTML = switchInput.checked
            ? `<span class="badge bg-success text-white px-2 py-1 rounded-pill">Ativo</span>`
            : `<span class="badge bg-danger text-white px-2 py-1 rounded-pill">Inativo</span>`;
    }

    switchInput.addEventListener('change', updateStatus);
    updateStatus();

    // telefone
    const telefone = document.getElementById('telefone');
    telefone.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/^(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = value.substring(0, 15);
    });

    // cpf
    const cpf = document.getElementById('cpf');
    cpf.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value.substring(0, 14);
    });

});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>