<?php 
if (isset($visitas) && !empty($visitas)): 
    foreach ($visitas as $v): 

        $s = $v['status'] ?? 'ABERTA';

        $colStatus = match ($s) {
            'FINALIZADA' => 'success',
            'CANCELADA' => 'danger',
            'CHECKLIST_INICIADO' => 'primary',
            'EM_ANDAMENTO' => 'info',
            default => 'warning'
        };

        $prioridade = $v['prioridade'] ?? 'NORMAL';

        $colPrioridade = match ($prioridade) {
            'BAIXA' => 'primary',
            'NORMAL' => 'success',
            'ALTA' => 'warning',
            'URGENTE' => 'danger',
            'EMERGENCIA' => 'dark',
            default => 'secondary'
        };

        $iconePrioridade = match ($prioridade) {
            'BAIXA' => 'fa-arrow-down',
            'NORMAL' => 'fa-circle-check',
            'ALTA' => 'fa-arrow-up',
            'URGENTE' => 'fa-triangle-exclamation',
            'EMERGENCIA' => 'fa-bolt',
            default => 'fa-circle'
        };

        $textoPrioridade = match ($prioridade) {
            'BAIXA' => 'Baixa',
            'NORMAL' => 'Normal',
            'ALTA' => 'Alta',
            'URGENTE' => 'Urgente',
            'EMERGENCIA' => 'Emergência',
            default => ucfirst(strtolower($prioridade))
        };
?>

<tr>
    <td>
        <span class="badge bg-<?= $colPrioridade ?> px-3 py-2 rounded-pill">
            <i class="fas <?= $iconePrioridade ?> me-1"></i>
            <?= $textoPrioridade ?>
        </span>
    </td>

    <td data-raw="<?= date('Y-m-d', strtotime($v['data_visita'])) ?>">
        <div class="fw-semibold text-dark">
            <i class="far fa-calendar-alt text-primary me-2"></i>
            <?= date('d/m/Y', strtotime($v['data_visita'])) ?>
        </div>

        <div class="small text-secondary ms-4">
            <i class="far fa-clock me-2"></i>
            <?= substr($v['hora_inicio'] ?? '00:00', 0, 5) ?>
            <span class="mx-1">→</span>
            <?= substr($v['hora_fim'] ?? '00:00', 0, 5) ?>
        </div>
    </td>

    <td>
        <div class="fw-semibold text-dark">
            <i class="fas fa-building text-secondary me-2"></i>
            <?= htmlspecialchars($v['empresa_nome'] ?? 'N/A') ?>
        </div>

        <?php if (!empty($v['unidade_nome'])): ?>
            <div class="small text-muted ms-4">
                <?= htmlspecialchars($v['unidade_nome']) ?>
            </div>
        <?php endif; ?>
    </td>

    <td>
        <div class="fw-semibold">
            <i class="fas fa-user text-primary me-2"></i>
            <?= htmlspecialchars($v['usuario_nome'] ?? 'N/A') ?>
        </div>
    </td>

    <td>
        <?php if (!empty($v['veiculo_modelo'])): ?>
            <div class="fw-semibold">
                <i class="fas fa-car text-secondary me-2"></i>
                <?= htmlspecialchars($v['veiculo_modelo']) ?>
            </div>

            <?php if (!empty($v['veiculo_placa'])): ?>
                <div class="small text-muted ms-4">
                    <?= htmlspecialchars($v['veiculo_placa']) ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-muted">
                <i class="fas fa-walking me-2"></i>
                A pé
            </div>
        <?php endif; ?>
    </td>

    <td>
        <span class="badge bg-<?= $colStatus ?>-subtle text-<?= $colStatus ?> px-3 py-2 rounded-pill">
            <?= ucfirst(strtolower(str_replace('_', ' ', $s))) ?>
        </span>
    </td>

    <td class="text-end pe-4">
        <div class="dropdown">
            <button 
                class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fas fa-ellipsis-vertical me-1"></i>
                Ações
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                <li>
                    <button 
                        type="button"
                        class="dropdown-item"
                        data-bs-toggle="modal"
                        data-bs-target="#modalVisita<?= $v['id'] ?>">
                        <i class="fas fa-circle-info me-2 text-secondary"></i>
                        Visualizar
                    </button>
                </li>

                <?php if (!in_array($s, ['FINALIZADA', 'CANCELADA', 'EXCLUIDA'])): ?>
                    <li>
                        <a class="dropdown-item" href="<?= BASE_URL ?>/checklists/iniciar/<?= $v['id'] ?>">
                            <i class="fas fa-clipboard-check me-2 text-success"></i>
                            Iniciar Checklist
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item" href="<?= BASE_URL ?>/visitas/editar?id=<?= $v['id'] ?>">
                            <i class="fas fa-edit me-2 text-primary"></i>
                            Editar
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <a 
                            class="dropdown-item text-danger"
                            href="<?= BASE_URL ?>/visitas/cancelar?id=<?= $v['id'] ?>"
                            onclick="return confirm('Tem certeza que deseja cancelar este agendamento?')">
                            <i class="fas fa-ban me-2"></i>
                            Cancelar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </td>
</tr>

<?php endforeach; else: ?>
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">
            <i class="fas fa-calendar-xmark fa-2x d-block mb-2"></i>
            Nenhum agendamento encontrado.
        </td>
    </tr>
<?php endif; ?>