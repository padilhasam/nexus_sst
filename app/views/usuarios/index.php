<?php
$css = 'usuarios.css';
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

        <header class="page-header-usuarios mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #084298); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.25);">
                        <i class="fas fa-users text-white" style="font-size: 1.10rem;"></i>
                    </span>

                    Usuários

                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold fs-6 border border-primary-subtle">
                        <?= count($usuarios ?? []) ?>
                    </span>
                </h3>

                <small class="text-muted d-block mt-1">
                    Gerenciamento de credenciais, perfis corporativos e controle de acessos ativos do sistema.
                </small>
            </div>

            <div class="page-header-actions d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-light border rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fas fa-gauge-high me-1 text-primary"></i>
                    Dashboard
                </a>

                <a href="<?= BASE_URL ?>/usuarios/criar" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i>
                    Novo Usuário
                </a>
            </div>
        </header>

        <?php if (!empty($usuarios)): ?>

            <section class="usuarios-app-view">

                <div class="usuarios-app-toolbar bg-white border rounded-4 shadow-sm p-3 mb-3">
                    <div class="input-group usuarios-app-search">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>

                        <input
                            type="text"
                            id="buscarUsuario"
                            class="form-control border-start-0"
                            placeholder="Buscar usuário, e-mail, nível ou status...">
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-3 gap-2">
                        <small class="text-muted">
                            <span id="contadorUsuarios"><?= count($usuarios ?? []) ?></span>
                            usuário(s) exibido(s)
                        </small>
                    </div>
                </div>

                <div class="usuarios-app-list">
                    <?php foreach ($usuarios as $u): ?>
                        <?php
                        $usuarioAtivo = (int)($u['ativo'] ?? 0) === 1;

                        $textoBusca = strtolower(
                            ($u['nome'] ?? '') . ' ' .
                            ($u['email'] ?? '') . ' ' .
                            ($u['tipo'] ?? '') . ' ' .
                            ($usuarioAtivo ? 'ativo ativa' : 'inativo inativa')
                        );
                        ?>

                        <article class="usuario-app-card" data-search="<?= htmlspecialchars($textoBusca) ?>">

                            <div class="usuario-app-card-main">
                                <div class="usuario-app-icon">
                                    <i class="fas fa-user"></i>
                                </div>

                                <div class="usuario-app-content">
                                    <h5><?= htmlspecialchars($u['nome'] ?? 'Usuário sem nome') ?></h5>

                                    <p class="usuario-app-subtitle">
                                        <?= htmlspecialchars($u['email'] ?? 'E-mail não informado') ?>
                                    </p>

                                    <div class="usuario-app-meta">
                                        <span>
                                            <i class="fas fa-envelope"></i>
                                            <?= !empty($u['email']) ? htmlspecialchars($u['email']) : 'E-mail não informado' ?>
                                        </span>

                                        <span>
                                            <i class="fas fa-user-shield"></i>
                                            <?= !empty($u['tipo']) ? htmlspecialchars($u['tipo']) : 'Nível não informado' ?>
                                        </span>

                                        <span>
                                            <i class="<?= $usuarioAtivo ? 'fas fa-circle-check text-success' : 'fas fa-circle-xmark text-danger' ?>"></i>
                                            <?= $usuarioAtivo ? 'Usuário Ativo' : 'Usuário Inativo' ?>
                                        </span>

                                        <span>
                                            <i class="far fa-calendar-alt"></i>
                                            Último acesso:
                                            <?= !empty($u['ultimo_acesso']) ? date('d/m/Y H:i', strtotime($u['ultimo_acesso'])) : 'Nunca' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="usuario-app-actions">
                                <button
                                    class="btn btn-outline-secondary rounded-pill px-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalUsuario<?= $u['id'] ?>">
                                    <i class="fas fa-circle-info me-1"></i>
                                    Ficha
                                </button>

                                <a href="<?= BASE_URL ?>/usuarios/editar/<?= $u['id'] ?>" class="btn btn-outline-primary rounded-pill px-3">
                                    <i class="fas fa-edit me-1"></i>
                                    Editar
                                </a>

                                <a href="<?= BASE_URL ?>/usuarios/excluir/<?= $u['id'] ?>"
                                   class="btn btn-outline-danger rounded-pill px-3"
                                   onclick="return confirm('Excluir este usuário?')">
                                    <i class="fas fa-trash me-1"></i>
                                    Excluir
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div id="semResultadoUsuarios" class="text-center py-5 text-muted d-none">
                    <i class="fas fa-magnifying-glass fa-2x mb-3 opacity-50"></i>
                    <h6 class="fw-bold text-dark">Nenhum usuário encontrado</h6>
                    <p class="small mb-0">Tente buscar por nome, e-mail, nível ou status.</p>
                </div>

                <div class="usuarios-pagination bg-white border rounded-4 shadow-sm p-3 mt-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <small class="text-muted">
                            Página <span id="paginaAtualUsuarios">1</span> de <span id="totalPaginasUsuarios">1</span>
                        </small>

                        <div class="d-flex gap-2">
                            <button type="button" id="btnAnteriorUsuarios" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-chevron-left me-1"></i>
                                Anterior
                            </button>

                            <button type="button" id="btnProximaUsuarios" class="btn btn-primary rounded-pill px-4">
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
                            <i class="fas fa-users fa-2x"></i>
                        </div>

                        <h5 class="fw-bold text-dark mb-1">Nenhum usuário encontrado</h5>

                        <p class="small text-muted mb-3">
                            Clique no botão abaixo para adicionar um novo usuário.
                        </p>

                        <a href="<?= BASE_URL ?>/usuarios/criar" class="btn btn-primary btn-sm rounded-pill px-4 fw-medium shadow-sm">
                            <i class="fas fa-plus-circle me-1"></i>
                            Cadastrar Usuário
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <?php if (!empty($usuarios)): foreach ($usuarios as $u): ?>
        <?php $usuarioAtivo = (int)($u['ativo'] ?? 0) === 1; ?>

        <div class="modal fade" id="modalUsuario<?= $u['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content border-0 shadow-lg rounded-3">

                    <div class="modal-header bg-light border-bottom py-3">
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-user border p-2 bg-light rounded-3 text-secondary"></i>
                            Ficha do Usuário #<?= $u['id'] ?>
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="mb-3 border-bottom pb-2">
                            <label class="text-secondary small fw-semibold d-block">Nome Completo</label>
                            <span class="text-dark fw-bold fs-5">
                                <?= htmlspecialchars($u['nome'] ?? '-') ?>
                            </span>
                        </div>

                        <div class="mb-3 border-bottom pb-2">
                            <label class="text-secondary small fw-semibold d-block">E-mail</label>
                            <span class="text-dark fw-medium">
                                <?= htmlspecialchars($u['email'] ?? '-') ?>
                            </span>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">Nível</label>
                                <span class="text-dark fw-bold">
                                    <?= htmlspecialchars($u['tipo'] ?? '-') ?>
                                </span>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block mb-1">Status</label>
                                <?= $usuarioAtivo
                                    ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">Usuário Ativo</span>'
                                    : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-semibold">Usuário Inativo</span>'
                                ?>
                            </div>
                        </div>

                        <div class="border-top mt-3 pt-3">
                            <label class="text-secondary small fw-semibold d-block">Último acesso</label>

                            <span class="text-muted small">
                                <i class="far fa-calendar-alt me-1"></i>
                                <?= !empty($u['ultimo_acesso']) ? date('d/m/Y H:i', strtotime($u['ultimo_acesso'])) : 'Nunca' ?>
                            </span>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top py-3">
                        <a href="<?= BASE_URL ?>/usuarios/editar/<?= $u['id'] ?>" class="btn btn-primary rounded-pill px-4">
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

    const input = document.getElementById('buscarUsuario');
    const cards = Array.from(document.querySelectorAll('.usuario-app-card'));
    const contador = document.getElementById('contadorUsuarios');
    const semResultado = document.getElementById('semResultadoUsuarios');

    const btnAnterior = document.getElementById('btnAnteriorUsuarios');
    const btnProxima = document.getElementById('btnProximaUsuarios');
    const paginaAtualEl = document.getElementById('paginaAtualUsuarios');
    const totalPaginasEl = document.getElementById('totalPaginasUsuarios');

    const itensPorPagina = 10;
    let paginaAtual = 1;
    let cardsFiltrados = [...cards];

    function renderizarUsuarios() {
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

    function filtrarUsuarios() {
        const termo = input.value.toLowerCase().trim();

        cardsFiltrados = cards.filter(card => {
            const texto = card.dataset.search || '';
            return texto.includes(termo);
        });

        paginaAtual = 1;
        renderizarUsuarios();
    }

    if (input && cards.length) {
        input.addEventListener('input', filtrarUsuarios);
    }

    if (btnAnterior) {
        btnAnterior.addEventListener('click', function () {
            if (paginaAtual > 1) {
                paginaAtual--;
                renderizarUsuarios();
            }
        });
    }

    if (btnProxima) {
        btnProxima.addEventListener('click', function () {
            const totalPaginas = Math.max(1, Math.ceil(cardsFiltrados.length / itensPorPagina));

            if (paginaAtual < totalPaginas) {
                paginaAtual++;
                renderizarUsuarios();
            }
        });
    }

    renderizarUsuarios();
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>