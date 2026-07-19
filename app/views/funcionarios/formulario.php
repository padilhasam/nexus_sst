<section class="funcionario-form-section">
    <div class="funcionario-section-title">
        <i class="fa-solid fa-sitemap"></i>
        <div><h3>Vínculo hierárquico</h3><p>Selecione a posição ocupada pelo funcionário.</p></div>
    </div>

    <div class="funcionario-form-grid">
        <div class="funcionario-field">
            <label for="empresa_id">Empresa *</label>
            <select name="empresa_id" id="empresa_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($empresas as $empresa): ?>
                    <option value="<?= (int)$empresa['id'] ?>"
                        <?= (string)($dados['empresa_id'] ?? '') === (string)$empresa['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($empresa['nome_fantasia'] ?: $empresa['razao_social']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="funcionario-field">
            <label for="unidade_id">Unidade *</label>
            <select name="unidade_id" id="unidade_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($unidades as $unidade): ?>
                    <option value="<?= (int)$unidade['id'] ?>" data-empresa="<?= (int)$unidade['empresa_id'] ?>"
                        <?= (string)($dados['unidade_id'] ?? '') === (string)$unidade['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($unidade['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="funcionario-field funcionario-field-wide">
            <label for="hierarquia_id">Setor / Cargo *</label>
            <select name="hierarquia_id" id="hierarquia_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($hierarquias as $hierarquia): ?>
                    <option value="<?= (int)$hierarquia['id'] ?>"
                            data-empresa="<?= (int)$hierarquia['empresa_id'] ?>"
                            data-unidade="<?= (int)$hierarquia['unidade_id'] ?>"
                        <?= (string)($dados['hierarquia_id'] ?? '') === (string)$hierarquia['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(($hierarquia['setor_nome'] ?? '-') . ' — ' . ($hierarquia['cargo_nome'] ?? '-')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small>O funcionário será vinculado à hierarquia oficial da empresa.</small>
        </div>
    </div>
</section>

<section class="funcionario-form-section">
    <div class="funcionario-section-title">
        <i class="fa-regular fa-id-card"></i>
        <div><h3>Dados do funcionário</h3><p>Informe os dados cadastrais disponíveis.</p></div>
    </div>

    <div class="funcionario-form-grid">
        <div class="funcionario-field funcionario-field-wide">
            <label for="nome">Nome completo *</label>
            <input type="text" name="nome" id="nome" class="form-control" maxlength="180" required
                   value="<?= htmlspecialchars((string)($dados['nome'] ?? '')) ?>">
        </div>
        <div class="funcionario-field">
            <label for="matricula">Matrícula</label>
            <input type="text" name="matricula" id="matricula" class="form-control" maxlength="50"
                   value="<?= htmlspecialchars((string)($dados['matricula'] ?? '')) ?>">
        </div>
        <div class="funcionario-field">
            <label for="cpf">CPF</label>
            <input type="text" name="cpf" id="cpf" class="form-control" maxlength="20"
                   value="<?= htmlspecialchars((string)($dados['cpf'] ?? '')) ?>">
        </div>
        <div class="funcionario-field">
            <label for="codigo">Código interno</label>
            <input type="text" name="codigo" id="codigo" class="form-control" maxlength="30"
                   value="<?= htmlspecialchars((string)($dados['codigo'] ?? '')) ?>">
        </div>
        <div class="funcionario-field">
            <label for="codigo_externo">Código externo</label>
            <input type="text" name="codigo_externo" id="codigo_externo" class="form-control" maxlength="80"
                   value="<?= htmlspecialchars((string)($dados['codigo_externo'] ?? '')) ?>">
        </div>
        <div class="funcionario-field">
            <label for="data_admissao">Data de admissão</label>
            <input type="date" name="data_admissao" id="data_admissao" class="form-control"
                   value="<?= htmlspecialchars((string)($dados['data_admissao'] ?? '')) ?>">
        </div>
        <div class="funcionario-field funcionario-field-wide">
            <label for="observacoes">Observações</label>
            <textarea name="observacoes" id="observacoes" class="form-control" rows="4"><?= htmlspecialchars((string)($dados['observacoes'] ?? '')) ?></textarea>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const empresa = document.getElementById('empresa_id');
    const unidade = document.getElementById('unidade_id');
    const hierarquia = document.getElementById('hierarquia_id');

    function atualizarOpcoes() {
        const empresaId = empresa.value;
        const unidadeId = unidade.value;

        Array.from(unidade.options).forEach(function (option) {
            if (!option.value) return;
            option.hidden = empresaId !== '' && option.dataset.empresa !== empresaId;
            if (option.hidden && option.selected) unidade.value = '';
        });

        Array.from(hierarquia.options).forEach(function (option) {
            if (!option.value) return;
            option.hidden =
                (empresaId !== '' && option.dataset.empresa !== empresaId) ||
                (unidadeId !== '' && option.dataset.unidade !== unidadeId);
            if (option.hidden && option.selected) hierarquia.value = '';
        });
    }

    empresa.addEventListener('change', function () {
        unidade.value = '';
        hierarquia.value = '';
        atualizarOpcoes();
    });
    unidade.addEventListener('change', function () {
        hierarquia.value = '';
        atualizarOpcoes();
    });
    atualizarOpcoes();
});
</script>
