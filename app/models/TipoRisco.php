<?php

class TipoRisco extends Database {
    private $db;

    public function __construct() {
        parent::__construct();
        $this->db = $this->getConnection();
    }

    // Listar todos os tipos de agentes (riscos)
    public function listarTodos() {
        $sql = "SELECT id, nome FROM tipos_riscos ORDER BY nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar tipo de agente pelo ID
    public function buscarPorId($id) {
        $sql = "SELECT id, nome FROM tipos_riscos WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
