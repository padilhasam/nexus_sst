<?php

class Database
{
    protected PDO $pdo;
    protected ?string $erro = null;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        $host = (string)$config['host'];
        $dbname = (string)$config['dbname'];
        $usuario = (string)$config['usuario'];
        $senha = (string)$config['senha'];
        $charset = (string)($config['charset'] ?? 'utf8mb4');
        $collation = (string)($config['collation'] ?? 'utf8mb4_unicode_ci');

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        $opcoes = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$collation}",
        ];

        try {
            $this->pdo = new PDO($dsn, $usuario, $senha, $opcoes);
            $this->pdo->exec("SET NAMES {$charset} COLLATE {$collation}");
        } catch (PDOException $e) {
            $this->erro = $e->getMessage();
            die('Erro de conexão com o banco de dados: ' . $this->erro);
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
