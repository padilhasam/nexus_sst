<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">
        
        <header class="mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #0a58ca); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.2);">
                        <i class="fas fa-briefcase text-white" style="font-size: 1.15rem;"></i>
                    </span>
                    Cadastrar Cargo / Função
                </h3>
                <small class="text-muted d-block mt-1">Insira um novo cargo vinculando-o ao seu respectivo setor corporativo</small>
            </div>

            <a href="<?= BASE_URL ?>/cargos" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
                <i class="fas fa-arrow-left me-1"></i> Voltar à Lista
            </a>
        </header>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                
                <form action="<?= BASE_URL ?>/cargos/salvar" method="POST" class="needs-validation" novalidate>
                    
                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-id-card"></i> Informações da Função
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-5">
                                <label for="setor_id" class="form-label fw-semibold text-secondary small">Setor Vinculado *</label>
                                <select class="form-select rounded-3 border-dark-subtle" name="setor_id" id="setor_id" required>
                                    <option value="" disabled selected>Selecione um setor...</option>
                                    
                                    <?php if (!empty($setores)): ?>
                                        <?php foreach ($setores as $s): ?>
                                            <option value="<?= $s['id'] ?>">
                                                <?= htmlspecialchars($s['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                </select>
                                <div class="invalid-feedback">Por favor, selecione um setor para vincular o cargo.</div>
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <label for="nome" class="form-label fw-semibold text-secondary small">Nome do Cargo *</label>
                                <input type="text" class="form-control rounded-3 border-dark-subtle" name="nome" id="nome" placeholder="Ex: Auxiliar de Produção" required>
                                <div class="invalid-feedback">O nome do cargo é obrigatório.</div>
                            </div>
                            
                            <div class="col-12 col-md-3">
                                <label for="cbo" class="form-label fw-semibold text-secondary small">Código CBO</label>
                                <input type="text" class="form-control rounded-3 border-dark-subtle font-monospace" name="cbo" id="cbo" placeholder="Ex: 721215">
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                        <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-file-alt"></i> Descritivo de Atividades
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="descricao" class="form-label fw-semibold text-secondary small">Descrição das Atividades (Opcional)</label>
                                <textarea class="form-control rounded-3 border-dark-subtle" name="descricao" id="descricao" rows="4" 
                                          placeholder="Descreva sucintamente as atribuições da função para facilitar a avaliação de riscos nos laudos..." 
                                          style="resize: none;"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="<?= BASE_URL ?>/cargos" class="btn btn-outline-danger rounded-pill px-4 fw-medium">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-medium shadow-sm">
                            <i class="fas fa-check me-1"></i> Salvar Cargo
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
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