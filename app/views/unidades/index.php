<?php
$css = 'unidades.css';
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

        <header class="page-header-unidades mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #084298); border-radius: 8px;">
                        <i class="fas fa-map-location-dot text-white" style="font-size: 1.10rem;"></i>
                    </span>

                    Unidades

                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold fs-6 border border-primary-subtle">
                        <?= count($unidades ?? []) ?>
                    </span>
                </h3>

                <small class="text-muted d-block mt-1">
                    Gestão das unidades, filiais e plantas operacionais vinculadas às empresas.
                </small>
            </div>

            <div class="page-header-actions d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-light border rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fas fa-gauge-high me-1 text-primary"></i>
                    Dashboard
                </a>

                <a href="<?= BASE_URL ?>/unidades/criar" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i>
                    Nova Unidade
                </a>
            </div>
        </header>

        <?php if (!empty($unidades)): ?>

            <section class="unidades-app-view">

                <div class="unidades-app-toolbar bg-white border rounded-4 shadow-sm p-3 mb-3">
                    <div class="input-group unidades-app-search">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>

                        <input
                            type="text"
                            id="buscarUnidadeMobile"
                            class="form-control border-start-0"
                            placeholder="Buscar unidade, empresa, cidade ou UF...">
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-3 gap-2">
                        <small class="text-muted">
                            <span id="contadorUnidadesMobile"><?= count($unidades ?? []) ?></span>
                            unidade(s) exibida(s)
                        </small>
                    </div>
                </div>

                <div class="unidades-app-list">
                    <?php foreach ($unidades as $u): ?>
                        <?php
                        $empresaNome = $u['nome_fantasia'] ?? $u['razao_social'] ?? 'Empresa não informada';
                        $textoBusca = strtolower(
                            ($u['nome'] ?? '') . ' ' .
                            ($empresaNome ?? '') . ' ' .
                            ($u['cidade'] ?? '') . ' ' .
                            ($u['estado'] ?? '') . ' ' .
                            ($u['cnpj'] ?? '') . ' ' .
                            ($u['telefone'] ?? '')
                        );
                        ?>

                        <article class="unidade-app-card" data-search="<?= htmlspecialchars($textoBusca) ?>">

                            <div class="unidade-app-card-main">
                                <div class="unidade-app-icon">
                                    <i class="fas fa-map-location-dot"></i>
                                </div>

                                <div class="unidade-app-content">
                                    <h5><?= htmlspecialchars($u['nome'] ?? 'Unidade sem nome') ?></h5>

                                    <p class="unidade-app-empresa">
                                        <?= htmlspecialchars($empresaNome) ?>
                                    </p>

                                    <div class="unidade-app-meta">
                                        <span>
                                            <i class="fas fa-location-dot"></i>
                                            <?= !empty($u['cidade']) ? htmlspecialchars($u['cidade']) : 'Cidade não informada' ?>
                                            <?= !empty($u['estado']) ? ' / ' . htmlspecialchars($u['estado']) : '' ?>
                                        </span>

                                        <span>
                                            <i class="fas fa-id-card"></i>
                                            <?= !empty($u['cnpj']) ? htmlspecialchars($u['cnpj']) : 'CNPJ não informado' ?>
                                        </span>

                                        <span>
                                            <i class="fas fa-phone"></i>
                                            <?= !empty($u['telefone']) ? htmlspecialchars($u['telefone']) : 'Telefone não informado' ?>
                                        </span>

                                        <span>
                                            <i class="<?= ((int)($u['ativo'] ?? 0) === 1) ? 'fas fa-circle-check text-success' : 'fas fa-circle-xmark text-danger' ?>"></i>
                                            <?= ((int)($u['ativo'] ?? 0) === 1) ? 'Ativa' : 'Inativa' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="unidade-app-actions">
                                <button
                                    class="btn btn-outline-secondary rounded-pill px-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalUnidade<?= $u['id'] ?>">
                                    <i class="fas fa-circle-info me-1"></i>
                                    Ficha
                                </button>

                                <a href="<?= BASE_URL ?>/unidades/editar/<?= $u['id'] ?>" class="btn btn-outline-primary rounded-pill px-3">
                                    <i class="fas fa-edit me-1"></i>
                                    Editar
                                </a>

                                <a href="<?= BASE_URL ?>/unidades/excluir/<?= $u['id'] ?>"
                                   class="btn btn-outline-danger rounded-pill px-3"
                                   onclick="return confirm('Deseja realmente excluir esta unidade?')">
                                    <i class="fas fa-trash me-1"></i>
                                    Excluir
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div id="semResultadoUnidadesMobile" class="text-center py-5 text-muted d-none">
                    <i class="fas fa-magnifying-glass fa-2x mb-3 opacity-50"></i>
                    <h6 class="fw-bold text-dark">Nenhuma unidade encontrada</h6>
                    <p class="small mb-0">Tente buscar por unidade, empresa, cidade ou UF.</p>
                </div>
            </section>

        <?php else: ?>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div class="text-center py-5 text-muted">
                        <div class="d-flex align-items-center justify-content-center rounded-circle mx-auto mb-3 bg-light text-muted opacity-70" style="width: 70px; height: 70px;">
                            <i class="fas fa-map-location-dot fa-2x"></i>
                        </div>

                        <h5 class="fw-bold text-dark mb-1">Nenhuma unidade cadastrada</h5>

                        <p class="small text-muted mb-3">
                            Clique no botão abaixo para adicionar a primeira unidade.
                        </p>

                        <a href="<?= BASE_URL ?>/unidades/criar" class="btn btn-primary btn-sm rounded-pill px-4 fw-medium shadow-sm">
                            <i class="fas fa-plus-circle me-1"></i>
                            Cadastrar Unidade
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <?php if (!empty($unidades)): foreach ($unidades as $u): ?>
        <div class="modal fade" id="modalUnidade<?= $u['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content border-0 shadow-lg rounded-3">

                    <div class="modal-header bg-light border-bottom py-3">
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-map-location-dot border p-2 bg-light rounded-3 text-secondary"></i>
                            Ficha da Unidade #<?= $u['id'] ?>
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="mb-3 border-bottom pb-2">
                            <label class="text-secondary small fw-semibold d-block">Nome da Unidade</label>
                            <span class="text-dark fw-bold fs-5"><?= htmlspecialchars($u['nome'] ?? '-') ?></span>
                        </div>

                        <div class="mb-3 border-bottom pb-2">
                            <label class="text-secondary small fw-semibold d-block">Empresa Vinculada</label>
                            <span class="text-dark fw-medium">
                                <?= htmlspecialchars($u['nome_fantasia'] ?? $u['razao_social'] ?? '-') ?>
                            </span>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">Cidade</label>
                                <span class="text-dark fw-medium"><?= htmlspecialchars($u['cidade'] ?? '-') ?></span>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="text-secondary small fw-semibold d-block">Estado</label>
                                <span class="text-dark fw-medium"><?= htmlspecialchars($u['estado'] ?? '-') ?></span>
                            </div>
                        </div>

                        <div class="mb-3 border-bottom pb-3">
                            <label class="text-secondary small fw-semibold d-block mb-1">Status Operacional</label>
                            <?= ((int)($u['ativo'] ?? 0) === 1)
                                ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">Unidade Ativa</span>'
                                : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-semibold">Unidade Inativa</span>'
                            ?>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-3">
                        <a href="<?= BASE_URL ?>/unidades/editar/<?= $u['id'] ?>" class="btn btn-primary rounded-pill px-4">
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

    const inputMobile = document.getElementById('buscarUnidadeMobile');
    const cardsMobile = document.querySelectorAll('.unidade-app-card');
    const contadorMobile = document.getElementById('contadorUnidadesMobile');
    const semResultado = document.getElementById('semResultadoUnidadesMobile');

    if (inputMobile && cardsMobile.length) {
        inputMobile.addEventListener('input', function () {
            const termo = this.value.toLowerCase().trim();
            let totalVisiveis = 0;

            cardsMobile.forEach(card => {
                const texto = card.dataset.search || '';
                const visivel = texto.includes(termo);

                card.classList.toggle('d-none', !visivel);

                if (visivel) totalVisiveis++;
            });

            if (contadorMobile) contadorMobile.textContent = totalVisiveis;

            if (semResultado) semResultado.classList.toggle('d-none', totalVisiveis > 0);
        });
    }
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>