<?php
$css = 'ghe.css';
require_once dirname(__DIR__) . '/templates/header.php';

$ghe = $ghe ?? [];
$riscosDisponiveis = $riscosDisponiveis ?? [];
$editavel = $editavel ?? false;
$csrfToken = $csrfToken ?? '';

$categoriaLabel = static fn(string $categoria): string => match (strtolower($categoria)) {
    'fisico' => 'Físico',
    'quimico' => 'Químico',
    'biologico' => 'Biológico',
    'ergonomico' => 'Ergonômico',
    'acidente' => 'Acidente',
    'psicossocial' => 'Psicossocial',
    default => ucfirst($categoria),
};

$statusChecklistLabel = match (strtoupper((string)($ghe['checklist_status'] ?? ''))) {
    'ABERTO' => 'Aberto',
    'EM_ANDAMENTO' => 'Em andamento',
    'CONCLUIDO' => 'Concluído',
    'CANCELADO' => 'Cancelado',
    default => 'Não informado',
};
?>

<div class="ghe-page">
    <?php if (!empty($_SESSION['sucesso'])): ?>
        <div class="alert alert-success rounded-4 alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= htmlspecialchars($_SESSION['sucesso']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger rounded-4 alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?= htmlspecialchars($_SESSION['erro']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <header class="ghe-page-header">
        <div>
            <h2><?= htmlspecialchars($ghe['codigo'] . ' — ' . $ghe['nome']) ?></h2>
            <p>Detalhamento dos cargos e riscos ocupacionais aplicados ao grupo.</p>
        </div>
        <div class="ghe-header-actions">
            <a href="<?= BASE_URL ?>/ghe" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            <?php if ($editavel): ?>
                <a href="<?= BASE_URL ?>/ghe/editar/<?= (int)$ghe['id'] ?>" class="btn btn-primary"><i class="fa-regular fa-pen-to-square"></i> Editar GHE</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="ghe-detail-hero <?= (int)$ghe['ativo'] === 1 ? '' : 'is-inactive' ?>">
        <div class="ghe-detail-code"><?= htmlspecialchars($ghe['codigo']) ?></div>
        <div class="ghe-detail-title">
            <span>Grupo Homogêneo de Exposição</span>
            <h3><?= htmlspecialchars($ghe['nome']) ?></h3>
            <p><?= htmlspecialchars($ghe['descricao'] ?: 'Sem descrição das atividades.') ?></p>
        </div>
        <div class="ghe-detail-badges">
            <span class="ghe-status <?= (int)$ghe['ativo'] === 1 ? 'status-ativo' : 'status-inativo' ?>">
                <?= (int)$ghe['ativo'] === 1 ? 'Ativo' : 'Inativo' ?>
            </span>
            <span class="ghe-checklist-badge status-<?= strtolower((string)$ghe['checklist_status']) ?>">
                Check-list <?= htmlspecialchars($statusChecklistLabel) ?>
            </span>
        </div>
    </section>

    <section class="ghe-detail-grid">
        <article class="ghe-detail-card">
            <div class="ghe-detail-card-title"><i class="fa-regular fa-building"></i><h3>Contexto da visita</h3></div>
            <div class="ghe-detail-info-grid">
                <div><span>Empresa</span><strong><?= htmlspecialchars($ghe['empresa_nome'] ?? '-') ?></strong></div>
                <div><span>Unidade</span><strong><?= htmlspecialchars($ghe['unidade_nome'] ?? 'Matriz') ?></strong></div>
                <div><span>Data da visita</span><strong><?= !empty($ghe['data_visita']) ? date('d/m/Y', strtotime($ghe['data_visita'])) : '-' ?></strong></div>
                <div><span>Técnico responsável</span><strong><?= htmlspecialchars($ghe['tecnico_nome'] ?? '-') ?></strong></div>
                <div><span>Check-list</span><strong>#<?= (int)$ghe['checklist_id'] ?></strong></div>
                <div><span>Criado por</span><strong><?= htmlspecialchars($ghe['criado_por_nome'] ?? '-') ?></strong></div>
            </div>
            <?php if (!empty($ghe['observacoes'])): ?>
                <div class="ghe-observation"><span>Observações</span><p><?= nl2br(htmlspecialchars($ghe['observacoes'])) ?></p></div>
            <?php endif; ?>
        </article>

        <article class="ghe-detail-card">
            <div class="ghe-detail-card-title"><i class="fa-solid fa-briefcase"></i><h3>Cargos vinculados</h3><b><?= count($ghe['cargos'] ?? []) ?></b></div>
            <div class="ghe-cargo-list">
                <?php foreach ($ghe['cargos'] ?? [] as $cargo): ?>
                    <div class="ghe-cargo-item">
                        <i class="fa-solid fa-briefcase"></i>
                        <div><strong><?= htmlspecialchars($cargo['cargo_nome']) ?></strong><span><?= htmlspecialchars(($cargo['unidade_nome'] ?? 'Matriz') . ' · ' . $cargo['setor_nome']) ?></span></div>
                        <?php if (!empty($cargo['cbo'])): ?><small>CBO <?= htmlspecialchars($cargo['cbo']) ?></small><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($ghe['cargos'])): ?><div class="ghe-inline-empty">Nenhum cargo vinculado.</div><?php endif; ?>
            </div>
        </article>
    </section>

    <section class="ghe-risk-section">
        <div class="ghe-section-heading">
            <div><span>Caracterização da exposição</span><h3>Riscos aplicados</h3><p>Fontes geradoras, frequência, tempo de exposição e indicação de quantificação.</p></div>
            <strong><?= count($ghe['riscos'] ?? []) ?> riscos</strong>
        </div>

        <div class="ghe-risk-table-wrap">
            <table class="table ghe-risk-table align-middle">
                <thead><tr><th>Categoria / Risco</th><th>Fonte geradora</th><th>Exposição</th><th>Medição</th><th>Quantificação</th><?php if ($editavel): ?><th></th><?php endif; ?></tr></thead>
                <tbody>
                <?php foreach ($ghe['riscos'] ?? [] as $risco): ?>
                    <tr>
                        <td data-label="Risco"><span class="ghe-risk-category category-<?= htmlspecialchars(strtolower($risco['categoria'])) ?>"><?= htmlspecialchars($categoriaLabel($risco['categoria'])) ?></span><strong><?= htmlspecialchars($risco['risco_nome']) ?></strong></td>
                        <td data-label="Fonte geradora"><?= htmlspecialchars($risco['fonte_geradora'] ?: '-') ?><small><?= htmlspecialchars($risco['meio_propagacao'] ?: '') ?></small></td>
                        <td data-label="Exposição"><strong><?= htmlspecialchars($risco['frequencia'] ? ucfirst(strtolower($risco['frequencia'])) : '-') ?></strong><small><?= htmlspecialchars($risco['tempo_exposicao'] ? str_replace('_', ' ', ucfirst(strtolower($risco['tempo_exposicao']))) : '') ?></small></td>
                        <td data-label="Medição"><?= htmlspecialchars(trim(($risco['intensidade'] ?? '') . ' ' . ($risco['unidade_medida'] ?? ''))) ?: '-' ?></td>
                        <td data-label="Quantificação"><span class="ghe-quant-badge <?= (int)$risco['exige_quantificacao'] === 1 ? 'is-required' : '' ?>"><?= (int)$risco['exige_quantificacao'] === 1 ? 'Necessária' : 'Não aplicável' ?></span></td>
                        <?php if ($editavel): ?>
                            <td class="text-end" data-label="Ação">
                                <form method="POST" action="<?= BASE_URL ?>/ghe/<?= (int)$ghe['id'] ?>/riscos/remover/<?= (int)$risco['id'] ?>" onsubmit="return confirm('Remover este risco do GHE?');">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover risco"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($ghe['riscos'])): ?>
                    <tr><td colspan="<?= $editavel ? 6 : 5 ?>"><div class="ghe-inline-empty">Nenhum risco aplicado a este GHE.</div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($editavel): ?>
            <form method="POST" action="<?= BASE_URL ?>/ghe/<?= (int)$ghe['id'] ?>/riscos/salvar" class="ghe-risk-form-card">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <div class="ghe-section-title">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div><h3>Aplicar risco ao GHE</h3><p>Inclua a fonte geradora e a caracterização inicial da exposição.</p></div>
                </div>
                <div class="ghe-risk-form-grid">
                    <div class="ghe-field ghe-field-span-2"><label>Risco *</label><select name="risco_id" class="form-select" required><option value="">Selecione</option><?php foreach ($riscosDisponiveis as $risco): ?><option value="<?= (int)$risco['id'] ?>"><?= htmlspecialchars($categoriaLabel($risco['categoria']) . ' — ' . $risco['nome']) ?><?= (int)$risco['exige_quantificacao'] === 1 ? ' [Quantificável]' : '' ?></option><?php endforeach; ?></select></div>
                    <div class="ghe-field ghe-field-span-2"><label>Fonte geradora</label><input type="text" name="fonte_geradora" class="form-control" placeholder="Máquina, atividade ou processo"></div>
                    <div class="ghe-field"><label>Meio de propagação</label><input type="text" name="meio_propagacao" class="form-control"></div>
                    <div class="ghe-field"><label>Frequência</label><select name="frequencia" class="form-select"><option value="">Selecione</option><option value="EVENTUAL">Eventual</option><option value="ESPORADICA">Esporádica</option><option value="INTERMITENTE">Intermitente</option><option value="HABITUAL">Habitual</option><option value="PERMANENTE">Permanente</option></select></div>
                    <div class="ghe-field"><label>Tempo de exposição</label><select name="tempo_exposicao" class="form-select"><option value="">Selecione</option><option value="MUITO_BAIXO">Muito baixo</option><option value="BAIXO">Baixo</option><option value="MODERADO">Moderado</option><option value="ALTO">Alto</option><option value="MUITO_ALTO">Muito alto</option></select></div>
                    <div class="ghe-field"><label>Intensidade / medição</label><input type="text" name="intensidade" class="form-control" placeholder="Ex.: 82,4"></div>
                    <div class="ghe-field ghe-field-span-3"><label>Observações</label><input type="text" name="observacoes" class="form-control"></div>
                    <div class="ghe-risk-form-action"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Aplicar risco</button></div>
                </div>
            </form>
        <?php endif; ?>
    </section>

    <section class="ghe-bottom-actions">
        <a href="<?= BASE_URL ?>/checklists/visualizar/<?= (int)$ghe['checklist_id'] ?>?aba=ghe-riscos" class="btn btn-outline-primary"><i class="fa-regular fa-square-check"></i> Abrir no Check-list</a>
        <?php if ($editavel): ?>
            <form method="POST" action="<?= BASE_URL ?>/ghe/inativar/<?= (int)$ghe['id'] ?>" onsubmit="return confirm('Inativar este GHE? O histórico será preservado.');">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-outline-danger"><i class="fa-solid fa-circle-xmark"></i> Inativar GHE</button>
            </form>
        <?php elseif ((int)$ghe['ativo'] === 0 && in_array(strtoupper((string)$ghe['checklist_status']), ['ABERTO', 'EM_ANDAMENTO'], true)): ?>
            <form method="POST" action="<?= BASE_URL ?>/ghe/reativar/<?= (int)$ghe['id'] ?>">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-outline-success"><i class="fa-solid fa-rotate-left"></i> Reativar GHE</button>
            </form>
        <?php endif; ?>
    </section>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
