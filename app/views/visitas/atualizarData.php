<?php
// app/views/visitas/atualizarData.php
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__, 3) . '/config/database.php';

header('Content-Type: application/json');

$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$novaData = filter_input(INPUT_POST, 'nova_data', FILTER_SANITIZE_SPECIAL_CHARS);

if ($id && $novaData) {
    try {
        $stmt = $pdo->prepare("UPDATE visitas SET data_visita = :data WHERE id = :id");
        if ($stmt->execute([':data' => $novaData, ':id' => $id])) {
            echo json_encode(['status' => 'sucesso', 'mensagem' => 'Data atualizada com sucesso!']);
            exit;
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no banco de dados.']);
        exit;
    }
}

http_response_code(400); // Bad Request
echo json_encode(['status' => 'erro', 'mensagem' => 'Dados inválidos.']);