<?php

class Cargo extends Model
{
    public function listarTudo(): array
    {
        return $this->query("
            SELECT
                c.*,
                COUNT(DISTINCT h.empresa_id) AS total_empresas,
                COUNT(DISTINCT h.unidade_id) AS total_unidades,
                COUNT(DISTINCT h.setor_id) AS total_setores,
                COUNT(DISTINCT f.id) AS total_funcionarios
            FROM cargos c
            LEFT JOIN hierarquias h ON h.cargo_id = c.id
            LEFT JOIN funcionarios f ON f.hierarquia_id = h.id AND f.ativo = 1
            GROUP BY c.id, c.codigo, c.codigo_externo, c.nome, c.cbo, c.descricao, c.ativo, c.created_at, c.updated_at
            ORDER BY c.ativo DESC, c.nome
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAtivos(): array
    {
        return $this->query('SELECT * FROM cargos WHERE ativo = 1 ORDER BY nome')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array
    {
        $registro = $this->query('SELECT * FROM cargos WHERE id = :id LIMIT 1', [':id' => $id])
            ->fetch(PDO::FETCH_ASSOC);
        return $registro ?: null;
    }

    public function buscarPorCodigo(string $codigo): ?array
    {
        $registro = $this->query('SELECT * FROM cargos WHERE codigo = :codigo LIMIT 1', [':codigo' => trim($codigo)])
            ->fetch(PDO::FETCH_ASSOC);
        return $registro ?: null;
    }

    public function buscarPorNome(string $nome): ?array
    {
        $registro = $this->query('SELECT * FROM cargos WHERE nome = :nome LIMIT 1', [':nome' => trim($nome)])
            ->fetch(PDO::FETCH_ASSOC);
        return $registro ?: null;
    }

    public function salvar(array $dados): int
    {
        $this->validar($dados);
        $this->query("
            INSERT INTO cargos (codigo, codigo_externo, nome, cbo, descricao, ativo)
            VALUES (:codigo, :codigo_externo, :nome, :cbo, :descricao, :ativo)
        ", $this->parametros($dados));
        return (int)$this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        if (!$this->buscarPorId($id)) {
            throw new RuntimeException('Cargo não encontrado.');
        }
        $this->validar($dados);
        $params = $this->parametros($dados);
        $params[':id'] = $id;
        return $this->query("
            UPDATE cargos SET
                codigo = :codigo,
                codigo_externo = :codigo_externo,
                nome = :nome,
                cbo = :cbo,
                descricao = :descricao,
                ativo = :ativo
            WHERE id = :id
        ", $params)->rowCount() >= 0;
    }

    public function desativar(int $id): bool
    {
        $stmt = $this->query('UPDATE cargos SET ativo = 0 WHERE id = :id AND ativo = 1', [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function validar(array $dados): void
    {
        if (trim((string)($dados['nome'] ?? '')) === '') {
            throw new RuntimeException('Informe o nome do cargo.');
        }
    }

    private function parametros(array $dados): array
    {
        return [
            ':codigo' => $this->textoOuNull($dados['codigo'] ?? null),
            ':codigo_externo' => $this->textoOuNull($dados['codigo_externo'] ?? null),
            ':nome' => trim((string)$dados['nome']),
            ':cbo' => $this->textoOuNull($dados['cbo'] ?? null),
            ':descricao' => $this->textoOuNull($dados['descricao'] ?? null),
            ':ativo' => !empty($dados['ativo']) ? 1 : 0,
        ];
    }

    private function textoOuNull(mixed $valor): ?string
    {
        $valor = trim((string)$valor);
        return $valor !== '' ? $valor : null;
    }
}
