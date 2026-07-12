<?php

class Usuario extends Database
{
    private PDO $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->getConnection();
    }

    /* =========================
       BUSCAR POR E-MAIL
    ========================= */
    public function buscarPorEmail(string $email)
    {
        $sql = "SELECT * 
                FROM usuarios 
                WHERE email = :email 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       LISTAR TODOS
    ========================= */
    public function listarTodos()
    {
        $sql = "SELECT 
                    id,
                    nome,
                    email,
                    tipo,
                    ativo,
                    ultimo_acesso,
                    criado_em
                FROM usuarios
                ORDER BY nome ASC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       BUSCAR POR ID
    ========================= */
    public function buscarPorId(int $id)
    {
        $sql = "SELECT * 
                FROM usuarios 
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
        $sql = "INSERT INTO usuarios (
                    nome,
                    email,
                    senha,
                    tipo,
                    ativo,
                    criado_em
                ) VALUES (
                    :nome,
                    :email,
                    :senha,
                    :tipo,
                    :ativo,
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nome'  => $dados['nome'],
            ':email' => $dados['email'],
            ':senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
            ':tipo'  => $dados['tipo'],
            ':ativo' => $dados['ativo'] ?? 1
        ]);
    }

    /* =========================
       ATUALIZAR
    ========================= */
    public function atualizar(int $id, array $dados)
    {
        $sql = "UPDATE usuarios SET
                    nome = :nome,
                    email = :email,
                    tipo = :tipo,
                    ativo = :ativo
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nome'  => $dados['nome'],
            ':email' => $dados['email'],
            ':tipo'  => $dados['tipo'],
            ':ativo' => $dados['ativo'],
            ':id'    => $id
        ]);
    }

    /* =========================
       ALTERAR SENHA
    ========================= */
    public function alterarSenha(int $id, string $senha)
    {
        $sql = "UPDATE usuarios SET
                    senha = :senha
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':senha' => password_hash($senha, PASSWORD_DEFAULT),
            ':id'    => $id
        ]);
    }

    /* =========================
       EXCLUIR
    ========================= */
    public function excluir(int $id)
    {
        $sql = "DELETE FROM usuarios 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    /* =========================
       ATUALIZAR ÚLTIMO ACESSO
    ========================= */
    public function atualizarUltimoAcesso(int $id)
    {
        $sql = "UPDATE usuarios SET
                    ultimo_acesso = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    /* =========================
       TOTAL DE USUÁRIOS
    ========================= */
    public function totalUsuarios()
    {
        $sql = "SELECT COUNT(*) as total
                FROM usuarios";

        $stmt = $this->db->query($sql);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}