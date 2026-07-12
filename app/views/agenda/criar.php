<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<div class="panel">
    <form action="<?= BASE_URL ?>/agenda/salvar" method="POST" class="row g-3">
        <?php require __DIR__ . '/partials/form.php'; ?>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
