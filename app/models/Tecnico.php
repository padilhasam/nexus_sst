<?php

class Tecnico extends Database
{
    private PDO $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->getConnection();
    }

    /* =========================
       LISTAR TODOS
    ========================= */
    public function listarTodos()
    {
        $sql = "SELECT *
                FROM tecnicos
                ORDER BY nome ASC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       BUSCAR POR ID
    ========================= */
    public function buscarPorId(int $id)
    {
        $sql = "SELECT *
                FROM tecnicos
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       CADASTRAR
    ========================= */
    public function cadastrar(array $dados)
    {
        $sql = "INSERT INTO tecnicos (
                    nome,
                    registro_profissional,
                    conselho,
                    uf,
                    cpf,
                    telefone,
                    email,
                    assinatura,
                    ativo
                ) VALUES (
                    :nome,
                    :registro_profissional,
                    :conselho,
                    :uf,
                    :cpf,
                    :telefone,
                    :email,
                    :assinatura,
                    :ativo
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':registro_profissional' => $dados['registro_profissional'] ?? null,
            ':conselho' => $dados['conselho'] ?? null,
            ':uf' => $dados['uf'] ?? null,
            ':cpf' => $dados['cpf'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':email' => $dados['email'] ?? null,
            ':assinatura' => $dados['assinatura'] ?? null,
            ':ativo' => $dados['ativo'] ?? 1
        ]);
    }

    /* =========================
       ATUALIZAR
    ========================= */
    public function atualizar(int $id, array $dados)
    {
        $sql = "UPDATE tecnicos SET
                    nome = :nome,
                    registro_profissional = :registro_profissional,
                    conselho = :conselho,
                    uf = :uf,
                    cpf = :cpf,
                    telefone = :telefone,
                    email = :email,
                    assinatura = :assinatura,
                    ativo = :ativo
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':registro_profissional' => $dados['registro_profissional'] ?? null,
            ':conselho' => $dados['conselho'] ?? null,
            ':uf' => $dados['uf'] ?? null,
            ':cpf' => $dados['cpf'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':email' => $dados['email'] ?? null,
            ':assinatura' => $dados['assinatura'] ?? null,
            ':ativo' => $dados['ativo'] ?? 1,
            ':id' => $id
        ]);
    }

    /* =========================
       EXCLUIR
    ========================= */
    public function excluir(int $id)
    {
        $sql = "DELETE FROM tecnicos WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    /* =========================
       ATIVOS SOMENTE
    ========================= */
    public function listarAtivos()
    {
        $sql = "SELECT *
                FROM tecnicos
                WHERE ativo = 1
                ORDER BY nome ASC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}