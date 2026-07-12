<?php
$css = 'empresas.css';
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

        <header class="page-header-empresas mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #084298); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.25);">
                        <i class="fas fa-building text-white" style="font-size: 1.10rem;"></i>
                    </span>

                    Empresas Clientes

                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold fs-6 border border-primary-subtle">
                        <?= count($empresas ?? []) ?>
                    </span>
                </h3>

                <small class="text-muted d-block mt-1">
                    Gestão centralizada de clientes, responsáveis legais e contatos principais.
                </small>
            </div>

            <div class="page-header-actions d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-light border rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fas fa-gauge-high me-1 text-primary"></i>
                    Dashboard
                </a>

                <a href="<?= BASE_URL ?>/empresas/criar" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i>
                    Nova Empresa
                </a>
            </div>
        </header>

        <?php if (!empty($empresas)): ?>

            <section class="empresas-app-view">

                <div class="empresas-app-toolbar bg-white border rounded-4 shadow-sm p-3 mb-3">
                    <div class="input-group empresas-app-search">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>

                        <input
                            type="text"
                            id="buscarEmpresaMobile"
                            class="form-control border-start-0"
                            placeholder="Buscar empresa, CNPJ, cidade, status ou responsável...">
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-3 gap-2">
                        <small class="text-muted">
                            <span id="contadorEmpresasMobile"><?= count($empresas ?? []) ?></span>
                            empresa(s) exibida(s)
                        </small>
                    </div>
                </div>

                <div class="empresas-app-list">
                    <?php foreach ($empresas as $emp): ?>
                        <?php
                        $nomeEmpresa = $emp['nome_fantasia'] ?? $emp['nome'] ?? $emp['razao_social'] ?? 'Sem nome';
                        $empresaAtiva = (int)($emp['ativo'] ?? 1) === 1;

                        $textoBusca = strtolower(
                            ($emp['codigo'] ?? '') . ' ' .
                            ($emp['codigo_externo'] ?? '') . ' ' .
                            ($emp['nome_fantasia'] ?? '') . ' ' .
                            ($emp['razao_social'] ?? '') . ' ' .
                            ($emp['cnpj'] ?? '') . ' ' .
                            ($emp['responsavel'] ?? '') . ' ' .
                            ($emp['cidade'] ?? '') . ' ' .
                            ($emp['estado'] ?? '') . ' ' .
                            ($empresaAtiva ? 'ativa ativo' : 'inativa inativo')
                        );
                        ?>

                        <article class="empresa-app-card" data-search="<?= htmlspecialchars($textoBusca) ?>">

                            <div class="empresa-app-card-main">
                                <div class="empresa-app-icon">
                                    <i class="fas fa-building"></i>
                                </div>

                                <div class="empresa-app-content">
                                    <h5><?= htmlspecialchars($nomeEmpresa) ?></h5>

                                    <?php if (!empty($emp['razao_social']) && $emp['razao_social'] !== $nomeEmpresa): ?>
                                        <p class="empresa-app-razao">
                                            <?= htmlspecialchars($emp['razao_social']) ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="empresa-app-meta">
                                        <span>
                                            <i class="fas fa-hashtag"></i>
                                            <?= !empty($emp['codigo']) ? htmlspecialchars($emp['codigo']) : 'Código interno não informado' ?>
                                        </span>

                                        <span>
                                            <i class="fas fa-id-card"></i>
                                            <?= !empty($emp['cnpj']) ? htmlspecialchars($emp['cnpj']) : 'CNPJ não informado' ?>
                                        </span>

                                        <span>
                                            <i class="fas fa-location-dot"></i>
                                            <?= !empty($emp['cidade']) ? htmlspecialchars($emp['cidade']) : 'Cidade não informada' ?>
                                            <?= !empty($emp['estado']) ? ' / ' . htmlspecialchars($emp['estado']) : '' ?>
                                        </span>

                                        <span>
                                            <i class="fas fa-user"></i>
                                            <?= !empty($emp['responsavel']) ? htmlspecialchars($emp['responsavel']) : 'Responsável não informado' ?>
                                        </span>

                                        <span>
                                            <i class="<?= $empresaAtiva ? 'fas fa-circle-check text-success' : 'fas fa-circle-xmark text-danger' ?>"></i>
                                            <?= $empresaAtiva ? 'Empresa Ativa' : 'Empresa Inativa' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="empresa-app-actions">
                                <button
                                    class="btn btn-outline-secondary rounded-pill px-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEmpresa<?= $emp['id'] ?>">
                                    <i class="fas fa-circle-info me-1"></i>
                                    Ficha
                                </button>

                                <a href="<?= BASE_URL ?>/empresas/editar/<?= $emp['id'] ?>" class="btn btn-outline-primary rounded-pill px-3">
                                    <i class="fas fa-edit me-1"></i>
                                    Editar
                                </a>

                                <a href="<?= BASE_URL ?>/empresas/excluir/<?= $emp['id'] ?>"
                                   class="btn btn-outline-danger rounded-pill px-3"
                                   onclick="return confirm('Deseja realmente excluir esta empresa?')">
                                    <i class="fas fa-trash me-1"></i>
                                    Excluir
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div id="semResultadoEmpresasMobile" class="text-center py-5 text-muted d-none">
                    <i class="fas fa-magnifying-glass fa-2x mb-3 opacity-50"></i>
                    <h6 class="fw-bold text-dark">Nenhuma empresa encontrada</h6>
                    <p class="small mb-0">Tente buscar por nome, CNPJ, cidade, status ou responsável.</p>
                </div>

                <div class="empresas-pagination bg-white border rounded-4 shadow-sm p-3 mt-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <small class="text-muted">
                            Página <span id="paginaAtualEmpresas">1</span> de <span id="totalPaginasEmpresas">1</span>
                        </small>

                        <div class="d-flex gap-2">
                            <button type="button" id="btnAnteriorEmpresas" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-chevron-left me-1"></i>
                                Anterior
                            </button>

                            <button type="button" id="btnProximaEmpresas" class="btn btn-primary rounded-pill px-4">
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
                            <i class="fas fa-building fa-2x"></i>
                        </div>

                        <h5 class="fw-bold text-dark mb-1">Nenhuma empresa cadastrada</h5>

                        <p class="small text-muted mb-3">
                            Clique no botão abaixo para adicionar a primeira empresa.
                        </p>

                        <a href="<?= BASE_URL ?>/empresas/criar" class="btn btn-primary btn-sm rounded-pill px-4 fw-medium shadow-sm">
                            <i class="fas fa-plus-circle me-1"></i>
                            Cadastrar Empresa
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <?php if (!empty($empresas)): foreach ($empresas as $emp): ?>
        <?php $empresaAtiva = (int)($emp['ativo'] ?? 1) === 1; ?>

        <div class="modal fade" id="modalEmpresa<?= $emp['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header bg-light border-bottom py-3">
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-building border p-2 bg-light rounded-3 text-secondary"></i>
                            Ficha da Empresa #<?= $emp['id'] ?>
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">Código Interno</label>
                                <span class="font-monospace text-primary fw-bold">
                                    <?= htmlspecialchars($emp['codigo'] ?? '-') ?>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3 border-bottom pb-2">
                            <label class="text-secondary small fw-semibold d-block">Razão Social</label>
                            <span class="text-dark fw-bold fs-5">
                                <?= htmlspecialchars($emp['razao_social'] ?? $emp['nome'] ?? '-') ?>
                            </span>
                        </div>

                        <div class="mb-3">
                            <label class="text-secondary small fw-semibold d-block">Nome Fantasia</label>
                            <span class="font-monospace text-dark fw-bold bg-light px-2 py-1 rounded border d-inline-block">
                                <?= htmlspecialchars($emp['nome_fantasia'] ?? '-') ?>
                            </span>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">CNPJ</label>
                                <span class="text-dark fw-medium">
                                    <?= htmlspecialchars($emp['cnpj'] ?? '-') ?>
                                </span>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">Insc. Estadual</label>
                                <span class="text-dark fw-medium">
                                    <?= htmlspecialchars($emp['inscricao_estadual'] ?? '-') ?>
                                </span>
                            </div>
                        </div>

                        <div class="row g-3 mb-3 border-top pt-3">
                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">Responsável</label>
                                <span class="text-dark fw-medium">
                                    <?= htmlspecialchars($emp['responsavel'] ?? '-') ?>
                                </span>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">Contato</label>
                                <span class="text-dark fw-medium">
                                    <?= htmlspecialchars($emp['contato_responsavel'] ?? '-') ?>
                                </span>
                            </div>
                        </div>

                        <div class="border-top pt-3">
                            <label class="text-secondary small fw-semibold d-block">Endereço</label>

                            <span class="text-dark">
                                <?= htmlspecialchars($emp['endereco'] ?? '-') ?>
                            </span>

                            <?php if (!empty($emp['cidade']) || !empty($emp['estado'])): ?>
                                <div class="text-muted small mt-1">
                                    <?= htmlspecialchars($emp['cidade'] ?? '') ?>
                                    <?= (!empty($emp['cidade']) && !empty($emp['estado'])) ? ' / ' : '' ?>
                                    <?= htmlspecialchars($emp['estado'] ?? '') ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($emp['cep'])): ?>
                                <div class="text-muted small">
                                    CEP: <?= htmlspecialchars($emp['cep']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="border-top mt-3 pt-3">
                            <label class="text-secondary small fw-semibold d-block mb-1">Status da Empresa</label>

                            <?= $empresaAtiva
                                ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">Empresa Ativa</span>'
                                : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-semibold">Empresa Inativa</span>'
                            ?>
                        </div>

                        <div class="border-top mt-3 pt-3">
                            <label class="text-secondary small fw-semibold d-block">Data de Cadastro</label>

                            <span class="text-muted small">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?= !empty($emp['criado_em']) ? date('d/m/Y H:i', strtotime($emp['criado_em'])) : 'Data não informada' ?>
                            </span>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top">
                        <a href="<?= BASE_URL ?>/empresas/editar/<?= $emp['id'] ?>" class="btn btn-primary rounded-pill px-4">
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

        const input = document.getElementById('buscarEmpresaMobile');
        const cards = Array.from(document.querySelectorAll('.empresa-app-card'));
        const contador = document.getElementById('contadorEmpresasMobile');
        const semResultado = document.getElementById('semResultadoEmpresasMobile');

        const btnAnterior = document.getElementById('btnAnteriorEmpresas');
        const btnProxima = document.getElementById('btnProximaEmpresas');
        const paginaAtualEl = document.getElementById('paginaAtualEmpresas');
        const totalPaginasEl = document.getElementById('totalPaginasEmpresas');

        const itensPorPagina = 10;
        let paginaAtual = 1;
        let cardsFiltrados = [...cards];

        function renderizarEmpresas() {
            const totalPaginas = Math.max(1, Math.ceil(cardsFiltrados.length / itensPorPagina));

            if (paginaAtual > totalPaginas) {
                paginaAtual = totalPaginas;
            }

            const inicio = (paginaAtual - 1) * itensPorPagina;
            const fim = inicio + itensPorPagina;

            cards.forEach(card => {
                card.classList.add('d-none');
            });

            cardsFiltrados.slice(inicio, fim).forEach(card => {
                card.classList.remove('d-none');
            });

            if (contador) {
                contador.textContent = cardsFiltrados.length;
            }

            if (semResultado) {
                semResultado.classList.toggle('d-none', cardsFiltrados.length > 0);
            }

            if (paginaAtualEl) {
                paginaAtualEl.textContent = paginaAtual;
            }

            if (totalPaginasEl) {
                totalPaginasEl.textContent = totalPaginas;
            }

            if (btnAnterior) {
                btnAnterior.disabled = paginaAtual <= 1;
            }

            if (btnProxima) {
                btnProxima.disabled = paginaAtual >= totalPaginas;
            }
        }

        function filtrarEmpresas() {
            const termo = input.value.toLowerCase().trim();

            cardsFiltrados = cards.filter(card => {
                const texto = card.dataset.search || '';
                return texto.includes(termo);
            });

            paginaAtual = 1;
            renderizarEmpresas();
        }

        if (input && cards.length) {
            input.addEventListener('input', filtrarEmpresas);
        }

        if (btnAnterior) {
            btnAnterior.addEventListener('click', function () {
                if (paginaAtual > 1) {
                    paginaAtual--;
                    renderizarEmpresas();
                }
            });
        }

        if (btnProxima) {
            btnProxima.addEventListener('click', function () {
                const totalPaginas = Math.max(1, Math.ceil(cardsFiltrados.length / itensPorPagina));

                if (paginaAtual < totalPaginas) {
                    paginaAtual++;
                    renderizarEmpresas();
                }
            });
        }

        renderizarEmpresas();
    });
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>