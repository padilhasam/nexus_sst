<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<div class="container py-4">

    <header class="mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between">
        <div>
            <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                <span class="icon-container d-flex align-items-center justify-content-center"
                    style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #0a58ca); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.2);">
                    <i class="fas fa-calendar-plus text-white" style="font-size: 1.15rem;"></i>
                </span>
                Novo Agendamento de Visita
            </h3>
            <small class="text-muted d-block mt-1">Aloque um veículo da frota e defina o itinerário da visita técnica</small>
        </div>

        <a href="<?= BASE_URL ?>/visitas" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
            <i class="fas fa-arrow-left me-1"></i> Voltar à Lista
        </a>
    </header>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">

            <form action="<?= BASE_URL ?>/visitas/salvar" method="POST" class="needs-validation" novalidate>

                <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                    <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-user-tie"></i> Responsável & Transporte
                    </h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="usuario_id" class="form-label fw-semibold text-secondary small">Usuário / Responsável *</label>
                            <select name="usuario_id" id="usuario_id" class="form-select rounded-3 border-dark-subtle" required>
                                <option value="" disabled selected>Selecione o usuário condutor...</option>
                                <?php if (isset($usuarios) && is_array($usuarios)): ?>
                                    <?php foreach($usuarios as $user): ?>
                                        <option value="<?= $user['id'] ?>">
                                            <?= htmlspecialchars($user['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Selecione o usuário condutor/responsável.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="veiculo_id" class="form-label fw-semibold text-secondary small">Veículo Coletivo / Frota</label>
                            <select name="veiculo_id" id="veiculo_id" class="form-select rounded-3 border-dark-subtle">
                                <option value="">Nenhum veículo alocado (Meios próprios / UBER)</option>
                                <?php if (isset($veiculos) && is_array($veiculos)): ?>
                                    <?php foreach($veiculos as $carro): ?>
                                        <option value="<?= $carro['id'] ?>">
                                            <?= htmlspecialchars($carro['modelo']) ?> — Placa: <?= htmlspecialchars($carro['placa']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                    <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-route"></i> Destino & Cronograma
                    </h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="empresa_id" class="form-label fw-semibold text-secondary small">Empresa Cliente *</label>
                            <select name="empresa_id" id="empresa_id" class="form-select rounded-3 border-dark-subtle" required>
                                <option value="" disabled selected>Selecione a empresa alvo...</option>
                                <?php if (isset($empresas) && is_array($empresas)): ?>
                                    <?php foreach($empresas as $empresa): ?>
                                        <option value="<?= $empresa['id'] ?>">
                                            <?= htmlspecialchars($empresa['razao_social'] ?: $empresa['nome_fantasia']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Por favor, escolha a empresa destino.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="unidade_id" class="form-label fw-semibold text-secondary small">Unidade Operacional</label>
                            <select name="unidade_id" id="unidade_id" class="form-select rounded-3 border-dark-subtle">
                                <option value="">Matriz / Unidade Principal</option>
                                <?php if (isset($unidades) && is_array($unidades)): ?>
                                    <?php foreach($unidades as $unidade): ?>
                                        <option value="<?= $unidade['id'] ?>">
                                            <?= htmlspecialchars($unidade['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="data_visita" class="form-label fw-semibold text-secondary small">Data do Agendamento *</label>
                            <input type="date" 
                                   name="data_visita" 
                                   id="data_visita" 
                                   class="form-control rounded-3 border-dark-subtle" 
                                   min="<?= date('Y-m-d') ?>" 
                                   required>
                            <div class="invalid-feedback">Defina uma data válida.</div>
                        </div>

                        <div class="col-12 col-md-2">
                            <label for="hora_inicio" class="form-label fw-semibold text-secondary small">Início *</label>
                            <input type="time" 
                                name="hora_inicio" 
                                id="hora_inicio" 
                                class="form-control rounded-3 border-dark-subtle"
                                required>
                            <div class="invalid-feedback">Informe o horário inicial.</div>
                        </div>

                        <div class="col-12 col-md-2">
                            <label for="hora_fim" class="form-label fw-semibold text-secondary small">Fim *</label>
                            <input type="time" 
                                name="hora_fim" 
                                id="hora_fim" 
                                class="form-control rounded-3 border-dark-subtle"
                                required>
                            <div class="invalid-feedback">Informe o horário final.</div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="responsavel_acompanhamento" class="form-label fw-semibold text-secondary small">Responsável no Local</label>
                            <input type="text" 
                                name="responsavel_acompanhamento" 
                                id="responsavel_acompanhamento" 
                                class="form-control rounded-3 border-dark-subtle" 
                                placeholder="Ex: Gerente de RH, Engenheiro">
                        </div>
                    </div>
                </div>

                <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                    <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-clipboard-list"></i> Detalhes da Visita & Frota
                    </h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="objetivo" class="form-label fw-semibold text-secondary small">Objetivo da Visita</label>
                            <textarea name="objetivo" 
                                      id="objetivo" 
                                      rows="3" 
                                      class="form-control rounded-3 border-dark-subtle" 
                                      placeholder="Descreva a finalidade da agenda (Ex: Renovação de LTCAT, Auditoria)..." 
                                      style="resize: none;"></textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="observacoes" class="form-label fw-semibold text-secondary small">Observações Gerais / Frota</label>
                            <textarea name="observacoes" 
                                      id="observacoes" 
                                      rows="3" 
                                      class="form-control rounded-3 border-dark-subtle" 
                                      placeholder="Observações sobre quilometragem, retirada de chaves do veículo, etc..." 
                                      style="resize: none;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="<?= BASE_URL ?>/visitas" class="btn btn-outline-danger rounded-pill px-4 fw-medium">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-medium shadow-sm">
                        <i class="fas fa-check me-1"></i> Cadastrar Agendamento
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Ativa a validação visual nativa do Bootstrap 5
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