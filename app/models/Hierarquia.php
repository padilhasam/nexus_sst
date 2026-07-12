<?php

class Hierarquia extends Model
{
    public function listarTudo()
    {
        $sql = "
            SELECT
                h.*,
                e.nome_fantasia AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                u.nome AS unidade_nome,
                s.nome AS setor_nome,
                c.nome AS cargo_nome
            FROM hierarquias h
            INNER JOIN empresas e ON e.id = h.empresa_id
            INNER JOIN unidades u ON u.id = h.unidade_id
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            ORDER BY
                e.nome_fantasia ASC,
                u.nome ASC,
                s.nome ASC,
                c.nome ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id)
    {
        $sql = "
            SELECT *
            FROM hierarquias
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarCompletaPorId(int $id)
    {
        $sql = "
            SELECT
                h.*,
                e.nome_fantasia AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                u.nome AS unidade_nome,
                s.nome AS setor_nome,
                c.nome AS cargo_nome
            FROM hierarquias h
            INNER JOIN empresas e ON e.id = h.empresa_id
            INNER JOIN unidades u ON u.id = h.unidade_id
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            WHERE h.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existe(int $empresaId, int $unidadeId, int $setorId, int $cargoId)
    {
        $sql = "
            SELECT id
            FROM hierarquias
            WHERE empresa_id = :empresa_id
              AND unidade_id = :unidade_id
              AND setor_id = :setor_id
              AND cargo_id = :cargo_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':empresa_id' => $empresaId,
            ':unidade_id' => $unidadeId,
            ':setor_id' => $setorId,
            ':cargo_id' => $cargoId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados)
    {
        $sql = "
            INSERT INTO hierarquias (
                empresa_id,
                unidade_id,
                setor_id,
                cargo_id
            ) VALUES (
                :empresa_id,
                :unidade_id,
                :setor_id,
                :cargo_id
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':empresa_id' => $dados['empresa_id'],
            ':unidade_id' => $dados['unidade_id'],
            ':setor_id' => $dados['setor_id'],
            ':cargo_id' => $dados['cargo_id']
        ]);

        return $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados)
    {
        $sql = "
            UPDATE hierarquias SET
                empresa_id = :empresa_id,
                unidade_id = :unidade_id,
                setor_id = :setor_id,
                cargo_id = :cargo_id
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':empresa_id' => $dados['empresa_id'],
            ':unidade_id' => $dados['unidade_id'],
            ':setor_id' => $dados['setor_id'],
            ':cargo_id' => $dados['cargo_id']
        ]);
    }

    public function excluir(int $id)
    {
        $sql = "
            DELETE FROM hierarquias
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    public function contarEmpresas()
    {
        $sql = "
            SELECT COUNT(DISTINCT empresa_id)
            FROM hierarquias
        ";

        return $this->db->query($sql)->fetchColumn();
    }

    public function contarSetores()
    {
        $sql = "
            SELECT COUNT(DISTINCT setor_id)
            FROM hierarquias
        ";

        return $this->db->query($sql)->fetchColumn();
    }

    public function contarCargos()
    {
        $sql = "
            SELECT COUNT(DISTINCT cargo_id)
            FROM hierarquias
        ";

        return $this->db->query($sql)->fetchColumn();
    }

    public function listarEmpresasEstruturadas()
    {
        $sql = "
            SELECT
                e.id,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                COUNT(DISTINCT h.unidade_id) AS total_unidades,
                COUNT(DISTINCT h.setor_id) AS total_setores,
                COUNT(DISTINCT h.cargo_id) AS total_cargos,
                COUNT(h.id) AS total_hierarquias
            FROM hierarquias h
            INNER JOIN empresas e ON e.id = h.empresa_id
            GROUP BY 
                e.id,
                e.nome_fantasia,
                e.razao_social
            ORDER BY empresa_nome ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}