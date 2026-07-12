<?php 
// Ajuste o número de subidas (dirname) conforme a localização real da sua pasta templates
require_once dirname(__DIR__) . '/templates/header.php'; 

// Mapeia a visita entregue pelo controller
$visita = $data['visita'] ?? null;
?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">
        
        <header class="mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #084298); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.25);">
                        <i class="fas fa-calendar-check text-white" style="font-size: 1.10rem;"></i>
                    </span>
                    Editar Agendamento #<?= $visita['id'] ?? '??' ?>
                </h3>
            </div>

            <a href="<?= BASE_URL ?>/visitas" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
                <i class="fas fa-arrow-left me-1"></i> Voltar à Lista
            </a>
        </header>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                
                <?php if (!$visita): ?>
                    <div class="alert alert-danger">Erro: Dados da visita não foram carregados.</div>
                <?php else: ?>
                    <form action="<?= BASE_URL ?>/visitas/atualizar?id=<?= $visita['id'] ?>" method="POST" class="needs-validation" novalidate>
                        
                        <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                            <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="fas fa-info-circle"></i> Detalhes do Agendamento
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Usuário Responsável *</label>
                                    <select name="usuario_id" class="form-select rounded-3 border-dark-subtle" required>
                                        <?php foreach ($usuarios as $u): ?>
                                            <option value="<?= $u['id'] ?>" <?= ($u['id'] == $visita['usuario_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($u['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Empresa/Destino *</label>
                                    <select name="empresa_id" class="form-select rounded-3 border-dark-subtle" required>
                                        <?php foreach ($empresas as $e): ?>
                                            <option value="<?= $e['id'] ?>" <?= ($e['id'] == $visita['empresa_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($e['razao_social']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                            <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="fas fa-truck"></i> Logística, Prioridade e Horário
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-secondary small">Veículo</label>
                                    <select name="veiculo_id" class="form-select rounded-3 border-dark-subtle">
                                        <option value="">Sem veículo / A pé</option>
                                        <?php foreach ($veiculos as $v): ?>
                                            <option value="<?= $v['id'] ?>" <?= ($v['id'] == $visita['veiculo_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($v['modelo']) ?> (<?= htmlspecialchars($v['placa']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-secondary small">Data *</label>
                                    <input 
                                        type="date" 
                                        name="data_visita" 
                                        class="form-control rounded-3 border-dark-subtle" 
                                        value="<?= htmlspecialchars($visita['data_visita']) ?>" 
                                        required>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-secondary small">Início *</label>
                                    <input 
                                        type="time" 
                                        name="hora_inicio" 
                                        class="form-control rounded-3 border-dark-subtle" 
                                        value="<?= substr($visita['hora_inicio'] ?? '', 0, 5) ?>" 
                                        required>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-secondary small">Fim *</label>
                                    <input 
                                        type="time" 
                                        name="hora_fim" 
                                        class="form-control rounded-3 border-dark-subtle" 
                                        value="<?= substr($visita['hora_fim'] ?? '', 0, 5) ?>" 
                                        required>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-secondary small">Prioridade *</label>
                                    <select name="prioridade" class="form-select rounded-3 border-dark-subtle" required>
                                        <?php $prioridadeAtual = $visita['prioridade'] ?? 'NORMAL'; ?>

                                        <option value="BAIXA" <?= $prioridadeAtual === 'BAIXA' ? 'selected' : '' ?>>Baixa</option>
                                        <option value="NORMAL" <?= $prioridadeAtual === 'NORMAL' ? 'selected' : '' ?>>Normal</option>
                                        <option value="ALTA" <?= $prioridadeAtual === 'ALTA' ? 'selected' : '' ?>>Alta</option>
                                        <option value="URGENTE" <?= $prioridadeAtual === 'URGENTE' ? 'selected' : '' ?>>Urgente</option>
                                        <option value="EMERGENCIA" <?= $prioridadeAtual === 'EMERGENCIA' ? 'selected' : '' ?>>Emergência</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Observações</label>
                            <textarea name="observacoes" class="form-control rounded-3 border-dark-subtle" rows="3"><?= htmlspecialchars($visita['observacoes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-4">
                            <div class="d-flex gap-2">
                                <?php if (($visita['status'] ?? '') !== 'FINALIZADA'): ?>
                                    <form action="<?= BASE_URL ?>/visitas/atualizarStatus?id=<?= $visita['id'] ?>" method="POST" onsubmit="return confirm('Confirmar finalização desta visita?');" class="m-0">
                                        <input type="hidden" name="status" value="FINALIZADA">
                                        <button type="submit"
                                                formaction="<?= BASE_URL ?>/visitas/atualizarStatus?id=<?= $visita['id'] ?>"
                                                formmethod="POST"
                                                class="btn btn-success rounded-pill px-3 fw-medium"
                                                onclick="return confirm('Confirmar finalização desta visita?');">
                                            <i class="fas fa-check-circle me-1"></i> Finalizar
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (($visita['status'] ?? '') !== 'CANCELADA'): ?>
                                    <a href="<?= BASE_URL ?>/visitas/cancelar?id=<?= $visita['id'] ?>" 
                                    class="btn btn-warning rounded-pill px-3 fw-medium text-white"
                                    onclick="return confirm('Tem certeza que deseja CANCELAR este agendamento?');">
                                        <i class="fas fa-ban me-1"></i> Cancelar
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>/visitas" class="btn btn-outline-secondary rounded-pill px-4 fw-medium">
                                    Voltar AGENDA
                                </a>
                                
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm">
                                    <i class="fas fa-sync-alt me-1"></i> Atualizar Visita
                                </button>

                                <a href="<?= BASE_URL ?>/visitas/excluir?id=<?= $visita['id'] ?>" 
                                class="btn btn-outline-danger rounded-pill px-4 fw-medium"
                                onclick="return confirm('Deseja realmente EXCLUIR este registro permanentemente?');">
                                    <i class="fas fa-trash-alt"></i> Excluir
                                </a>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>