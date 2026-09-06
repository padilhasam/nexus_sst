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
<input type="hidden" name="_token" value="<?= htmlspecialchars($cs<div class="alert alert-primary d-flex gap-2 align-items-start" role="alert">
    <i class="fa-solid fa-shield-halved mt-1 flex-shrink-0"></i>
    <div>
        <strong>Fluxo oficial:</strong> Empresa &rarr; Unidade &rarr; Setor &rarr; Cargo &rarr; Funcionário.
        Setores e Cargos são catálogos globais e podem ser reutilizados em qualquer empresa.
    </div>
</div>

<section class="card mb-3">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 flex-shrink-0" style="width:42px;height:42px">
                <i class="fa-regular fa-building"></i>
            </span>
            <div>
                <h2 class="h6 fw-bold mb-1">1. Empresa e Unidade</h2>
                <p class="text-secondary small mb-0">A unidade será filtrada automaticamente conforme a empresa selecionada.</p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="empresa_id" class="form-label fw-semibold small">Empresa *</label>
                <select class="form-select" name="empresa_id" id="empresa_id" required>
                    <option value="">Selecione a empresa</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= (int)$empresa['id'] ?>" <?= $empresaSelecionada === (string)$empresa['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nome_fantasia'] ?: $empresa['razao_social']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="unidade_id" class="form-label fw-semibold small">Unidade vinculada *</label>
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
                <div class="form-text" id="unidadeAjuda">Somente as unidades da empresa escolhida serão exibidas.</div>
            </div>
        </div>
    </div>
</section>

<section class="card mb-3">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 flex-shrink-0" style="width:42px;height:42px">
                <i class="fa-solid fa-layer-group"></i>
            </span>
            <div>
                <h2 class="h6 fw-bold mb-1">2. Selecione os Setores</h2>
                <p class="text-secondary small mb-0">Escolha um ou mais setores do catálogo global para utilizar nesta unidade.</p>
            </div>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="search" class="form-control" id="buscarSetorHierarquia" placeholder="Buscar setor por nome ou código">
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-2" id="setoresSelector">
            <?php foreach ($setores as $setor): ?>
                <?php $setorTexto = strtolower(trim(($setor['codigo'] ?? '') . ' ' . ($setor['nome'] ?? ''))); ?>
                <div class="col" data-setor-search="<?= htmlspecialchars($setorTexto) ?>">
                    <input type="checkbox" class="btn-check org-sector-checkbox" value="<?= (int)$setor['id'] ?>" id="setor-hier-<?= (int)$setor['id'] ?>" autocomplete="off">
                    <label class="btn btn-outline-primary w-100 h-100 text-start d-flex align-items-center gap-2 p-2" for="setor-hier-<?= (int)$setor['id'] ?>">
                        <i class="fa-solid fa-check flex-shrink-0"></i>
                        <span style="min-width:0">
                            <span class="d-block fw-bold text-truncate"><?= htmlspecialchars($setor['nome']) ?></span>
                            <span class="d-block small"><?= !empty($setor['codigo']) ? 'Código ' . htmlspecialchars($setor['codigo']) : 'Catálogo global' ?></span>
                        </span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="card mb-3">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 flex-shrink-0" style="width:42px;height:42px">
                <i class="fa-solid fa-briefcase"></i>
            </span>
            <div>
                <h2 class="h6 fw-bold mb-1">3. Aloque os Cargos nos Setores</h2>
                <p class="text-secondary small mb-0">Para cada setor selecionado, marque os cargos que existirão na unidade.</p>
            </div>
        </div>

        <div id="cargoPanels">
            <div class="text-center text-secondary p-4 border rounded-3 bg-light" id="cargoPanelsEmpty">
                <i class="fa-solid fa-arrow-up fs-4 d-block mb-2"></i>
                <strong class="d-block">Selecione um setor acima</strong>
                <span class="small">Os cargos disponíveis aparecerão aqui.</span>
            </div>

            <?php foreach ($setores as $setor): ?>
                <article class="card mb-3 d-none" data-setor-panel="<?= (int)$setor['id'] ?>">
                    <div class="card-header d-flex align-items-center justify-content-between gap-3 bg-body-tertiary">
                        <div>
                            <span class="text-uppercase text-secondary fw-semibold small">Setor selecionado</span>
                            <h3 class="h6 fw-bold mb-0"><?= htmlspecialchars($setor['nome']) ?></h3>
                        </div>
                        <div class="text-end">
                            <span class="fs-4 fw-bold text-primary d-block" data-sector-counter>0</span>
                            <span class="text-secondary small">novos cargos</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="search" class="form-control" data-cargo-search placeholder="Buscar cargo neste setor">
                        </div>

                        <div class="row row-cols-1 row-cols-md-2 g-2">
                            <?php foreach ($cargos as $cargo): ?>
                                <?php
                                $valor = (int)$setor['id'] . ':' . (int)$cargo['id'];
                                $checado = in_array($valor, $vinculosSelecionados, true);
                                $cargoTexto = strtolower(trim(($cargo['codigo'] ?? '') . ' ' . ($cargo['nome'] ?? '') . ' ' . ($cargo['cbo'] ?? '')));
                                ?>
                                <div class="col" data-cargo-search-text="<?= htmlspecialchars($cargoTexto) ?>">
                                    <input
                                        type="checkbox"
                                        class="btn-check"
                                        name="vinculos[]"
                                        value="<?= $valor ?>"
                                        id="vinc-<?= (int)$setor['id'] ?>-<?= (int)$cargo['id'] ?>"
                                        data-sector-id="<?= (int)$setor['id'] ?>"
                                        data-cargo-id="<?= (int)$cargo['id'] ?>"
                                        autocomplete="off"
                                        <?= $checado ? 'checked' : '' ?>
                                    >
                                    <label class="org-role-option btn btn-outline-primary w-100 h-100 text-start d-flex align-items-start gap-2 p-2" for="vinc-<?= (int)$setor['id'] ?>-<?= (int)$cargo['id'] ?>">
                                        <i class="fa-solid fa-check mt-1 flex-shrink-0"></i>
                                        <span class="flex-grow-1" style="min-width:0">
                                            <span class="d-block fw-bold small"><?= htmlspecialchars($cargo['nome']) ?></span>
                                            <span class="d-block small text-body-secondary">
                                                <?= !empty($cargo['codigo']) ? 'Código ' . htmlspecialchars($cargo['codigo']) : 'Catálogo global' ?>
                                                <?= !empty($cargo['cbo']) ? ' &middot; CBO ' . htmlspecialchars($cargo['cbo']) : '' ?>
                                            </span>
                                        </span>
                                        <span class="org-existing-label d-none text-success fw-semibold small text-nowrap flex-shrink-0"><i class="fa-solid fa-lock"></i> Já vinculado</span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="card mb-3">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 flex-shrink-0" style="width:42px;height:42px">
                <i class="fa-solid fa-users"></i>
            </span>
            <div>
                <h2 class="h6 fw-bold mb-1">4. Funcionários</h2>
                <p class="text-secondary small mb-0">Após salvar, você será direcionado à estrutura da empresa para selecionar ou mover os funcionários dentro de cada cargo.</p>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-sm-3 g-2">
            <div class="col">
                <div class="border rounded-3 p-2 bg-light h-100">
                    <span class="d-block text-uppercase text-secondary fw-semibold small">Empresa</span>
                    <strong class="d-block" id="resumoEmpresa">Não selecionada</strong>
                </div>
            </div>
            <div class="col">
                <div class="border rounded-3 p-2 bg-light h-100">
                    <span class="d-block text-uppercase text-secondary fw-semibold small">Unidade</span>
                    <strong class="d-block" id="resumoUnidade">Não selecionada</strong>
                </div>
            </div>
            <div class="col">
                <div class="border rounded-3 p-2 bg-light h-100">
                    <span class="d-block text-uppercase text-secondary fw-semibold small">Novos vínculos</span>
                    <strong class="d-block" id="resumoVinculos">0</strong>
                </div>
            </div>
        </div>
nidade</span><strong id="resumoUnidade">Não selecionada</strong></div>
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
