<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">

        <?php
        $sucesso = $_SESSION['sucesso'] ?? null;
        $erro = $_SESSION['erro'] ?? null;
        unset($_SESSION['sucesso'], $_SESSION['erro']);

        $codigoInterno = 'EMP-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $codigoExterno = 'EXT-EMP-' . date('YmdHis');

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
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #0a58ca); border-radius: 8px;">
                        <i class="fas fa-building text-white" style="font-size: 1.10rem;"></i>
                    </span>
                    Cadastrar Empresa
                </h3>
                <small class="text-muted d-block mt-1">
                    Insira os dados cadastrais, localização, contatos e informações técnicas da empresa.
                </small>
            </div>

            <a href="<?= BASE_URL ?>/empresas" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
                <i class="fas fa-arrow-left me-1"></i> Voltar à Lista
            </a>
        </header>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">

                <form action="<?= BASE_URL ?>/empresas/armazenar" method="POST" class="needs-validation" novalidate>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-balance-scale"></i> Identificação Jurídica
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <label for="codigo" class="form-label fw-semibold text-secondary small">Código Interno</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-hashtag"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle text-uppercase bg-light" name="codigo" id="codigo" value="<?= htmlspecialchars($codigoInterno) ?>" readonly>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="codigo_externo" class="form-label fw-semibold text-secondary small">Código Externo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-link"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle text-uppercase bg-light" name="codigo_externo" id="codigo_externo" value="<?= htmlspecialchars($codigoExterno) ?>" readonly>
                                </div>
                            </div><br>

                            <div class="col-12 col-md-6">
                                <label for="cnpj" class="form-label fw-semibold text-secondary small">CNPJ</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-id-card"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="cnpj" id="cnpj" placeholder="00.000.000/0000-00">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="inscricao_estadual" class="form-label fw-semibold text-secondary small">Inscrição Estadual</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-receipt"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="inscricao_estadual" id="inscricao_estadual" maxlength="50">
                                </div>
                            </div>

                            <div class="col-12 col-md-12">
                                <label for="razao_social" class="form-label fw-semibold text-secondary small">Razão Social *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-file-signature"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="razao_social" id="razao_social" maxlength="200" required>
                                    <div class="invalid-feedback">A razão social é obrigatória.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-12">
                                <label for="nome_fantasia" class="form-label fw-semibold text-secondary small">Nome Fantasia</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-store"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="nome_fantasia" id="nome_fantasia" maxlength="200">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-chart-pie"></i> Características da Empresa
                        </h6>

                        <div class="row g-3">

                            <div class="col-12 col-lg-8">
                                <label for="cnae" class="form-label fw-semibold text-secondary small">
                                    CNAE Principal
                                </label>

                                <div class="row g-2">

                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-dark-subtle text-muted">
                                                <i class="fas fa-barcode"></i>
                                            </span>

                                            <input
                                                type="text"
                                                class="form-control border-dark-subtle"
                                                name="cnae"
                                                id="cnae"
                                                placeholder="00.00-0/00"
                                                maxlength="30">
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="form-control bg-light border-dark-subtle d-flex align-items-center"
                                            style="min-height:46px;">

                                            <input type="hidden" name="descricao_cnae" id="descricao_cnae"> <small class="text-muted d-block mt-1" id="descricaoCnaeTexto"> A descrição do CNAE será preenchida automaticamente pela consulta do CNPJ. </small>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-lg-2">
                                <label for="grau_risco" class="form-label fw-semibold text-secondary small">
                                    Grau de Risco
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle text-muted">
                                        <i class="fas fa-triangle-exclamation"></i>
                                    </span>

                                    <select
                                        class="form-select border-dark-subtle"
                                        name="grau_risco"
                                        id="grau_risco">

                                        <option value="">Selecione...</option>
                                        <option value="1">Grau 1</option>
                                        <option value="2">Grau 2</option>
                                        <option value="3">Grau 3</option>
                                        <option value="4">Grau 4</option>

                                    </select>
                                </div>
                            </div>

                            <div class="col-6 col-lg-2">
                                <label for="quantidade_funcionarios" class="form-label fw-semibold text-secondary small">
                                    Funcionários
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle text-muted">
                                        <i class="fas fa-users"></i>
                                    </span>

                                    <input
                                        type="number"
                                        class="form-control border-dark-subtle"
                                        name="quantidade_funcionarios"
                                        id="quantidade_funcionarios"
                                        min="0"
                                        placeholder="0">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-envelope-open-text"></i> Contatos
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="responsavel" class="form-label fw-semibold text-secondary small">Responsável Principal / Gestor</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-user-tie"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="responsavel" id="responsavel" maxlength="150">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="cargo_responsavel" class="form-label fw-semibold text-secondary small">Cargo do Responsável</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-briefcase"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="cargo_responsavel" id="cargo_responsavel" placeholder="Ex: Gerente de RH / Técnico SST" maxlength="150">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="telefone" class="form-label fw-semibold text-secondary small">Telefone Comercial</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="telefone" id="telefone" placeholder="(00) 0000-0000">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="contato_responsavel" class="form-label fw-semibold text-secondary small">Celular / WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-mobile-screen-button"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="contato_responsavel" id="contato_responsavel" placeholder="(00) 00000-0000">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="email" class="form-label fw-semibold text-secondary small">E-mail Institucional</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control rounded-end-3 border-dark-subtle" name="email" id="email" placeholder="comercial@empresa.com" maxlength="150">
                                    <div class="invalid-feedback">Insira um e-mail válido.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-map-marked-alt"></i> Endereço
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <label for="cep" class="form-label fw-semibold text-secondary small">CEP</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-map-pin"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="cep" id="cep" placeholder="00000-000">
                                </div>
                            </div>

                            <div class="col-12 col-md-5">
                                <label for="logradouro" class="form-label fw-semibold text-secondary small">Logradouro</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-road"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="logradouro" id="logradouro" placeholder="Rua, Avenida, Travessa">
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <label for="numero" class="form-label fw-semibold text-secondary small">Número</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-location-crosshairs"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="numero" id="numero" maxlength="20">
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <label for="complemento" class="form-label fw-semibold text-secondary small">Complemento</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="complemento" id="complemento" maxlength="100">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="bairro" class="form-label fw-semibold text-secondary small">Bairro</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-map"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="bairro" id="bairro">
                                </div>
                            </div>

                            <div class="col-12 col-md-5">
                                <label for="cidade" class="form-label fw-semibold text-secondary small">Cidade</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-city"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="cidade" id="cidade">
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="estado" class="form-label fw-semibold text-secondary small">UF</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-map-location-dot"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle text-uppercase" name="estado" id="estado" maxlength="2">
                                </div>
                            </div>

                            <input type="hidden" name="endereco" id="endereco">
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-user-gear"></i> Responsabilidade Técnica
                        </h6>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="tecnico_responsavel" class="form-label fw-semibold text-secondary small">Técnico Responsável</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-helmet-safety"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="tecnico_responsavel" id="tecnico_responsavel" maxlength="150">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="supervisor_responsavel" class="form-label fw-semibold text-secondary small">Supervisor / Gestor Interno</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-user-check"></i>
                                    </span>
                                    <input type="text" class="form-control rounded-end-3 border-dark-subtle" name="supervisor_responsavel" id="supervisor_responsavel" maxlength="150">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="periodicidade_visitas" class="form-label fw-semibold text-secondary small">Periodicidade das Visitas</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-calendar-check"></i>
                                    </span>
                                    <select class="form-select rounded-end-3 border-dark-subtle" name="periodicidade_visitas" id="periodicidade_visitas">
                                        <option value="">Selecione...</option>
                                        <option value="Mensal">Mensal</option>
                                        <option value="Bimestral">Bimestral</option>
                                        <option value="Trimestral">Trimestral</option>
                                        <option value="Semestral">Semestral</option>
                                        <option value="Anual">Anual</option>
                                        <option value="Sob demanda">Sob demanda</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-align-left"></i> Observações Gerais
                        </h6>

                        <div class="input-group">
                            <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted align-items-start pt-3">
                                <i class="fas fa-note-sticky"></i>
                            </span>
                            <textarea class="form-control rounded-end-3 border-dark-subtle" name="observacoes" id="observacoes" rows="4" placeholder="Informações adicionais sobre a empresa, contrato, atendimento ou particularidades operacionais."></textarea>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-toggle-on"></i> Situação da Empresa
                        </h6>

                        <input type="hidden" name="ativo" value="0">

                        <div class="form-check form-switch d-flex align-items-center gap-3 ps-0">
                            <input class="form-check-input switch-lg m-0 border-dark-subtle" type="checkbox" id="ativo" name="ativo" value="1" checked style="cursor: pointer;">

                            <div>
                                <label class="form-check-label fw-semibold text-secondary small d-block" for="ativo">
                                    Status da Empresa
                                </label>

                                <div class="status-text mt-0.5">
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Empresa Ativa</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="<?= BASE_URL ?>/empresas" class="btn btn-outline-danger rounded-pill px-4 fw-medium">
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-medium shadow-sm">
                            <i class="fas fa-check me-1"></i> Salvar Empresa
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

    if (toastErro) {
        new bootstrap.Toast(toastErro, { delay: 5000 }).show();
    }

    const switchInput = document.getElementById('ativo');
    const statusText = document.querySelector('.status-text');

    function updateStatus() {
        statusText.innerHTML = switchInput.checked
            ? `<span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Empresa Ativa</span>`
            : `<span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Empresa Inativa</span>`;
    }

    switchInput.addEventListener('change', updateStatus);
    updateStatus();

    const upperFields = ['codigo', 'codigo_externo', 'estado'];

    upperFields.forEach(id => {
        const input = document.getElementById(id);

        if (!input) return;

        input.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    });

    const cnpj = document.getElementById('cnpj');

    if (cnpj) {
        cnpj.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            v = v.replace(/^(\d{2})(\d)/, '$1.$2')
                 .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                 .replace(/\.(\d{3})(\d)/, '.$1/$2')
                 .replace(/(\d{4})(\d)/, '$1-$2');

            e.target.value = v.substring(0, 18);
        });
    }

    const aplicarMascaraTelefone = (id) => {
        const input = document.getElementById(id);

        if (!input) return;

        input.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            v = v.replace(/^(\d{2})(\d)/g, '($1) $2');
            v = v.length > 13 ? v.replace(/(\d{5})(\d)/, '$1-$2') : v.replace(/(\d{4})(\d)/, '$1-$2');
            e.target.value = v.substring(0, 15);
        });
    };

    aplicarMascaraTelefone('telefone');
    aplicarMascaraTelefone('contato_responsavel');

    const cep = document.getElementById('cep');

    if (cep) {
        cep.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            v = v.replace(/^(\d{5})(\d)/, '$1-$2');
            e.target.value = v.substring(0, 9);
        });

        cep.addEventListener('blur', function () {
            buscarCep(this.value);
        });
    }

    const enderecoCampos = ['logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado'];

    function atualizarEnderecoCompleto() {
        const logradouro = document.getElementById('logradouro')?.value || '';
        const numero = document.getElementById('numero')?.value || '';
        const complemento = document.getElementById('complemento')?.value || '';
        const bairro = document.getElementById('bairro')?.value || '';
        const cidade = document.getElementById('cidade')?.value || '';
        const estado = document.getElementById('estado')?.value || '';

        const enderecoCompleto = [
            logradouro,
            numero,
            complemento,
            bairro,
            cidade,
            estado
        ].filter(Boolean).join(', ');

        const enderecoHidden = document.getElementById('endereco');

        if (enderecoHidden) {
            enderecoHidden.value = enderecoCompleto;
        }
    }

    enderecoCampos.forEach(id => {
        const input = document.getElementById(id);

        if (!input) return;

        input.addEventListener('input', atualizarEnderecoCompleto);
        input.addEventListener('change', atualizarEnderecoCompleto);
    });

    const email = document.getElementById('email');

    if (email) {
        email.addEventListener('blur', function() {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email.value !== '' && !regex.test(email.value)) {
                email.setCustomValidity('Inválido');
                email.classList.add('is-invalid');
            } else {
                email.setCustomValidity('');
                email.classList.remove('is-invalid');
            }
        });
    }

    const forms = document.querySelectorAll('.needs-validation');

    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            atualizarEnderecoCompleto();

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });

    const preencherCampo = (id, valor) => {
        const campo = document.getElementById(id);

        if (campo && valor !== null && valor !== undefined && valor !== '') {
            campo.value = valor;
            campo.dispatchEvent(new Event('input'));
            campo.dispatchEvent(new Event('change'));
        }
    };

    const formatarCnae = (codigo) => {
    const cnae = String(codigo || '').replace(/\D/g, '');

    if (cnae.length !== 7) {
        return codigo;
    }

    return `${cnae.substring(0, 2)}.${cnae.substring(2, 4)}-${cnae.substring(4, 5)}/${cnae.substring(5, 7)}`;
    };

    const cnpjInput = document.getElementById('cnpj');

    if (cnpjInput) {
        cnpjInput.addEventListener('blur', async function () {
            const cnpjLimpo = this.value.replace(/\D/g, '');

            if (cnpjLimpo.length !== 14) {
                return;
            }

            try {
                cnpjInput.classList.add('is-validating');

                const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpjLimpo}`);

                if (!response.ok) {
                    throw new Error('CNPJ não encontrado.');
                }

                const data = await response.json();

                const telefoneApi = data.ddd_telefone_1 || data.ddd_telefone_2 || '';

                preencherCampo('razao_social', data.razao_social || data.nome);
                preencherCampo('nome_fantasia', data.nome_fantasia);
                preencherCampo('telefone', telefoneApi);
                preencherCampo('email', data.email);
                preencherCampo('cep', data.cep);
                preencherCampo('numero', data.numero);
                preencherCampo('complemento', data.complemento);

                await buscarCep(data.cep);

                if (data.cnae_fiscal) {
                    preencherCampo('cnae', formatarCnae(data.cnae_fiscal));
                }

                if (data.cnae_fiscal_descricao) {
                    preencherCampo('descricao_cnae', data.cnae_fiscal_descricao);

                    const descricaoCnaeTexto = document.getElementById('descricaoCnaeTexto');

                    if (descricaoCnaeTexto) {
                        descricaoCnaeTexto.innerHTML = `
                            <i class="fas fa-circle-check text-success me-1"></i>
                            ${data.cnae_fiscal_descricao}
                        `;
                    }
                }

                atualizarEnderecoCompleto();

            } catch (error) {
                alert('Não foi possível consultar o CNPJ informado. Preencha os dados manualmente.');
            } finally {
                cnpjInput.classList.remove('is-validating');
            }
        });
    }

    async function buscarCep(cep) {

        const cepLimpo = cep.replace(/\D/g, '');

        if (cepLimpo.length !== 8) return;

        try {

            const response = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
            const data = await response.json();

            if (data.erro) return;

            preencherCampo('logradouro', data.logradouro);
            preencherCampo('bairro', data.bairro);
            preencherCampo('cidade', data.localidade);
            preencherCampo('estado', data.uf);

            atualizarEnderecoCompleto();

        } catch (e) {
            console.error(e);
        }
    }
    
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>