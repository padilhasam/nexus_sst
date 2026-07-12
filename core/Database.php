<?php

class Database {
    protected $pdo;

    public function __construct() {
        $config = require __DIR__ . '/../config/database.php';

        $host     = $config['host'];
        $dbname   = $config['dbname'];
        $usuario  = $config['usuario'];
        $senha    = $config['senha'];
        $charset  = $config['charset'];

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

        $opcoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT         => true,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $usuario, $senha, $opcoes);
        } catch (PDOException $e) {
            $this->erro = $e->getMessage();
            die("Erro de conexão com o banco de dados: " . $this->erro);
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}