<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Visita.php';

if ($_GET['action'] === 'atualizar_visita') {
    $id = $_POST['id'] ?? null;
    $novaData = $_POST['nova_data'] ?? null;

    if ($id && $novaData) {
        $visita = new Visita();
        // Certifique-se de que este método existe no seu model Visita
        if ($visita->atualizarData($id, $novaData)) {
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar']);
}