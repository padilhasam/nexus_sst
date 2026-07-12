<?php

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Inicia uma transação
     */
    protected function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    /**
     * Confirma a transação
     */
    protected function commit(): void
    {
        $this->db->commit();
    }

    /**
     * Desfaz a transação
     */
    protected function rollback(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    /**
     * Retorna o último ID inserido
     */
    protected function lastInsertId(): int
    {
        return (int)$this->db->lastInsertId();
    }

    /**
     * Executa uma consulta preparada
     */
    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }
}