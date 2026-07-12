<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">
        
        <header class="mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #ffc107, #ff9800); border-radius: 8px; box-shadow: 0 2px 6px rgba(255, 152, 0, 0.25);">
                        <i class="fas fa-map-marked-alt text-white" style="font-size: 1.10rem;"></i>
                    </span>
                    Editar Unidade
                </h3>
                <small class="text-muted d-block mt-1">Modifique as informações cadastrais, vínculos institucionais ou a localização desta unidade física</small>
            </div>

            <a href="<?= BASE_URL ?>/unidades" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
                <i class="fas fa-arrow-left me-1"></i> Voltar à Lista
            </a>
        </header>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                
                <form action="<?= BASE_URL ?>/unidades/atualizar/<?= $unidade['id'] ?? '' ?>" method="POST" class="needs-validation" novalidate>
                    
                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-id-card"></i> Estrutura Organizacional
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="empresa_id" class="form-label fw-semibold text-secondary small">Empresa Responsável *</label>
                                <select class="form-select rounded-3 border-dark-subtle" name="empresa_id" id="empresa_id" required>
                                    <option value="1" selected>Nexus Indústria S.A.</option>
                                    </select>
                                <div class="invalid-feedback">Por favor, selecione a empresa proprietária desta unidade.</div>
                            </div>
                            
                            <div class="col-12 col-md-6">
                                <label for="nome" class="form-label fw-semibold text-secondary small">Nome da Unidade / Filial *</label>
                                <input type="text" class="form-control rounded-3 border-dark-subtle" name="nome" id="nome" 
                                       value="<?= htmlspecialchars($unidade['nome'] ?? 'Planta Industrial Principal') ?>" required>
                                <div class="invalid-feedback">O nome da unidade é obrigatório.</div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-map-marker-alt"></i> Endereço da Unidade
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="endereco" class="form-label fw-semibold text-secondary small">Logradouro (Rua, Número, Bairro)</label>
                                <input type="text" class="form-control rounded-3 border-dark-subtle" name="endereco" id="endereco" 
                                       value="<?= htmlspecialchars($unidade['endereco'] ?? '') ?>" placeholder="Ex: Av. das Araucárias, 1500 - Centro">
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <label for="cidade_uf" class="form-label fw-semibold text-secondary small">Cidade / UF</label>
                                <input type="text" class="form-control rounded-3 border-dark-subtle" name="cidade_uf" id="cidade_uf" 
                                       value="<?= htmlspecialchars($unidade['cidade_uf'] ?? 'Araucária / PR') ?>" placeholder="Ex: Curitiba / PR">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="<?= BASE_URL ?>/unidades" class="btn btn-outline-danger rounded-pill px-4 fw-medium">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-medium shadow-sm">
                            <i class="fas fa-sync-alt me-1"></i> Atualizar Unidade
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Ativa a validação visual nativa do Bootstrap 5 ao tentar submeter
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