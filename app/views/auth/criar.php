<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">
        
        <header class="mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #0a58ca); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.2);">
                        <i class="fas fa-user-plus text-white" style="font-size: 1.10rem;"></i>
                    </span>
                    Cadastrar Usuário
                </h3>
                <small class="text-muted d-block mt-1">Insira as credenciais, permissões e dados de perfil do novo colaborador do sistema</small>
            </div>

            <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
                <i class="fas fa-arrow-left me-1"></i> Voltar à Lista
            </a>
        </header>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                
                <form action="<?= BASE_URL ?>/usuarios/salvar" method="POST" class="needs-validation" novalidate>
                    
                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-id-badge"></i> Identificação e Perfil Corporativo
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="nome" class="form-label fw-semibold text-secondary small">
                                    Nome Completo *
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <input type="text"
                                        class="form-control rounded-end-3 border-dark-subtle"
                                        id="nome"
                                        name="nome"
                                        placeholder="Digite o nome completo"
                                        maxlength="100"
                                        required>

                                    <div class="invalid-feedback">
                                        O nome completo é obrigatório.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-shield-alt"></i> Autenticação e Segurança
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label fw-semibold text-secondary small">E-mail Corporativo (Login) *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control rounded-end-3 border-dark-subtle" id="email" name="email" placeholder="exemplo@empresa.com" autocomplete="off" maxlength="150" required>
                                    <div class="invalid-feedback">Insira um endereço de e-mail válido para login.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="senha" class="form-label fw-semibold text-secondary small">Senha de Acesso *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control border-dark-subtle" id="senha" name="senha" placeholder="Crie uma senha inicial segura" minlength="6" required>
                                    <button class="btn btn-outline-secondary rounded-end-3 border-dark-subtle" type="button" id="btn-toggle-senha">
                                        <i class="fas fa-eye" id="icon-senha"></i>
                                    </button>
                                    <div class="invalid-feedback">A senha inicial é obrigatória (mínimo 6 caracteres).</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-sliders-h"></i> Configurações da Conta e Contato
                        </h6>
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-4">
                                <label for="tipo" class="form-label fw-semibold text-secondary small">
                                    Nível de Permissão *
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-user-shield"></i>
                                    </span>

                                    <select class="form-select rounded-end-3 border-dark-subtle"
                                            name="tipo"
                                            id="tipo"
                                            required>

                                        <option value="" disabled selected>
                                            Selecione...
                                        </option>

                                        <option value="ADMIN">Administrador</option>
                                        <option value="TECNICO">Técnico</option>
                                        <option value="CLIENTE">Cliente</option>
                                        <option value="VISUALIZADOR">Visualizador</option>

                                    </select>

                                    <div class="invalid-feedback">
                                        Selecione uma permissão.
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="telefone" class="form-label fw-semibold text-secondary small">
                                    Telefone
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted">
                                        <i class="fas fa-phone"></i>
                                    </span>

                                    <input type="text"
                                        class="form-control rounded-end-3 border-dark-subtle"
                                        id="telefone"
                                        name="telefone"
                                        placeholder="(41) 99999-9999">
                                </div>
                            </div>

                            <div class="col-12 col-md-4 mt-md-4 pt-md-2">
                                <input type="hidden" name="ativo" value="0">
                                <div class="form-check form-switch d-flex align-items-center gap-3 ps-0 ps-md-4">
                                    <input class="form-check-input switch-lg m-0 border-dark-subtle" type="checkbox" id="ativo" name="ativo" value="1" checked style="cursor: pointer;">
                                    <div>
                                        <label class="form-check-label fw-semibold text-secondary small d-block" for="ativo">Status do Usuário</label>
                                        <div class="status-text mt-0.5">
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Ativo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-danger rounded-pill px-4 fw-medium">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-medium shadow-sm">
                            <i class="fas fa-check me-1"></i> Salvar Usuário
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Gerenciamento dinâmico visual do Switch de Status
    const switchInput = document.getElementById('ativo');
    const statusText = document.querySelector('.status-text');

    function updateStatus() {
        statusText.innerHTML = switchInput.checked 
            ? `<span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Ativo</span>`
            : `<span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Inativo</span>`;
    }
    switchInput.addEventListener('change', updateStatus);
    updateStatus();

    // Alternador de visibilidade da senha (Olhinho)
    const btnToggleSenha = document.getElementById('btn-toggle-senha');
    const inputSenha = document.getElementById('senha');
    const iconSenha = document.getElementById('icon-senha');

    btnToggleSenha.addEventListener('click', function() {
        if (inputSenha.type === 'password') {
            inputSenha.type = 'text';
            iconSenha.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            inputSenha.type = 'password';
            iconSenha.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // Máscara reativa para Celular/Telefone (padrão brasileiro de 10 e 11 dígitos)
    const telefone = document.getElementById('telefone');
    telefone.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        if (value.length > 13) {
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
        } else {
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        }
        e.target.value = value.substring(0, 15);
    });

    // Validação estrutural de E-mail via blur integrada à API nativa do Bootstrap
    const email = document.getElementById('email');
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

    // Validação nativa de submissão do Bootstrap 5
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