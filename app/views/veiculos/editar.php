<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/usuarios.css">

<div class="container mt-4">

    <div class="mb-3">
        <h3 class="mb-0 fw-bold">
            <i class="fas fa-user-pen me-2 text-primary"></i>
            Editar Usuário
        </h3>
        <small class="text-muted">Atualize os dados do usuário</small>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form action="<?= BASE_URL ?>/usuarios/atualizar/<?= $usuario['id'] ?>" method="POST">

                <div class="row g-3">

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Nome</label>
                        <input type="text"
                               class="form-control"
                               name="nome"
                               value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>"
                               required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">E-mail</label>
                        <input type="email"
                               class="form-control"
                               name="email"
                               id="email"
                               value="<?= htmlspecialchars($usuario['email'] ?? '') ?>"
                               required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Senha (opcional)</label>
                        <input type="password"
                               class="form-control"
                               name="senha"
                               placeholder="Deixe vazio para manter a atual">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Tipo de Usuário</label>
                        <select class="form-select" name="tipo" id="selectTipo">
                            <option value="ADMIN" <?= ($usuario['tipo'] ?? '') === 'ADMIN' ? 'selected' : '' ?>>
                                Administrador
                            </option>
                            <option value="TECNICO" <?= ($usuario['tipo'] ?? '') === 'TECNICO' ? 'selected' : '' ?>>
                                Técnico
                            </option>
                            <option value="CLIENTE" <?= ($usuario['tipo'] ?? '') === 'CLIENTE' ? 'selected' : '' ?>>
                                Cliente
                            </option>
                            <option value="VISUALIZADOR" <?= ($usuario['tipo'] ?? '') === 'VISUALIZADOR' ? 'selected' : '' ?>>
                                Visualizador
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Telefone</label>
                        <input type="text"
                               class="form-control"
                               name="telefone"
                               id="telefone"
                               value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>">
                    </div>

                    <div id="secaoCamposTecnicos" class="col-12 mt-4" style="display: none;">
                        <div class="p-3 bg-light rounded border">
                            <h5 class="fw-bold mb-3 text-secondary d-flex align-items-center gap-2">
                                <i class="fas fa-id-card-clip text-primary"></i>
                                Informações Técnicas Profissionais
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-12 col-md-5">
                                    <label class="form-label">Registro Profissional (MTE / CREA / CRM)</label>
                                    <input type="text" 
                                           class="form-control bg-white" 
                                           name="registro_profissional" 
                                           value="<?= htmlspecialchars($usuario['registro_profissional'] ?? '') ?>">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Conselho</label>
                                    <input type="text" 
                                           class="form-control bg-white" 
                                           name="conselho" 
                                           placeholder="Ex: CREA-PR"
                                           value="<?= htmlspecialchars($usuario['conselho'] ?? '') ?>">
                                </div>

                                <div class="col-12 col-md-3">
                                    <label class="form-label">UF do Conselho</label>
                                    <input type="text" 
                                           class="form-control bg-white text-uppercase" 
                                           name="uf" 
                                           maxlength="2" 
                                           placeholder="Ex: PR"
                                           value="<?= htmlspecialchars($usuario['uf'] ?? '') ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Assinatura Digitalizada / Termo de Responsabilidade</label>
                                    <textarea class="form-control bg-white font-monospace" 
                                              name="assinatura" 
                                              rows="3" 
                                              placeholder="Cole aqui o hash Base64 da assinatura ou texto descritivo do termo de responsabilidade..."><?= htmlspecialchars($usuario['assinatura'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <input type="hidden" name="ativo" value="0">
                        <div class="form-check form-switch d-flex align-items-center gap-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="ativo"
                                   name="ativo"
                                   value="1"
                                   <?= !empty($usuario['ativo']) ? 'checked' : '' ?>>
                            <div>
                                <label class="form-check-label fw-semibold mb-0">
                                    Status do usuário
                                </label>
                                <div class="status-text"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        Atualizar
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
        if (!switchInput || !statusText) return;

        statusText.innerHTML = switchInput.checked
            ? `<span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Ativo</span>`
            : `<span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Inativo</span>`;
    }

    if (switchInput) {
        switchInput.addEventListener('change', updateStatus);
        updateStatus();
    }

    const telefone = document.getElementById('telefone');
    if (telefone) {
        telefone.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = value.substring(0, 15);
        });
    }

    // LÓGICA DE EXIBIÇÃO DINÂMICA DOS CAMPOS DE TÉCNICO
    const selectTipo = document.getElementById('selectTipo');
    const secaoCamposTecnicos = document.getElementById('secaoCamposTecnicos');

    function toggleCamposTecnicos() {
        if (selectTipo.value === 'TECNICO') {
            secaoCamposTecnicos.style.display = 'block';
        } else {
            secaoCamposTecnicos.style.display = 'none';
        }
    }

    if (selectTipo && secaoCamposTecnicos) {
        selectTipo.addEventListener('change', toggleCamposTecnicos);
        // Executa na inicialização para caso o usuário já seja carregado como Técnico
        toggleCamposTecnicos(); 
    }

});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>