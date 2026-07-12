<?php
$css = 'listar_riscos.css';
require_once dirname(__DIR__) . '/templates/header.php';
?>

<main class="content flex-grow-1 pt-3 px-4 pb-4 bg-light-subtle">
    <div class="container-fluid px-2 px-lg-4 mb-4">

        <?php
        $sucesso = $_SESSION['sucesso'] ?? null;
        $erro = $_SESSION['erro'] ?? null;
        unset($_SESSION['sucesso'], $_SESSION['erro']);

        $totalRiscos = count($riscos ?? []);
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

        <header class="page-header-riscos mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center"
                          style="width: 38px; height: 38px; background:linear-gradient(135deg,<?= $cor ?>,<?= $cor ?>dd); border-radius: 8px;">
                        <i class="fas <?= htmlspecialchars($icone ?? 'fa-triangle-exclamation') ?> text-white" style="font-size: 1.10rem;"></i>
                    </span>

                    <?= htmlspecialchars($titulo ?? 'Riscos') ?>

                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold fs-6 border border-primary-subtle">
                        <?= $totalRiscos ?>
                    </span>
                </h3>

                <small class="text-muted d-block mt-1">
                    Gerencie os riscos cadastrados para esta categoria.
                </small>
            </div>

            <div class="page-header-actions d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>/riscos" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>

                <a href="<?= BASE_URL ?>/riscos/criar/<?= htmlspecialchars($categoria ?? '') ?>" class="btn btn-primary btn-sm rounded-pill px-3 fw-medium shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i> Novo Risco
                </a>
            </div>
        </header>

        <div class="card border-0 shadow-sm rounded-3 mb-4 riscos-app-toolbar">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-lg-8 riscos-app-search">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-dark-subtle text-muted">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text"
                                   id="riscoSearch"
                                   class="form-control border-dark-subtle"
                                   placeholder="Pesquisar por nome, código, descrição ou metodologia...">
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 text-lg-end">
                        <small class="text-muted">
                            Exibindo <strong id="riscoCount"><?= $totalRiscos ?></strong> risco(s)
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($riscos)): ?>

            <div class="riscos-app-list" id="riscosList">

                <?php foreach ($riscos as $risco): ?>
                    <?php
                    $search = strtolower(
                        ($risco['codigo'] ?? '') . ' ' .
                        ($risco['codigo_externo'] ?? '') . ' ' .
                        ($risco['nome'] ?? '') . ' ' .
                        ($risco['descricao'] ?? '') . ' ' .
                        ($risco['metodologia'] ?? '') . ' ' .
                        ($risco['normas_aplicaveis'] ?? '')
                    );
                    ?>

                    <article class="risco-app-card" data-search="<?= htmlspecialchars($search) ?>">

                        <div class="risco-app-card-main">
                            <div class="risco-app-icon">
                                <i class="fas <?= htmlspecialchars($icone ?? 'fa-triangle-exclamation') ?>"></i>
                            </div>

                            <div class="risco-app-content">
                                <h5><?= htmlspecialchars($risco['nome'] ?? '-') ?></h5>

                                <p class="risco-app-subtitle">
                                    <?= !empty($risco['descricao']) ? htmlspecialchars($risco['descricao']) : 'Sem descrição informada.' ?>
                                </p>

                                <div class="risco-app-meta">
                                    <span>
                                        <i class="fas fa-hashtag"></i>
                                        <?= htmlspecialchars($risco['codigo'] ?? 'Sem código') ?>
                                    </span>

                                    <span>
                                        <i class="fas fa-clipboard-check"></i>
                                        <?= htmlspecialchars($risco['tipo_avaliacao'] ?? 'Qualitativo') ?>
                                    </span>

                                    <span>
                                        <i class="fas fa-ruler-combined"></i>
                                        <?= htmlspecialchars($risco['unidade_medida'] ?? 'Sem unidade') ?>
                                    </span>

                                    <span>
                                        <i class="fas fa-vials"></i>
                                        <?= !empty($risco['exige_quantificacao']) ? 'Quantitativo' : 'Qualitativo' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="risco-app-actions">
                            <a href="<?= BASE_URL ?>/riscos/editar/<?= (int)$risco['id'] ?>"
                               class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="fas fa-edit"></i>
                                Editar
                            </a>

                            <a href="<?= BASE_URL ?>/riscos/excluir/<?= (int)$risco['id'] ?>"
                               class="btn btn-sm btn-outline-danger rounded-pill"
                               onclick="return confirm('Deseja desativar este risco?')">
                                <i class="fas fa-trash"></i>
                                Desativar
                            </a>
                        </div>

                    </article>
                <?php endforeach; ?>

            </div>

            <div class="card border-0 shadow-sm rounded-3 mt-4 riscos-pagination">
                <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="prevPage">
                        <i class="fas fa-chevron-left me-1"></i> Anterior
                    </button>

                    <small class="text-muted fw-semibold" id="pageInfo">
                        Página 1
                    </small>

                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="nextPage">
                        Próxima <i class="fas fa-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>

        <?php else: ?>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body text-center py-5 text-muted">
                    <div class="icon-container d-flex align-items-center justify-content-center rounded-circle mx-auto mb-3 bg-light text-muted opacity-70"
                         style="width: 70px; height: 70px;">
                        <i class="fas <?= htmlspecialchars($icone ?? 'fa-triangle-exclamation') ?> fa-2x"></i>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">Nenhum risco cadastrado</h5>

                    <p class="mb-3">
                        Cadastre o primeiro risco desta categoria para utilizar nos levantamentos.
                    </p>

                    <a href="<?= BASE_URL ?>/riscos/criar/<?= htmlspecialchars($categoria ?? '') ?>" class="btn btn-primary btn-sm rounded-pill px-4 fw-medium shadow-sm">
                        <i class="fas fa-plus-circle me-1"></i> Cadastrar Risco
                    </a>
                </div>
            </div>

        <?php endif; ?>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toast').forEach(toast => {
        new bootstrap.Toast(toast, { delay: 4000 }).show();
    });

    const searchInput = document.getElementById('riscoSearch');
    const cards = Array.from(document.querySelectorAll('.risco-app-card'));
    const riscoCount = document.getElementById('riscoCount');
    const prevPage = document.getElementById('prevPage');
    const nextPage = document.getElementById('nextPage');
    const pageInfo = document.getElementById('pageInfo');

    const perPage = 9;
    let currentPage = 1;
    let filteredCards = cards;

    function renderCards() {
        const totalPages = Math.max(1, Math.ceil(filteredCards.length / perPage));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        cards.forEach(card => card.style.display = 'none');

        filteredCards
            .slice((currentPage - 1) * perPage, currentPage * perPage)
            .forEach(card => card.style.display = '');

        if (riscoCount) {
            riscoCount.textContent = filteredCards.length;
        }

        if (pageInfo) {
            pageInfo.textContent = `Página ${currentPage} de ${totalPages}`;
        }

        if (prevPage) {
            prevPage.disabled = currentPage <= 1;
        }

        if (nextPage) {
            nextPage.disabled = currentPage >= totalPages;
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const termo = this.value.toLowerCase().trim();

            filteredCards = cards.filter(card => {
                return card.dataset.search.includes(termo);
            });

            currentPage = 1;
            renderCards();
        });
    }

    if (prevPage) {
        prevPage.addEventListener('click', function () {
            currentPage--;
            renderCards();
        });
    }

    if (nextPage) {
        nextPage.addEventListener('click', function () {
            currentPage++;
            renderCards();
        });
    }

    renderCards();
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>