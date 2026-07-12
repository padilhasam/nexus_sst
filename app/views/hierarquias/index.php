<?php
$css = 'hierarquias.css';
require_once dirname(__DIR__) . '/templates/header.php';

$empresasEstruturadas = $empresasEstruturadas ?? [];
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

        <header class="page-header-hierarquias mb-4 px-4 py-3 bg-white border rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="m-0 fw-bold text-dark d-flex align-items-center gap-3" style="font-size: 1.5rem;">
                    <span class="icon-container d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, #0d6efd, #084298); border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.25);">
                        <i class="fas fa-diagram-project text-white"></i>
                    </span>

                    Estrutura Organizacional

                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold fs-6 border border-primary-subtle">
                        <?= count($empresasEstruturadas) ?>
                    </span>
                </h3>

                <small class="text-muted d-block mt-1">
                    Controle das empresas com suas unidades, setores, cargos e hierarquias.
                </small>
            </div>

            <div class="page-header-actions d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>/hierarquias/importar" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
                    <i class="fas fa-file-import me-1"></i>
                    Importar Planilha
                </a>

                <a href="<?= BASE_URL ?>/hierarquias/criar" class="btn btn-primary btn-sm rounded-pill px-3 fw-medium shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i>
                    Criar Manualmente
                </a>
            </div>
        </header>

        <?php if (!empty($empresasEstruturadas)): ?>

            <section class="estrutura-toolbar bg-white border rounded-4 shadow-sm p-3 mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>

                    <input
                        type="text"
                        id="buscarEmpresaEstrutura"
                        class="form-control border-start-0"
                        placeholder="Buscar empresa...">
                </div>

                <small class="text-muted d-block mt-2">
                    Clique na empresa para visualizar a estrutura em árvore.
                </small>
            </section>

            <section class="estrutura-empresas-grid">
                <?php foreach ($empresasEstruturadas as $empresa): ?>
                    <?php
                    $nomeEmpresa = $empresa['empresa_nome'] ?? 'Empresa não informada';

                    $textoBusca = strtolower($nomeEmpresa);
                    ?>

                    <a
                        href="<?= BASE_URL ?>/hierarquias/estrutura/<?= $empresa['id'] ?>"
                        class="estrutura-empresa-card"
                        data-search="<?= htmlspecialchars($textoBusca) ?>">

                        <div class="estrutura-empresa-top">
                            <div class="estrutura-empresa-icon">
                                <i class="fas fa-building"></i>
                            </div>

                            <div>
                                <h5><?= htmlspecialchars($nomeEmpresa) ?></h5>
                                <span><?= $empresa['total_hierarquias'] ?? 0 ?> hierarquia(s) montada(s)</span>
                            </div>
                        </div>

                        <div class="estrutura-empresa-metricas">
                            <div>
                                <strong><?= $empresa['total_unidades'] ?? 0 ?></strong>
                                <span>Unidades</span>
                            </div>

                            <div>
                                <strong><?= $empresa['total_setores'] ?? 0 ?></strong>
                                <span>Setores</span>
                            </div>

                            <div>
                                <strong><?= $empresa['total_cargos'] ?? 0 ?></strong>
                                <span>Cargos</span>
                            </div>
                        </div>

                        <div class="estrutura-empresa-footer">
                            <span>Visualizar estrutura</span>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </section>

            <div id="semResultadoEstrutura" class="text-center py-5 text-muted d-none">
                <i class="fas fa-magnifying-glass fa-2x mb-3 opacity-50"></i>
                <h6 class="fw-bold text-dark">Nenhuma empresa encontrada</h6>
                <p class="small mb-0">Tente buscar por outro nome de empresa.</p>
            </div>

        <?php else: ?>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div class="text-center py-5 text-muted">
                        <div class="d-flex align-items-center justify-content-center rounded-circle mx-auto mb-3 bg-light text-muted opacity-70" style="width: 74px; height: 74px;">
                            <i class="fas fa-diagram-project fa-2x"></i>
                        </div>

                        <h5 class="fw-bold text-dark mb-1">Nenhuma estrutura cadastrada</h5>

                        <p class="small text-muted mb-3">
                            Crie manualmente ou importe uma planilha para montar a estrutura automaticamente.
                        </p>

                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <a href="<?= BASE_URL ?>/hierarquias/importar" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-medium">
                                <i class="fas fa-file-import me-1"></i>
                                Importar Planilha
                            </a>

                            <a href="<?= BASE_URL ?>/hierarquias/criar" class="btn btn-primary btn-sm rounded-pill px-4 fw-medium shadow-sm">
                                <i class="fas fa-plus-circle me-1"></i>
                                Criar Manualmente
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>
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

    const input = document.getElementById('buscarEmpresaEstrutura');
    const cards = document.querySelectorAll('.estrutura-empresa-card');
    const vazio = document.getElementById('semResultadoEstrutura');

    if (input && cards.length) {
        input.addEventListener('input', function () {
            const termo = this.value.toLowerCase().trim();
            let total = 0;

            cards.forEach(card => {
                const texto = card.dataset.search || '';
                const visivel = texto.includes(termo);

                card.classList.toggle('d-none', !visivel);

                if (visivel) total++;
            });

            if (vazio) {
                vazio.classList.toggle('d-none', total > 0);
            }
        });
    }
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>