<?php
    // public/teste-conexao.php

    require_once '../core/Database.php';

    $db = new Database();
    $conn = $db->getConnection();

    if ($conn) {
        echo "Conexão bem-sucedida!";
    } else {
        echo "Erro na conexão.";
    }

?>