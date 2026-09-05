<?php
$esc = static fn (mixed $valor): string => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
$funcionariosLista = $funcionarios ?? [];
$totalFuncionarios = count($funcionariosLista);
$totalAtivos = count(array_filter($funcionariosLista, static fn (array $f): bool => (int)($f['ativo'] ?? 0) === 1));
$iniciais = static function (string $nome): string {
    $partes = preg_split('/\s+/', trim($nome)) ?: [];
    $partes = array_values(array_filter($partes));
    if ($partes === []) {
        return '--';
    }
    $primeira = function_exists('mb_substr') ? mb_substr($partes[0], 0, 1) : substr($partes[0], 0, 1);
    $ultimaParte = count($partes) > 1 ? $partes[array_key_last($partes)] : '';
    $ultima = $ultimaParte !== ''
        ? (function_exists('mb_substr') ? mb_substr($ultimaParte, 0, 1) : substr($ultimaParte, 0, 1))
        : '';
    return strtoupper($primeira . $ultima);
};
?>

<section class="checklist-workspace">
    <header class="checklist-workspace-header">
        <div>
            <span class="checklist-eyebrow">Quadro funcional</span>
            <h2>Funcionários da unidade</h2>
            <p>
                Registre os trabalhadores na hierarquia correta e sinalize desligamentos sem apagar o histórico
                utilizado nos documentos ocupacionais.
            </p>
        </div>
        <span class="checklist-count"><strong><?= $totalAtivos ?></strong> ativos</span>
    </header>

    <?php if (!$somenteLeitura): ?>
        <article class="checklist-form-card">
            <div class="checklist-form-heading">
                <span class="checklist-form-icon"><i class="fa-solid fa-user-plus"></i></span>
                <div>
                    <h3>Adicionar funcionário</h3>
                    <p>O setor e o cargo são definidos pelo vínculo selecionado na hierarquia.</p>
                </div>
            </div>

            <?php if (empty($hierarquia)): ?>
                <div class="checklist-empty is-compact">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <h3>Cadastre a hierarquia primeiro</h3>
                    <p>É necessário possuir ao menos um setor e cargo antes de incluir funcionários.</p>
                    <a class="btn btn-primary" href="<?= BASE_URL ?>/checklists/visualizar/<?= $checklistId ?>?aba=hierarquia">
                        <i class="fa-solid fa-sitemap"></i> Ir para Hierarquia
                    </a>
                </div>
            <?php else: ?>
                <form
                    method="POST"
                    action="<?= BASE_URL ?>/checklists/<?= $checklistId ?>/funcionarios/salvar"
                    class="checklist-form-grid"
                    data-prevent-double-submit
                >
                    <input type="hidden" name="_token" value="<?= $esc($csrfToken ?? '') ?>">

                    <div class="checklist-field span-2">
                        <label for="funcionario_hierarquia_id">Setor e cargo *</label>
                        <select class="form-select" id="funcionario_hierarquia_id" name="hierarquia_id" required>
                            <option value="">Selecione a posição na hierarquia</option>
                            <?php foreach ($hierarquia as $linha): ?>
                                <option value="<?= (int)$linha['id'] ?>">
                                    <?= $esc(($linha['setor_nome'] ?? '-') . ' · ' . ($linha['cargo_nome'] ?? '-')) ?>
                                    <?= !empty($linha['cbo']) ? ' — CBO ' . $esc($linha['cbo']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="checklist-field span-2">
                        <label for="funcionario_nome">Nome completo *</label>
                        <input class="form-control" id="funcionario_nome" name="nome" maxlength="180" required>
                    </div>

                    <div class="checklist-field">
                        <label for="funcionario_matricula">Matrícula</label>
                        <input class="form-control" id="funcionario_matricula" name="matricula" maxlength="50">
                    </div>

                    <div class="checklist-field">
                        <label for="funcionario_codigo">Código interno</label>
                        <input class="form-control" id="funcionario_codigo" name="codigo" maxlength="30">
                    </div>

                    <div class="checklist-field">
                        <label for="funcionario_cpf">CPF</label>
                        <input class="form-control" id="funcionario_cpf" name="cpf" maxlength="20" inputmode="numeric" data-mask="cpf">
                    </div>

                    <div class="checklist-field">
                        <label for="funcionario_admissao">Data de admissão</label>
                        <input class="form-control" id="funcionario_admissao" name="data_admissao" type="date">
                    </div>

                    <div class="checklist-field span-2">
                        <label for="funcionario_observacoes">Observações</label>
                        <input
                            class="form-control"
                            id="funcionario_observacoes"
                            name="observacoes"
                            maxlength="255"
                            placeholder="Informações verificadas durante a visita"
                        >
                    </div>

                    <div class="checklist-form-action">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Adicionar
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </article>
    <?php endif; ?>

    <div class="employee-grid">
        <?php if ($totalFuncionarios === 0): ?>
            <div class="checklist-empty">
                <i class="fa-solid fa-users-slash"></i>
                <h3>Nenhum funcionário cadastrado</h3>
                <p>Inclua os trabalhadores observados ou importe a hierarquia completa da empresa.</p>
            </div>
        <?php else: ?>
            <?php foreach ($funcionariosLista as $funcionario): ?>
                <?php
                $ativo = (int)($funcionario['ativo'] ?? 0) === 1;
                $nome = (string)($funcionario['nome'] ?? 'Funcionário');
                ?>
                <article class="employee-card <?= $ativo ? '' : 'is-inactive' ?>">
                    <span class="employee-avatar"><?= $esc($iniciais($nome)) ?></span>
                    <div class="employee-info">
                        <div class="employee-title-row">
                            <h3 title="<?= $esc($nome) ?>"><?= $esc($nome) ?></h3>
                            <span class="employee-status <?= $ativo ? 'is-active' : 'is-inactive' ?>">
                                <?= $ativo ? 'ATIVO' : 'INATIVO' ?>
                            </span>
                        </div>
                        <p><?= $esc(($funcionario['setor_nome'] ?? '-') . ' · ' . ($funcionario['cargo_nome'] ?? '-')) ?></p>
                        <div class="employee-meta">
                            <?php if (!empty($funcionario['matricula'])): ?>
                                <span><i class="fa-solid fa-id-badge"></i> <?= $esc($funcionario['matricula']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($funcionario['cpf'])): ?>
                                <span><i class="fa-solid fa-address-card"></i> <?= $esc($funcionario['cpf']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($funcionario['data_admissao'])): ?>
                                <span><i class="fa-regular fa-calendar-check"></i> Adm. <?= date('d/m/Y', strtotime($funcionario['data_admissao'])) ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (!$ativo && !empty($funcionario['motivo_inativacao'])): ?>
                            <small class="employee-inactive-reason">
                                <i class="fa-solid fa-circle-info"></i>
                                <?= $esc($funcionario['motivo_inativacao']) ?>
                                <?= !empty($funcionario['data_desligamento']) ? ' · ' . date('d/m/Y', strtotime($funcionario['data_desligamento'])) : '' ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <?php if ($ativo && !$somenteLeitura): ?>
                        <form
                            method="POST"
                            action="<?= BASE_URL ?>/checklists/<?= $checklistId ?>/funcionarios/inativar/<?= (int)$funcionario['id'] ?>"
                            class="employee-inactivate-form employee-inactivate-form-dated"
                            data-confirm-message="Confirma a inativação deste funcionário? O histórico será preservado."
                            data-prevent-double-submit
                        >
                            <input type="hidden" name="_token" value="<?= $esc($csrfToken ?? '') ?>">
                            <input
                                type="date"
                                class="form-control form-control-sm"
                                name="data_desligamento"
                                value="<?= date('Y-m-d') ?>"
                                aria-label="Data de desligamento"
                            >
                            <input
                                class="form-control form-control-sm"
                                name="motivo"
                                maxlength="255"
                                required
                                placeholder="Motivo do desligamento/inativação"
                                aria-label="Motivo da inativação"
                            >
                            <button class="btn btn-outline-danger btn-sm" type="submit" title="Inativar funcionário">
                                <i class="fa-solid fa-user-slash"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
