<?php
$ghe = $ghe ?? [];
$checklist = $checklist ?? null;
$hierarquias = $hierarquias ?? [];
$dadosAnteriores = $dadosAnteriores ?? [];
$csrfToken = $csrfToken ?? '';
$modoEdicao = !empty($ghe['id']);

$valor = static function (string $campo, mixed $padrao = '') use ($ghe, $dadosAnteriores): mixed {
    if (array_key_exists($campo, $dadosAnteriores)) {
        return $dadosAnteriores[$campo];
    }
    return $ghe[$campo] ?? $padrao;
};

$selecionados = $dadosAnteriores['hierarquias'] ?? array_column($ghe['cargos'] ?? [], 'hierarquia_id');
$selecionados = array_map('intval', is_array($selecionados) ? $selecionados : []);
?>

<section class="ghe-form-card">
    <div class="ghe-context-card">
        <div class="ghe-context-icon"><i class="fa-regular fa-square-check"></i></div>
        <div>
            <span>Check-list de origem</span>
            <strong>#<?= (int)($checklist['id'] ?? $ghe['checklist_id'] ?? 0) ?> — <?= htmlspecialchars($checklist['empresa_nome'] ?? $ghe['empresa_nome'] ?? '-') ?></strong>
            <small><?= htmlspecialchars($checklist['unidade_nome'] ?? $ghe['unidade_nome'] ?? 'Matriz') ?> · TST: <?= htmlspecialchars($checklist['tecnico_nome'] ?? $ghe['tecnico_nome'] ?? '-') ?></small>
        </div>
    </div>

    <form method="POST" action="<?= $modoEdicao ? BASE_URL . '/ghe/atualizar/' . (int)$ghe['id'] : BASE_URL . '/ghe/salvar' ?>">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <?php if (!$modoEdicao): ?>
            <input type="hidden" name="checklist_id" value="<?= (int)($checklist['id'] ?? 0) ?>">
        <?php endif; ?>

        <div class="ghe-form-section">
            <div class="ghe-section-title">
                <i class="fa-solid fa-people-group"></i>
                <div><h3>Identificação do GHE</h3><p>Informe o código, nome e a descrição das atividades homogêneas.</p></div>
            </div>

            <div class="ghe-form-grid">
                <div class="ghe-field">
                    <label for="codigo">Código *</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" maxlength="40"
                           placeholder="GHE 01" required value="<?= htmlspecialchars((string)$valor('codigo')) ?>">
                </div>

                <div class="ghe-field ghe-field-wide-name">
                    <label for="nome">Nome do GHE *</label>
                    <input type="text" id="nome" name="nome" class="form-control" maxlength="180"
                           placeholder="Administrativo" required value="<?= htmlspecialchars((string)$valor('nome')) ?>">
                </div>

                <div class="ghe-field ghe-field-wide">
                    <label for="descricao">Descrição das atividades</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="3"
                              placeholder="Descreva as atividades executadas pelos cargos deste grupo."><?= htmlspecialchars((string)$valor('descricao')) ?></textarea>
                </div>

                <div class="ghe-field ghe-field-wide">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" rows="2"><?= htmlspecialchars((string)$valor('observacoes')) ?></textarea>
                </div>
            </div>
        </div>

        <div class="ghe-form-section">
            <div class="ghe-section-title">
                <i class="fa-solid fa-briefcase"></i>
                <div><h3>Cargos vinculados</h3><p>Selecione os cargos que possuem exposição semelhante.</p></div>
            </div>

            <?php if (!empty($hierarquias)): ?>
                <div class="ghe-hierarchy-selector">
                    <?php foreach ($hierarquias as $item): ?>
                        <label class="ghe-hierarchy-option">
                            <input type="checkbox" name="hierarquias[]" value="<?= (int)$item['id'] ?>"
                                <?= in_array((int)$item['id'], $selecionados, true) ? 'checked' : '' ?>>
                            <span>
                                <strong><?= htmlspecialchars($item['cargo_nome']) ?></strong>
                                <small><?= htmlspecialchars(($item['unidade_nome'] ?? 'Matriz') . ' · ' . $item['setor_nome']) ?></small>
                                <?php if (!empty($item['cbo'])): ?><em>CBO <?= htmlspecialchars($item['cbo']) ?></em><?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="ghe-form-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Nenhum cargo foi encontrado na hierarquia deste check-list. Cadastre a hierarquia antes de criar o GHE.
                </div>
            <?php endif; ?>
        </div>

        <div class="ghe-form-actions">
            <a href="<?= $modoEdicao ? BASE_URL . '/ghe/visualizar/' . (int)$ghe['id'] : BASE_URL . '/ghe' ?>" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary" <?= empty($hierarquias) ? 'disabled' : '' ?>>
                <i class="fa-solid fa-floppy-disk"></i>
                <?= $modoEdicao ? 'Salvar alterações' : 'Criar GHE' ?>
            </button>
        </div>
    </form>
</section>
