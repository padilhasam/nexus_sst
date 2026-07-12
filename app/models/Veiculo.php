<?php

class Veiculo extends Database
{
    private PDO $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->getConnection();
    }

    /* =========================================================================
       LISTAR TODOS (Utilizado na listagem do painel de administração)
    ========================================================================= */
    public function listarTodos()
    {
        $sql = "SELECT *
                FROM veiculos
                ORDER BY modelo ASC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================================================
       BUSCAR POR ID (Utilizado para carregar os dados na tela de edição)
    ========================================================================= */
    public function buscarPorId(int $id)
    {
        $sql = "SELECT *
                FROM veiculos
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================================================================
       CADASTRAR VEÍCULO
    ========================================================================= */
    public function cadastrar(array $dados)
    {
        $sql = "INSERT INTO veiculos (
                    modelo,
                    placa,
                    cor,
                    ativo
                ) VALUES (
                    :modelo,
                    :placa,
                    :cor,
                    :ativo
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':modelo' => $dados['modelo'],
            ':placa'  => $dados['placa'],
            ':cor'    => $dados['cor'] ?? null,
            ':ativo'  => $dados['ativo'] ?? 1
        ]);
    }

    /* =========================================================================
       ATUALIZAR VEÍCULO
    ========================================================================= */
    public function atualizar(int $id, array $dados)
    {
        $sql = "UPDATE veiculos SET
                    modelo = :modelo,
                    placa = :placa,
                    cor = :cor,
                    ativo = :ativo
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':modelo' => $dados['modelo'],
            ':placa'  => $dados['placa'],
            ':cor'    => $dados['cor'] ?? null,
            ':ativo'  => $dados['ativo'] ?? 1,
            ':id'     => $id
        ]);
    }

    /* =========================================================================
       EXCLUIR VEÍCULO
    ========================================================================= */
    public function excluir(int $id)
    {
        $sql = "DELETE FROM veiculos WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    /* =========================================================================
       LISTAR ATIVOS (Essencial para carregar o <select> no agendamento da visita)
    ========================================================================= */
    public function listarAtivos()
    {
        $sql = "SELECT *
                FROM veiculos
                WHERE ativo = 1
                ORDER BY modelo ASC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}