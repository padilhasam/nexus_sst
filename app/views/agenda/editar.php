<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>

<div class="page-head d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1">Editar Agendamento</h2>
        <p class="text-muted mb-0">Toda alteração exige motivo e gera histórico.</p>
    </div>
    <a href="<?= BASE_URL ?>/agenda" class="btn btn-outline-secondary rounded-pill px-4"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
</div>

<div class="panel">
    <form action="<?= BASE_URL ?>/agenda/atualizar/<?= $agendamento['id'] ?>" method="POST" class="row g-3">
        <?php require __DIR__ . '/partials/form.php'; ?>
        <div class="col-12">
            <label class="form-label small fw-semibold text-danger">Motivo da alteração *</label>
            <textarea name="motivo" rows="2" class="form-control rounded-3" required placeholder="Descreva o motivo da alteração"></textarea>
        </div>
        <div class="col-12 d-flex justify-content-between gap-2 mt-4 flex-wrap">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-warning rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalCancelar">Cancelar Agenda</button>
                <button type="button" class="btn btn-outline-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalExcluir">Excluir</button>
            </div>
            <button class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-check me-2"></i>Atualizar</button>
        </div>
    </form>
</div>

<div class="modal fade" id="modalCancelar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="POST" action="<?= BASE_URL ?>/agenda/cancelar/<?= $agendamento['id'] ?>">
    <div class="modal-header"><h5 class="modal-title">Cancelar agendamento</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><label class="form-label">Motivo *</label><textarea name="motivo" class="form-control" required></textarea></div>
    <div class="modal-footer"><button class="btn btn-warning rounded-pill px-4">Confirmar cancelamento</button></div>
  </form></div>
</div>

<div class="modal fade" id="modalExcluir" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="POST" action="<?= BASE_URL ?>/agenda/excluir/<?= $agendamento['id'] ?>">
    <div class="modal-header"><h5 class="modal-title">Excluir agendamento</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><label class="form-label">Motivo *</label><textarea name="motivo" class="form-control" required></textarea></div>
    <div class="modal-footer"><button class="btn btn-danger rounded-pill px-4">Confirmar exclusão</button></div>
  </form></div>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
