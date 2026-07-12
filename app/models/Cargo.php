<?php

class Cargo extends Model
{
    public function listarTudo()
    {
        $sql = "
            SELECT *
            FROM cargos
            ORDER BY nome ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAtivos()
    {
        $sql = "
            SELECT *
            FROM cargos
            WHERE ativo = 1
            ORDER BY nome ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id)
    {
        $sql = "
            SELECT *
            FROM cargos
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorCodigo(string $codigo)
    {
        $sql = "
            SELECT *
            FROM cargos
            WHERE codigo = :codigo
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':codigo' => $codigo
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorNome(string $nome)
    {
        $sql = "
            SELECT *
            FROM cargos
            WHERE nome = :nome
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nome' => $nome
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados)
    {
        $sql = "
            INSERT INTO cargos (
                codigo,
                codigo_externo,
                nome,
                cbo,
                descricao,
                ativo
            ) VALUES (
                :codigo,
                :codigo_externo,
                :nome,
                :cbo,
                :descricao,
                :ativo
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':codigo'          => $dados['codigo'] ?? null,
            ':codigo_externo' => $dados['codigo_externo'] ?? null,
            ':nome'           => $dados['nome'],
            ':cbo'            => $dados['cbo'] ?? null,
            ':descricao'      => $dados['descricao'] ?? null,
            ':ativo'          => $dados['ativo'] ?? 1
        ]);

        return $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados)
    {
        $sql = "
            UPDATE cargos SET
                codigo = :codigo,
                codigo_externo = :codigo_externo,
                nome = :nome,
                cbo = :cbo,
                descricao = :descricao,
                ativo = :ativo
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'             => $id,
            ':codigo'         => $dados['codigo'] ?? null,
            ':codigo_externo' => $dados['codigo_externo'] ?? null,
            ':nome'           => $dados['nome'],
            ':cbo'            => $dados['cbo'] ?? null,
            ':descricao'      => $dados['descricao'] ?? null,
            ':ativo'          => $dados['ativo'] ?? 1
        ]);
    }

    public function desativar(int $id)
    {
        $sql = "
            UPDATE cargos
            SET ativo = 0
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}