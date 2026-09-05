<?php
$dados = $hierarquia ?? ($dadosAnteriores ?? []);
$empresaSelecionada = (string)($dados['empresa_id'] ?? '');
$unidadeSelecionada = (string)($dados['unidade_id'] ?? '');
$vinculosSelecionados = is_array($dados['vinculos'] ?? null) ? array_map('strval', $dados['vinculos']) : [];
$vinculosExistentesJson = array_map(
    static fn (array $item): array => [
        'empresa_id' => (int)$item['empresa_id'],
        'unidade_id' => (int)$item['unidade_id'],
        'setor_id' => (int)$item['setor_id'],
        'cargo_id' => (int)$item['cargo_id'],
    ],
    $vinculosExistentes ?? []
);
?>
<input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

<div class="org-callout">
    <i class="fa-solid fa-shield-halved"></i>
    <div>
        <strong>Fluxo oficial:</strong> Empresa → Unidade → Setor → Cargo → Funcionário.
        Setores e Cargos são catálogos globais e podem ser reutilizados em qualquer empresa.
    </div>
</div>

<section class="org-form-section org-builder-section">
    <div class="org-section-title">
        <div class="org-section-icon"><i class="fa-regular fa-building"></i></div>
        <div><h2>1. Empresa e Unidade</h2><p>A unidade será filtrada automaticamente conforme a empresa selecionada.</p></div>
    </div>

    <div class="org-form-grid">
        <div class="org-field org-col-6">
            <label for="empresa_id">Empresa *</label>
            <select class="form-select" name="empresa_id" id="empresa_id" required>
                <option value="">Selecione a empresa</option>
                <?php foreach ($empresas as $empresa): ?>
                    <option value="<?= (int)$empresa['id'] ?>" <?= $empresaSelecionada === (string)$empresa['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($empresa['nome_fantasia'] ?: $empresa['razao_social']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="org-field org-col-6">
            <label for="unidade_id">Unidade vinculada *</label>
            <select class="form-select" name="unidade_id" id="unidade_id" required>
                <option value="">Selecione a unidade</option>
                <?php foreach ($unidades as $unidade): ?>
                    <option
                        value="<?= (int)$unidade['id'] ?>"
                        data-empresa="<?= (int)$unidade['empresa_id'] ?>"
                        <?= $unidadeSelecionada === (string)$unidade['id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($unidade['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small id="unidadeAjuda">Somente as unidades da empresa escolhida serão exibidas.</small>
        </div>
    </div>
</section>

<section class="org-form-section org-builder-section">
    <div class="org-section-title">
        <div class="org-section-icon"><i class="fa-solid fa-layer-group"></i></div>
        <div><h2>2. Selecione os Setores</h2><p>Escolha um ou mais setores do catálogo global para utilizar nesta unidade.</p></div>
    </div>

    <div class="org-builder-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="search" class="form-control" id="buscarSetorHierarquia" placeholder="Buscar setor por nome ou código">
    </div>

    <div class="org-selector-grid" id="setoresSelector">
        <?php foreach ($setores as $setor): ?>
            <?php $setorTexto = strtolower(trim(($setor['codigo'] ?? '') . ' ' . ($setor['nome'] ?? ''))); ?>
            <label class="org-selector-card" data-setor-search="<?= htmlspecialchars($setorTexto) ?>">
                <input type="checkbox" class="org-sector-checkbox" value="<?= (int)$setor['id'] ?>">
                <span class="org-selector-check"><i class="fa-solid fa-check"></i></span>
                <span class="org-selector-icon"><i class="fa-solid fa-layer-group"></i></span>
                <span class="org-selector-copy">
                    <strong><?= htmlspecialchars($setor['nome']) ?></strong>
                    <small><?= !empty($setor['codigo']) ? 'Código ' . htmlspecialchars($setor['codigo']) : 'Catálogo global' ?></small>
                </span>
            </label>
        <?php endforeach; ?>
    </div>
</section>

<section class="org-form-section org-builder-section">
    <div class="org-section-title">
        <div class="org-section-icon"><i class="fa-solid fa-briefcase"></i></div>
        <div><h2>3. Aloque os Cargos nos Setores</h2><p>Para cada setor selecionado, marque os cargos que existirão na unidade.</p></div>
    </div>

    <div id="cargoPanels" class="org-cargo-panels">
        <div class="org-builder-empty" id="cargoPanelsEmpty">
            <i class="fa-solid fa-arrow-up"></i>
            <strong>Selecione um setor acima</strong>
            <span>Os cargos disponíveis aparecerão aqui.</span>
        </div>

        <?php foreach ($setores as $setor): ?>
            <article class="org-cargo-panel d-none" data-setor-panel="<?= (int)$setor['id'] ?>">
                <header>
                    <div><span>Setor selecionado</span><h3><?= htmlspecialchars($setor['nome']) ?></h3></div>
                    <div class="org-panel-counter"><strong data-sector-counter>0</strong><span>novos cargos</span></div>
                </header>

                <div class="org-builder-search org-builder-search-compact">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" class="form-control" data-cargo-search placeholder="Buscar cargo neste setor">
                </div>

                <div class="org-role-grid">
                    <?php foreach ($cargos as $cargo): ?>
                        <?php
                        $valor = (int)$setor['id'] . ':' . (int)$cargo['id'];
                        $checado = in_array($valor, $vinculosSelecionados, true);
                        $cargoTexto = strtolower(trim(($cargo['codigo'] ?? '') . ' ' . ($cargo['nome'] ?? '') . ' ' . ($cargo['cbo'] ?? '')));
                        ?>
                        <label class="org-role-option" data-cargo-search-text="<?= htmlspecialchars($cargoTexto) ?>">
                            <input
                                type="checkbox"
                                name="vinculos[]"
                                value="<?= $valor ?>"
                                data-sector-id="<?= (int)$setor['id'] ?>"
                                data-cargo-id="<?= (int)$cargo['id'] ?>"
                                <?= $checado ? 'checked' : '' ?>
                            >
                            <span class="org-role-check"><i class="fa-solid fa-check"></i></span>
                            <span>
                                <strong><?= htmlspecialchars($cargo['nome']) ?></strong>
                                <small>
                                    <?= !empty($cargo['codigo']) ? 'Código ' . htmlspecialchars($cargo['codigo']) : 'Catálogo global' ?>
                                    <?= !empty($cargo['cbo']) ? ' · CBO ' . htmlspecialchars($cargo['cbo']) : '' ?>
                                </small>
                            </span>
                            <em class="org-existing-label d-none"><i class="fa-solid fa-lock"></i> Já vinculado</em>
                        </label>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="org-form-section org-builder-section org-builder-final-step">
    <div class="org-section-title">
        <div class="org-section-icon"><i class="fa-solid fa-users"></i></div>
        <div><h2>4. Funcionários</h2><p>Após salvar, você será direcionado à estrutura da empresa para selecionar ou mover os funcionários dentro de cada cargo.</p></div>
    </div>
    <div class="org-builder-summary">
        <div><span>Empresa</span><strong id="resumoEmpresa">Não selecionada</strong></div>
        <div><span>Unidade</span><strong id="resumoUnidade">Não selecionada</strong></div>
        <div><span>Novos vínculos</span><strong id="resumoVinculos">0</strong></div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const empresa = document.getElementById('empresa_id');
    const unidade = document.getElementById('unidade_id');
    const setorChecks = Array.from(document.querySelectorAll('.org-sector-checkbox'));
    const panels = Array.from(document.querySelectorAll('[data-setor-panel]'));
    const cargoChecks = Array.from(document.querySelectorAll('input[name="vinculos[]"]'));
    const existentes = <?= json_encode($vinculosExistentesJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const existentesSet = new Set(existentes.map((item) => `${item.empresa_id}:${item.unidade_id}:${item.setor_id}:${item.cargo_id}`));
    const buscaSetor = document.getElementById('buscarSetorHierarquia');
    const vazio = document.getElementById('cargoPanelsEmpty');

    const textoSelecionado = (select, padrao) => {
        const option = select?.selectedOptions?.[0];
        return option && option.value ? option.textContent.trim() : padrao;
    };

    function filtrarUnidades(reset = false) {
        const empresaId = empresa?.value || '';
        if (reset && unidade) unidade.value = '';
        Array.from(unidade?.options || []).forEach((option) => {
            if (!option.value) return;
            option.hidden = empresaId !== '' && option.dataset.empresa !== empresaId;
            option.disabled = option.hidden;
            if (option.hidden && option.selected) unidade.value = '';
        });
    }

    function contextoKey(check) {
        return `${empresa?.value || 0}:${unidade?.value || 0}:${check.dataset.sectorId}:${check.dataset.cargoId}`;
    }

    function atualizarExistentes() {
        cargoChecks.forEach((check) => {
            const option = check.closest('.org-role-option');
            const label = option?.querySelector('.org-existing-label');
            const existente = empresa?.value && unidade?.value && existentesSet.has(contextoKey(check));
            check.disabled = Boolean(existente);
            option?.classList.toggle('is-existing', Boolean(existente));
            label?.classList.toggle('d-none', !existente);
            if (existente) check.checked = true;
        });

        setorChecks.forEach((setorCheck) => {
            const setorId = setorCheck.value;
            const possuiExistente = cargoChecks.some((check) => check.dataset.sectorId === setorId && check.disabled && check.checked);
            if (possuiExistente) setorCheck.checked = true;
        });
    }

    function atualizarPaineis() {
        let visiveis = 0;
        panels.forEach((panel) => {
            const setorCheck = setorChecks.find((item) => item.value === panel.dataset.setorPanel);
            const mostrar = Boolean(setorCheck?.checked);
            panel.classList.toggle('d-none', !mostrar);
            if (mostrar) visiveis++;
            if (!mostrar) {
                panel.querySelectorAll('input[name="vinculos[]"]:not(:disabled)').forEach((input) => { input.checked = false; });
            }
        });
        vazio?.classList.toggle('d-none', visiveis > 0);
        atualizarResumo();
    }

    function atualizarContadores() {
        panels.forEach((panel) => {
            const total = panel.querySelectorAll('input[name="vinculos[]"]:checked:not(:disabled)').length;
            const counter = panel.querySelector('[data-sector-counter]');
            if (counter) counter.textContent = String(total);
        });
    }

    function atualizarResumo() {
        atualizarContadores();
        const novos = cargoChecks.filter((check) => check.checked && !check.disabled).length;
        const resumoEmpresa = document.getElementById('resumoEmpresa');
        const resumoUnidade = document.getElementById('resumoUnidade');
        const resumoVinculos = document.getElementById('resumoVinculos');
        if (resumoEmpresa) resumoEmpresa.textContent = textoSelecionado(empresa, 'Não selecionada');
        if (resumoUnidade) resumoUnidade.textContent = textoSelecionado(unidade, 'Não selecionada');
        if (resumoVinculos) resumoVinculos.textContent = String(novos);
    }

    empresa?.addEventListener('change', function () {
        filtrarUnidades(true);
        cargoChecks.forEach((check) => { if (!check.disabled) check.checked = false; });
        setorChecks.forEach((check) => { check.checked = false; });
        atualizarExistentes();
        atualizarPaineis();
    });

    unidade?.addEventListener('change', function () {
        cargoChecks.forEach((check) => { if (!check.disabled) check.checked = false; });
        setorChecks.forEach((check) => { check.checked = false; });
        atualizarExistentes();
        atualizarPaineis();
    });

    setorChecks.forEach((check) => check.addEventListener('change', atualizarPaineis));
    cargoChecks.forEach((check) => check.addEventListener('change', atualizarResumo));

    buscaSetor?.addEventListener('input', function () {
        const termo = this.value.toLocaleLowerCase('pt-BR').trim();
        document.querySelectorAll('[data-setor-search]').forEach((card) => {
            card.classList.toggle('d-none', termo !== '' && !(card.dataset.setorSearch || '').includes(termo));
        });
    });

    document.querySelectorAll('[data-cargo-search]').forEach((input) => {
        input.addEventListener('input', function () {
            const termo = this.value.toLocaleLowerCase('pt-BR').trim();
            const panel = this.closest('[data-setor-panel]');
            panel?.querySelectorAll('[data-cargo-search-text]').forEach((option) => {
                option.classList.toggle('d-none', termo !== '' && !(option.dataset.cargoSearchText || '').includes(termo));
            });
        });
    });

    filtrarUnidades(false);
    atualizarExistentes();
    cargoChecks.filter((check) => check.checked).forEach((check) => {
        const setor = setorChecks.find((item) => item.value === check.dataset.sectorId);
        if (setor) setor.checked = true;
    });
    atualizarPaineis();
});
</script>
