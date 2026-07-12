<?php

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../core/Database.php'; // <<< AQUI está o fix

$db = new Database();
$pdo = $db->getConnection();

$senha = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE email = :email");
$stmt->execute([
    ':senha' => $senha,
    ':email' => 'admin@seudominio.com'
]);

echo "Senha atualizada com sucesso!";