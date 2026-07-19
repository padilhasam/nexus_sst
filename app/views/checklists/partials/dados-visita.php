<?php
$empresaNome = !empty($c['empresa_fantasia'])
    ? $c['empresa_fantasia']
    : ($c['empresa_nome'] ?? 'Empresa não informada');
$unidadeNome = !empty($c['unidade_fantasia'])
    ? $c['unidade_fantasia']
    : (!empty($c['unidade_razao_social']) ? $c['unidade_razao_social'] : ($c['unidade_nome'] ?? 'Matriz'));
$usarUnidade = !empty($c['unidade_id']);
$logradouro = $usarUnidade ? ($c['unidade_endereco'] ?? '') : ($c['empresa_logradouro'] ?? $c['empresa_endereco'] ?? '');
$numero = $usarUnidade ? ($c['unidade_numero'] ?? '') : ($c['empresa_numero'] ?? '');
$complemento = $usarUnidade ? ($c['unidade_complemento'] ?? '') : ($c['empresa_complemento'] ?? '');
$bairro = $usarUnidade ? ($c['unidade_bairro'] ?? '') : ($c['empresa_bairro'] ?? '');
$cidade = $usarUnidade ? ($c['unidade_cidade'] ?? '') : ($c['empresa_cidade'] ?? '');
$uf = $usarUnidade ? ($c['unidade_uf'] ?? '') : ($c['empresa_uf'] ?? '');
$cep = $usarUnidade ? ($c['unidade_cep'] ?? '') : ($c['empresa_cep'] ?? '');
$enderecoLinha = implode(', ', array_filter([$logradouro, $numero, $complemento]));
$cidadeLinha = implode(' · ', array_filter([$bairro, trim($cidade . ($uf ? '/' . $uf : '')), $cep ? 'CEP ' . $cep : '']));
$prioridade = match (strtoupper((string)($c['agenda_prioridade'] ?? $c['prioridade'] ?? 'PADRAO'))) {
    'CRITICA' => 'Crítica', 'URGENTE' => 'Urgente', default => 'Padrão'
};
$statusLabel = match ($statusChecklist) {
    'EM_ANDAMENTO' => 'Em andamento', 'CONCLUIDO' => 'Concluído', 'CANCELADO' => 'Cancelado', default => 'Aberto'
};
?>
<section class="checklist-section-grid">
    <article class="checklist-panel checklist-panel-main">
        <div class="checklist-panel-heading">
            <div>
                <span class="checklist-eyebrow">Identificação</span>
                <h2>Dados da visita técnica</h2>
            </div>
            <span class="checklist-status status-<?= strtolower($statusChecklist) ?>"><?= htmlspecialchars($statusLabel) ?></span>
        </div>

        <div class="checklist-data-grid">
            <div class="checklist-data-item">
                <span>Código da hierarquia</span>
                <strong><?= htmlspecialchars($c['empresa_codigo'] ?? '-') ?></strong>
            </div>
            <div class="checklist-data-item">
                <span>Prioridade</span>
                <strong><?= htmlspecialchars($prioridade) ?></strong>
            </div>
            <div class="checklist-data-item is-wide">
                <span>Empresa</span>
                <strong><?= htmlspecialchars($empresaNome) ?></strong>
                <small>CNPJ <?= htmlspecialchars($c['empresa_cnpj'] ?? '-') ?></small>
            </div>
            <div class="checklist-data-item is-wide">
                <span>Unidade</span>
                <strong><?= htmlspecialchars($unidadeNome) ?></strong>
                <small>CNPJ <?= htmlspecialchars($c['unidade_cnpj'] ?? '-') ?></small>
            </div>
            <div class="checklist-data-item">
                <span>Data</span>
                <strong><?= !empty($c['data_visita']) ? date('d/m/Y', strtotime($c['data_visita'])) : '-' ?></strong>
            </div>
            <div class="checklist-data-item">
                <span>Horário</span>
                <strong>
                    <?= !empty($c['hora_visita']) ? substr($c['hora_visita'], 0, 5) : '-' ?>
                    <?= !empty($c['hora_fim']) ? ' às ' . substr($c['hora_fim'], 0, 5) : '' ?>
                </strong>
            </div>
            <div class="checklist-data-item is-full">
                <span>Endereço completo</span>
                <strong><?= htmlspecialchars($enderecoLinha !== '' ? $enderecoLinha : 'Não informado') ?></strong>
                <small><?= htmlspecialchars($cidadeLinha) ?></small>
            </div>
            <div class="checklist-data-item is-wide">
                <span>Responsável da empresa</span>
                <strong><?= htmlspecialchars($c['responsavel_acompanhamento'] ?? 'Não informado') ?></strong>
            </div>
            <div class="checklist-data-item is-wide">
                <span>Técnico responsável</span>
                <strong><?= htmlspecialchars($c['tecnico_nome'] ?? '-') ?></strong>
                <small><?= htmlspecialchars($c['tecnico_registro'] ?? '') ?></small>
            </div>
        </div>
    </article>

    <aside class="checklist-side-stack">
        <article class="checklist-panel">
            <div class="checklist-panel-heading compact">
                <div><span class="checklist-eyebrow">Resumo</span><h2>Andamento</h2></div>
            </div>
            <div class="checklist-summary-list">
                <div><span>Iniciado em</span><strong><?= !empty($c['data_inicio']) ? date('d/m/Y H:i', strtotime($c['data_inicio'])) : '-' ?></strong></div>
                <div><span>Hierarquias</span><strong><?= (int)($progresso['hierarquias'] ?? 0) ?></strong></div>
                <div><span>Funcionários ativos</span><strong><?= (int)($progresso['funcionarios'] ?? 0) ?></strong></div>
                <div><span>GHEs</span><strong><?= (int)($progresso['ghes'] ?? 0) ?></strong></div>
                <div><span>Riscos aplicados</span><strong><?= (int)($progresso['riscos'] ?? 0) ?></strong></div>
            </div>
        </article>

        <article class="checklist-panel">
            <div class="checklist-panel-heading compact"><div><span class="checklist-eyebrow">Escopo</span><h2>Objetivo</h2></div></div>
            <p class="checklist-text-block"><?= nl2br(htmlspecialchars($c['objetivo'] ?? 'Não informado.')) ?></p>
        </article>

        <article class="checklist-panel">
            <div class="checklist-panel-heading compact"><div><span class="checklist-eyebrow">Registro</span><h2>Observações</h2></div></div>
            <p class="checklist-text-block"><?= nl2br(htmlspecialchars($c['observacoes'] ?? 'Sem observações.')) ?></p>
        </article>

        <article class="checklist-panel">
            <div class="checklist-panel-heading compact">
                <div>
                    <span class="checklist-eyebrow">Atalhos</span>
                    <h2>Ações rápidas</h2>
                </div>
            </div>

            <div class="checklist-quick-actions">
                <a href="<?= BASE_URL ?>/checklists/visualizar/<?= $checklistId ?>?aba=hierarquia">
                    <i class="fa-solid fa-sitemap"></i>
                    <span>Gerenciar hierarquia</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>

                <a href="<?= BASE_URL ?>/checklists/visualizar/<?= $checklistId ?>?aba=funcionarios">
                    <i class="fa-solid fa-users"></i>
                    <span>Gerenciar funcionários</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>

                <a href="<?= BASE_URL ?>/checklists/visualizar/<?= $checklistId ?>?aba=ghe-riscos">
                    <i class="fa-solid fa-flask-vial"></i>
                    <span>Gerenciar GHE e riscos</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>

                <span class="is-disabled">
                    <i class="fa-solid fa-helmet-safety"></i>
                    <span>EPI / EPC</span>
                    <small>Em breve</small>
                </span>
            </div>
        </article>
    </aside>
</section>
