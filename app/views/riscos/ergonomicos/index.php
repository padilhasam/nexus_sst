<?php require_once dirname(__DIR__, 2) . '/templates/header.php'; ?>

<main class="content flex-grow-1 p-4">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

    <div>
        <h3 class="mb-0 fw-bold">
            <i class="fas fa-chair text-info me-2"></i>
            Riscos Ergonômicos
        </h3>
        <p class="text-muted mb-0">
            Gerenciamento de riscos ergonômicos cadastrados no sistema
        </p>
    </div>

    <a href="<?= BASE_URL ?>/riscos/novo/ergonomico"
       class="btn btn-primary shadow-sm px-4">
        <i class="fas fa-plus-circle me-2"></i>
        Novo Risco
    </a>

</div>

<div class="card border-0 shadow-sm">

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th style="width:70px;">ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th class="text-center" style="width:160px;">Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (!empty($riscos)): ?>

                    <?php foreach ($riscos as $risco): ?>

                        <tr>
                            <td class="text-muted fw-semibold">#<?= $risco['id'] ?></td>

                            <td class="fw-semibold">
                                <?= htmlspecialchars($risco['nome']) ?>
                            </td>

                            <td class="text-muted">
                                <?= htmlspecialchars($risco['descricao'] ?? '-') ?>
                            </td>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">

                                    <a href="<?= BASE_URL ?>/riscos/editar/<?= $risco['id'] ?>"
                                       class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="<?= BASE_URL ?>/riscos/excluir/<?= $risco['id'] ?>"
                                       class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                       onclick="return confirm('Deseja excluir este risco?')">
                                        <i class="fas fa-trash"></i>
                                    </a>

                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-chair fa-3x mb-3 opacity-50"></i>
                            <div>Nenhum risco ergonômico cadastrado</div>
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>
    </div>
</div>

</main>

<?php require_once dirname(__DIR__, 2) . '/templates/footer.php'; ?>