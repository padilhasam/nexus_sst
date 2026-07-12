<?php
$css = 'cargos.css';
require_once dirname(__DIR__) . '/templates/header.php';
?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">

        <?php
        $sucesso = $_SESSION['sucesso'] ?? null;
        $erro = $_SESSION['erro'] ?? null;
        unset($_SESSION['sucesso'], $_SESSION['erro']);
        ?>

        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
            <?php if ($sucesso): ?>
                <div id="toastSucesso" class="toast text-bg-success border-0 shadow-lg">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-circle-check me-2"></i>
                            <?= htmlspecialchars($sucesso) ?>
                        </div>
                        <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div id="toastErro" class="toast text-bg-danger border-0 shadow-lg">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-circle-exclamation me-2"></i>
                            <?= htmlspecialchars($erro) ?>
                        </div>
                        <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <header class="page-header-cargos mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                          style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #084298); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.25);">
                        <i class="fas fa-briefcase text-white" style="font-size: 1.10rem;"></i>
                    </span>

                    Cargos e Funções

                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold fs-6 border border-primary-subtle">
                        <?= count($cargos ?? []) ?>
                    </span>
                </h3>

                <small class="text-muted d-block mt-1">
                    Gestão dos cargos e funções organizacionais para análise de riscos.
                </small>
            </div>

            <div class="page-header-actions d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-light border rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fas fa-gauge-high me-1 text-primary"></i>
                    Dashboard
                </a>

                <a href="<?= BASE_URL ?>/cargos/criar" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i>
                    Novo Cargo
                </a>
            </div>
        </header>

        <?php if (!empty($cargos)): ?>

            <section class="cargos-app-view">

                <div class="cargos-app-toolbar bg-white border rounded-4 shadow-sm p-3 mb-3">
                    <div class="input-group cargos-app-search">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>

                        <input
                            type="text"
                            id="buscarCargo"
                            class="form-control border-start-0"
                            placeholder="Buscar cargo, setor, CBO ou código...">
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-3 gap-2">
                        <small class="text-muted">
                            <span id="contadorCargos"><?= count($cargos ?? []) ?></span>
                            cargo(s) exibido(s)
                        </small>
                    </div>
                </div>

                <div class="cargos-app-list">
                    <?php foreach ($cargos as $c): ?>
                        <?php
                        $cargoAtivo = (int)($c['ativo'] ?? 1) === 1;

                        $textoBusca = strtolower(
                            ($c['codigo'] ?? '') . ' ' .
                            ($c['codigo_externo'] ?? '') . ' ' .
                            ($c['nome'] ?? '') . ' ' .
                            ($c['setor_nome'] ?? '') . ' ' .
                            ($c['cbo'] ?? '') . ' ' .
                            ($cargoAtivo ? 'ativo ativa' : 'inativo inativa')
                        );
                        ?>

                        <article class="cargo-app-card" data-search="<?= htmlspecialchars($textoBusca) ?>">

                            <div class="cargo-app-card-main">
                                <div class="cargo-app-icon">
                                    <i class="fas fa-briefcase"></i>
                                </div>

                                <div class="cargo-app-content">
                                    <h5><?= htmlspecialchars($c['nome'] ?? 'Cargo sem nome') ?></h5>

                                    <p class="cargo-app-subtitle">
                                        <?= htmlspecialchars($c['setor_nome'] ?? 'Setor não informado') ?>
                                    </p>

                                    <div class="cargo-app-meta">
                                        <span>
                                            <i class="fas fa-hashtag"></i>
                                            <?= !empty($c['codigo']) ? htmlspecialchars($c['codigo']) : 'Código interno não informado' ?>
                                        </span>

                                        <span>
                                            <i class="fas fa-link"></i>
                                            <?= !empty($c['codigo_externo']) ? htmlspecialchars($c['codigo_externo']) : 'Código externo não informado' ?>
                                        </span>

                                        <span>
                                            <i class="fas fa-id-card"></i>
                                            <?= !empty($c['cbo']) ? htmlspecialchars($c['cbo']) : 'CBO não informado' ?>
                                        </span>

                                        <span>
                                            <i class="fas fa-sitemap"></i>
                                            <?= !empty($c['setor_nome']) ? htmlspecialchars($c['setor_nome']) : 'Setor não informado' ?>
                                        </span>

                                        <span>
                                            <i class="<?= $cargoAtivo ? 'fas fa-circle-check text-success' : 'fas fa-circle-xmark text-danger' ?>"></i>
                                            <?= $cargoAtivo ? 'Cargo Ativo' : 'Cargo Inativo' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="cargo-app-actions">
                                <button
                                    class="btn btn-outline-secondary rounded-pill px-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalCargo<?= $c['id'] ?>">
                                    <i class="fas fa-circle-info me-1"></i>
                                    Ficha
                                </button>

                                <a href="<?= BASE_URL ?>/cargos/editar/<?= $c['id'] ?>" class="btn btn-outline-primary rounded-pill px-3">
                                    <i class="fas fa-edit me-1"></i>
                                    Editar
                                </a>

                                <a href="<?= BASE_URL ?>/cargos/excluir/<?= $c['id'] ?>"
                                   class="btn btn-outline-danger rounded-pill px-3"
                                   onclick="return confirm('Deseja excluir este cargo?')">
                                    <i class="fas fa-trash me-1"></i>
                                    Excluir
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div id="semResultadoCargos" class="text-center py-5 text-muted d-none">
                    <i class="fas fa-magnifying-glass fa-2x mb-3 opacity-50"></i>
                    <h6 class="fw-bold text-dark">Nenhum cargo encontrado</h6>
                    <p class="small mb-0">Tente buscar por cargo, setor, CBO ou código.</p>
                </div>

                <div class="cargos-pagination bg-white border rounded-4 shadow-sm p-3 mt-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <small class="text-muted">
                            Página <span id="paginaAtualCargos">1</span> de <span id="totalPaginasCargos">1</span>
                        </small>

                        <div class="d-flex gap-2">
                            <button type="button" id="btnAnteriorCargos" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-chevron-left me-1"></i>
                                Anterior
                            </button>

                            <button type="button" id="btnProximaCargos" class="btn btn-primary rounded-pill px-4">
                                Próxima
                                <i class="fas fa-chevron-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </section>

        <?php else: ?>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div class="text-center py-5 text-muted">
                        <div class="d-flex align-items-center justify-content-center rounded-circle mx-auto mb-3 bg-light text-muted opacity-70" style="width: 70px; height: 70px;">
                            <i class="fas fa-briefcase fa-2x"></i>
                        </div>

                        <h5 class="fw-bold text-dark mb-1">Nenhum cargo cadastrado</h5>

                        <p class="small text-muted mb-3">
                            Clique no botão abaixo para adicionar o primeiro cargo.
                        </p>

                        <a href="<?= BASE_URL ?>/cargos/criar" class="btn btn-primary btn-sm rounded-pill px-4 fw-medium shadow-sm">
                            <i class="fas fa-plus-circle me-1"></i>
                            Cadastrar Cargo
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <?php if (!empty($cargos)): foreach ($cargos as $c): ?>
        <?php $cargoAtivo = (int)($c['ativo'] ?? 1) === 1; ?>

        <div class="modal fade" id="modalCargo<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content border-0 shadow-lg rounded-3">

                    <div class="modal-header bg-light border-bottom py-3">
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-briefcase border p-2 bg-light rounded-3 text-secondary"></i>
                            Ficha do Cargo #<?= $c['id'] ?>
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="mb-3 border-bottom pb-2">
                            <label class="text-secondary small fw-semibold d-block">Nome do Cargo</label>
                            <span class="text-dark fw-bold fs-5">
                                <?= htmlspecialchars($c['nome'] ?? '-') ?>
                            </span>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">Código Interno</label>
                                <span class="font-monospace text-primary fw-bold">
                                    <?= htmlspecialchars($c['codigo'] ?? '-') ?>
                                </span>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">Código Externo</label>
                                <span class="font-monospace text-primary fw-bold">
                                    <?= htmlspecialchars($c['codigo_externo'] ?? '-') ?>
                                </span>
                            </div>
                        </div>

                        <div class="row g-3 mb-3 border-top pt-3">
                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">Setor</label>
                                <span class="text-dark fw-medium">
                                    <?= htmlspecialchars($c['setor_nome'] ?? '-') ?>
                                </span>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">CBO</label>
                                <span class="font-monospace text-dark fw-bold">
                                    <?= !empty($c['cbo']) ? htmlspecialchars($c['cbo']) : '-' ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($c['descricao'])): ?>
                            <div class="border-top pt-3">
                                <label class="text-secondary small fw-semibold d-block">Descrição</label>
                                <span class="text-dark">
                                    <?= nl2br(htmlspecialchars($c['descricao'])) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="border-top mt-3 pt-3">
                            <label class="text-secondary small fw-semibold d-block mb-1">Status do Cargo</label>

                            <?= $cargoAtivo
                                ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">Cargo Ativo</span>'
                                : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-semibold">Cargo Inativo</span>'
                            ?>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top py-3">
                        <a href="<?= BASE_URL ?>/cargos/editar/<?= $c['id'] ?>" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-edit me-1"></i>
                            Editar
                        </a>

                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            Fechar
                        </button>
                    </div>

                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    ['toastSucesso', 'toastErro'].forEach(id => {
        const el = document.getElementById(id);

        if (el) {
            new bootstrap.Toast(el, {
                delay: id === 'toastSucesso' ? 4000 : 5000
            }).show();
        }
    });

    const input = document.getElementById('buscarCargo');
    const cards = Array.from(document.querySelectorAll('.cargo-app-card'));
    const contador = document.getElementById('contadorCargos');
    const semResultado = document.getElementById('semResultadoCargos');

    const btnAnterior = document.getElementById('btnAnteriorCargos');
    const btnProxima = document.getElementById('btnProximaCargos');
    const paginaAtualEl = document.getElementById('paginaAtualCargos');
    const totalPaginasEl = document.getElementById('totalPaginasCargos');

    const itensPorPagina = 10;
    let paginaAtual = 1;
    let cardsFiltrados = [...cards];

    function renderizarCargos() {
        const totalPaginas = Math.max(1, Math.ceil(cardsFiltrados.length / itensPorPagina));

        if (paginaAtual > totalPaginas) {
            paginaAtual = totalPaginas;
        }

        const inicio = (paginaAtual - 1) * itensPorPagina;
        const fim = inicio + itensPorPagina;

        cards.forEach(card => card.classList.add('d-none'));

        cardsFiltrados.slice(inicio, fim).forEach(card => {
            card.classList.remove('d-none');
        });

        if (contador) contador.textContent = cardsFiltrados.length;
        if (semResultado) semResultado.classList.toggle('d-none', cardsFiltrados.length > 0);
        if (paginaAtualEl) paginaAtualEl.textContent = paginaAtual;
        if (totalPaginasEl) totalPaginasEl.textContent = totalPaginas;
        if (btnAnterior) btnAnterior.disabled = paginaAtual <= 1;
        if (btnProxima) btnProxima.disabled = paginaAtual >= totalPaginas;
    }

    function filtrarCargos() {
        const termo = input.value.toLowerCase().trim();

        cardsFiltrados = cards.filter(card => {
            const texto = card.dataset.search || '';
            return texto.includes(termo);
        });

        paginaAtual = 1;
        renderizarCargos();
    }

    if (input && cards.length) {
        input.addEventListener('input', filtrarCargos);
    }

    if (btnAnterior) {
        btnAnterior.addEventListener('click', function () {
            if (paginaAtual > 1) {
                paginaAtual--;
                renderizarCargos();
            }
        });
    }

    if (btnProxima) {
        btnProxima.addEventListener('click', function () {
            const totalPaginas = Math.max(1, Math.ceil(cardsFiltrados.length / itensPorPagina));

            if (paginaAtual < totalPaginas) {
                paginaAtual++;
                renderizarCargos();
            }
        });
    }

    renderizarCargos();
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>