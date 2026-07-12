<?php 
require_once dirname(__DIR__) . '/templates/header.php'; 

// Mapeia o usuário baseado na forma exata como o seu MVC entrega os dados
$user = $data['usuario'] ?? null;
?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">
        
        <header class="mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #ffc107, #ff9800); border-radius: 8px; box-shadow: 0 2px 6px rgba(255, 152, 0, 0.25);">
                        <i class="fas fa-user-edit text-white" style="font-size: 1.10rem;"></i>
                    </span>
                    Editar Usuário
                </h3>
                <small class="text-muted d-block mt-1">Altere as credenciais, permissões de acesso ou o status operacional do colaborador</small>
            </div>

            <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
                <i class="fas fa-arrow-left me-1"></i> Voltar à Lista
            </a>
        </header>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                
                <?php if (!$user): ?>
                    <div class="alert alert-danger">Erro: Dados do usuário não foram carregados corretamente.</div>
                <?php else: ?>

                    <form action="<?= BASE_URL ?>/usuarios/atualizar/<?= $user['id'] ?>" method="POST" class="needs-validation" novalidate>
                        
                        <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                            <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="fas fa-id-badge"></i> Identificação e Nível de Acesso
                            </h6>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="nome" class="form-label fw-semibold text-secondary small">Nome Completo *</label>
                                    <input type="text" class="form-control rounded-3 border-dark-subtle" name="nome" id="nome" 
                                           value="<?= htmlspecialchars($user['nome']) ?>" maxlength="100" required>
                                    <div class="invalid-feedback">O nome completo é obrigatório.</div>
                                </div>
                                
                                <div class="col-12 col-md-6">
                                    <label for="tipo" class="form-label fw-semibold text-secondary small">Perfil de Permissão *</label>
                                    <select class="form-select rounded-3 border-dark-subtle" name="tipo" id="tipo" required>
                                        <option value="" disabled>Selecione uma permissão...</option>
                                        <?php $userTipo = strtoupper($user['tipo'] ?? ''); ?>
                                        <option value="ADMINISTRADOR" <?= ($userTipo === 'ADMIN' || $userTipo === 'ADMINISTRADOR') ? 'selected' : '' ?>>Administrador</option>
                                        <option value="TÉCNICO" <?= ($userTipo === 'TECNICO' || $userTipo === 'TÉCNICO') ? 'selected' : '' ?>>Usuário Comum / Técnico</option>
                                        <option value="CLIENTE" <?= ($userTipo === 'CLIENTE') ? 'selected' : '' ?>>Cliente</option>
                                        <option value="VISUALIZADOR" <?= ($userTipo === 'VISUALIZADOR') ? 'selected' : '' ?>>Apenas Visualização</option>
                                    </select>
                                    <div class="invalid-feedback">Selecione o nível de acesso deste usuário.</div>
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
                                        <input type="email" class="form-control rounded-end-3 border-dark-subtle" name="email" id="email" 
                                               value="<?= htmlspecialchars($user['email']) ?>" maxlength="150" required>
                                        <div class="invalid-feedback">Insira um endereço de e-mail válido para login.</div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="senha" class="form-label fw-semibold text-secondary small">Nova Senha</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-dark-subtle rounded-start-3 text-muted"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control border-dark-subtle" name="senha" id="senha" 
                                               placeholder="Deixe em branco para manter a atual" minlength="6">
                                        <button class="btn btn-outline-secondary rounded-end-3 border-dark-subtle" type="button" id="btn-toggle-senha">
                                            <i class="fas fa-eye" id="icon-senha"></i>
                                        </button>
                                        <div class="invalid-feedback">A nova senha deve possuir no mínimo 6 caracteres.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                            <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="fas fa-toggle-on"></i> Configurações da Conta
                            </h6>
                            <div class="row g-3 align-items-center">
                                <div class="col-12 col-md-6">
                                    <input type="hidden" name="ativo" value="0">
                                    <div class="form-check form-switch d-flex align-items-center gap-3 ps-0">
                                        <input class="form-check-input switch-lg m-0 border-dark-subtle" type="checkbox" id="ativo" name="ativo" value="1" <?= ($user['ativo'] == 1) ? 'checked' : '' ?> style="cursor: pointer;">
                                        <div>
                                            <label class="form-check-label fw-semibold text-secondary small d-block" for="ativo">Acesso ao Sistema</label>
                                            <div class="status-text mt-0.5"></div>
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
                                <i class="fas fa-sync-alt me-1"></i> Atualizar Usuário
                            </button>
                        </div>

                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const switchInput = document.getElementById('ativo');
    const statusText = document.querySelector('.status-text');
    
    function updateStatusVisual() {
        if(statusText && switchInput) {
            statusText.innerHTML = switchInput.checked 
                ? `<span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Ativo</span>`
                : `<span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Inativo (Bloqueado)</span>`;
        }
    }
    if(switchInput) {
        switchInput.addEventListener('change', updateStatusVisual);
        updateStatusVisual();
    }

    const btnToggleSenha = document.getElementById('btn-toggle-senha');
    const inputSenha = document.getElementById('senha');
    const iconSenha = document.getElementById('icon-senha');

    if(btnToggleSenha && inputSenha && iconSenha) {
        btnToggleSenha.addEventListener('click', function() {
            if (inputSenha.type === 'password') {
                inputSenha.type = 'text';
                iconSenha.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                inputSenha.type = 'password';
                iconSenha.classList.replace('fa-eye-slash', 'fa-eye');
            }
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