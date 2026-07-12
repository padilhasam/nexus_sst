<?php

class Risco extends Model
{
    public function listarTodos(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM riscos
            WHERE ativo = 1
            ORDER BY categoria, nome
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorCategoria(string $categoria): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM riscos
            WHERE categoria = :categoria
              AND ativo = 1
            ORDER BY nome ASC
        ");

        $stmt->bindValue(':categoria', $categoria);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM riscos
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM riscos
            WHERE codigo = :codigo
            LIMIT 1
        ");

        $stmt->bindValue(':codigo', $codigo);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

   public function salvar(array $dados)
    {
        $stmt = $this->db->prepare("
            INSERT INTO riscos (
                codigo,
                codigo_externo,
                categoria,
                nome,
                tipo_avaliacao,
                descricao,
                normas_aplicaveis,
                metodologia,
                unidade_medida,
                limite_nr15,
                limite_acgih,
                nivel_acao,
                exige_quantificacao,
                severidade_padrao,
                probabilidade_padrao,
                ativo
            ) VALUES (
                :codigo,
                :codigo_externo,
                :categoria,
                :nome,
                :tipo_avaliacao,
                :descricao,
                :normas_aplicaveis,
                :metodologia,
                :unidade_medida,
                :limite_nr15,
                :limite_acgih,
                :nivel_acao,
                :exige_quantificacao,
                :severidade_padrao,
                :probabilidade_padrao,
                :ativo
            )
        ");

        return $stmt->execute($this->mapearParametros($dados))
            ? (int)$this->db->lastInsertId()
            : false;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $params = $this->mapearParametros($dados);
        $params[':id'] = $id;

        $stmt = $this->db->prepare("
            UPDATE riscos SET
                codigo = :codigo,
                codigo_externo = :codigo_externo,
                categoria = :categoria,
                nome = :nome,
                tipo_avaliacao = :tipo_avaliacao,
                descricao = :descricao,
                normas_aplicaveis = :normas_aplicaveis,
                metodologia = :metodologia,
                unidade_medida = :unidade_medida,
                limite_nr15 = :limite_nr15,
                limite_acgih = :limite_acgih,
                nivel_acao = :nivel_acao,
                exige_quantificacao = :exige_quantificacao,
                severidade_padrao = :severidade_padrao,
                probabilidade_padrao = :probabilidade_padrao,
                ativo = :ativo
            WHERE id = :id
        ");

        return $stmt->execute($params);
    }

    public function desativar(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE riscos
            SET ativo = 0
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    private function mapearParametros(array $dados): array
    {
        return [
            ':codigo' => $dados['codigo'] ?? null,
            ':codigo_externo' => $dados['codigo_externo'] ?? null,
            ':categoria' => $dados['categoria'],
            ':nome' => $dados['nome'],
            ':tipo_avaliacao' => $dados['tipo_avaliacao'] ?? 'Qualitativo',
            ':descricao' => $dados['descricao'] ?? null,
            ':normas_aplicaveis' => $dados['normas_aplicaveis'] ?? null,
            ':metodologia' => $dados['metodologia'] ?? null,
            ':unidade_medida' => $dados['unidade_medida'] ?? null,
            ':limite_nr15' => $dados['limite_nr15'] ?? null,
            ':limite_acgih' => $dados['limite_acgih'] ?? null,
            ':nivel_acao' => $dados['nivel_acao'] ?? null,
            ':exige_quantificacao' => (int)($dados['exige_quantificacao'] ?? 0),
            ':severidade_padrao' => (int)($dados['severidade_padrao'] ?? 1),
            ':probabilidade_padrao' => (int)($dados['probabilidade_padrao'] ?? 1),
            ':ativo' => (int)($dados['ativo'] ?? 1)
        ];
    }
}