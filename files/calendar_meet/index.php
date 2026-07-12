<?php 
require_once dirname(__DIR__) . '/templates/header.php'; 

// 1. Processamento PHP
$sucesso = $_SESSION['sucesso'] ?? null;
$erro = $_SESSION['erro'] ?? null;
unset($_SESSION['sucesso'], $_SESSION['erro']);

$eventos = [];
if (isset($visitas) && is_array($visitas)) {
    foreach ($visitas as $v) {
        // Passamos dados brutos. As cores e o design serão tratados pelo JS.
        $eventos[] = [
            'id'    => $v['id'],
            'title' => $v['empresa_nome'],
            'start' => $v['data_visita'] . 'T' . ($v['hora_visita'] ?? '00:00:00'),
            'url'   => BASE_URL . '/visitas/visualizar/' . $v['id'],
            'extendedProps' => [
                'status'  => $v['status'] ?? 'ABERTA',
                'veiculo' => $v['veiculo_modelo'] ?? 'A pé'
            ]
        ];
    }
}
?>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<link rel="stylesheet" href="<?= BASE_URL ?>/css/calendario-visitas.css">

<script>
    window.eventosData = <?= json_encode($eventos) ?>;
    window.baseUrl = '<?= BASE_URL ?>';
</script>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
    <?php if ($sucesso): ?>
        <div id="toastSucesso" class="toast text-bg-success border-0 shadow-lg" role="alert">
            <div class="toast-body"><i class="fas fa-circle-check me-2"></i> <?= htmlspecialchars($sucesso) ?></div>
        </div>
    <?php endif; ?>
</div>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-calendar-check text-primary"></i> Agenda de Visitas</h3>
        <a href="<?= BASE_URL ?>/visitas/criar" class="btn btn-primary rounded-pill shadow-sm">
            <i class="fas fa-calendar-plus me-2"></i> Novo Agendamento
        </a>
    </div>

    <div class="card shadow-sm border-0 p-3">
        <div id="calendar"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/js/calendario-visitas.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa Toast
        const toastEl = document.getElementById('toastSucesso');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    });
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>