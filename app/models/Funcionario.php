<?php

class Funcionario extends Model
{
    public function listar(array $filtros = []): array
    {
        $sql = "
            SELECT
                f.*,
                h.setor_id,
                h.cargo_id,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                e.cnpj AS empresa_cnpj,
                u.nome AS unidade_nome,
                u.cnpj AS unidade_cnpj,
                s.nome AS setor_nome,
                c.nome AS cargo_nome,
                c.cbo,
                iu.nome AS inativado_por_nome
            FROM funcionarios f
            INNER JOIN hierarquias h ON h.id = f.hierarquia_id
            INNER JOIN empresas e ON e.id = f.empresa_id
            LEFT JOIN unidades u ON u.id = f.unidade_id
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            LEFT JOIN usuarios iu ON iu.id = f.inativado_por
            WHERE 1 = 1
        ";

        $params = [];

        $empresaId = (int)($filtros['empresa_id'] ?? 0);
        if ($empresaId > 0) {
            $sql .= ' AND f.empresa_id = :empresa_id';
            $params[':empresa_id'] = $empresaId;
        }

        $unidadeId = (int)($filtros['unidade_id'] ?? 0);
        if ($unidadeId > 0) {
            $sql .= ' AND f.unidade_id = :unidade_id';
            $params[':unidade_id'] = $unidadeId;
        }

        $status = strtoupper(trim((string)($filtros['status'] ?? '')));
        if ($status === 'ATIVO') {
            $sql .= ' AND f.ativo = 1';
        } elseif ($status === 'INATIVO') {
            $sql .= ' AND f.ativo = 0';
        }

        $busca = trim((string)($filtros['busca'] ?? ''));
        if ($busca !== '') {
            $sql .= "
                AND (
                    f.nome LIKE :busca
                    OR f.cpf LIKE :busca
                    OR f.matricula LIKE :busca
                    OR f.codigo LIKE :busca
                    OR e.razao_social LIKE :busca
                    OR e.nome_fantasia LIKE :busca
                    OR u.nome LIKE :busca
                    OR s.nome LIKE :busca
                    OR c.nome LIKE :busca
                )
            ";
            $params[':busca'] = '%' . $busca . '%';
        }

        $sql .= "
            ORDER BY
                f.ativo DESC,
                COALESCE(e.nome_fantasia, e.razao_social) ASC,
                u.nome ASC,
                s.nome ASC,
                c.nome ASC,
                f.nome ASC
        ";

        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obterIndicadores(): array
    {
        $sql = "
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) AS ativos,
                SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) AS inativos,
                SUM(
                    CASE
                        WHEN data_admissao IS NOT NULL
                         AND YEAR(data_admissao) = YEAR(CURRENT_DATE())
                         AND MONTH(data_admissao) = MONTH(CURRENT_DATE())
                        THEN 1 ELSE 0
                    END
                ) AS admitidos_mes
            FROM funcionarios
        ";

        $resultado = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int)($resultado['total'] ?? 0),
            'ativos' => (int)($resultado['ativos'] ?? 0),
            'inativos' => (int)($resultado['inativos'] ?? 0),
            'admitidos_mes' => (int)($resultado['admitidos_mes'] ?? 0),
        ];
    }

    public function listarPorContexto(int $empresaId, ?int $unidadeId): array
    {
        $filtros = [
            'empresa_id' => $empresaId,
            'unidade_id' => $unidadeId ?? 0,
        ];

        return $this->listar($filtros);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                f.*,
                h.setor_id,
                h.cargo_id,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                u.nome AS unidade_nome,
                s.nome AS setor_nome,
                c.nome AS cargo_nome,
                c.cbo
            FROM funcionarios f
            INNER JOIN hierarquias h ON h.id = f.hierarquia_id
            INNER JOIN empresas e ON e.id = f.empresa_id
            LEFT JOIN unidades u ON u.id = f.unidade_id
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            WHERE f.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function salvar(array $dados): int
    {
        $nome = trim((string)($dados['nome'] ?? ''));
        if ($nome === '') {
            throw new RuntimeException('Informe o nome do funcionário.');
        }

        $hierarquia = $this->resolverHierarquia($dados);

        $stmt = $this->db->prepare("
            INSERT INTO funcionarios (
                empresa_id,
                unidade_id,
                hierarquia_id,
                codigo,
                codigo_externo,
                matricula,
                nome,
                cpf,
                data_admissao,
                observacoes,
                ativo
            ) VALUES (
                :empresa_id,
                :unidade_id,
                :hierarquia_id,
                :codigo,
                :codigo_externo,
                :matricula,
                :nome,
                :cpf,
                :data_admissao,
                :observacoes,
                1
            )
        ");

        $this->bindDados($stmt, $dados, $hierarquia);
        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        if (!$this->buscarPorId($id)) {
            throw new RuntimeException('Funcionário não encontrado.');
        }

        $nome = trim((string)($dados['nome'] ?? ''));
        if ($nome === '') {
            throw new RuntimeException('Informe o nome do funcionário.');
        }

        $hierarquia = $this->resolverHierarquia($dados);

        $stmt = $this->db->prepare("
            UPDATE funcionarios SET
                empresa_id = :empresa_id,
                unidade_id = :unidade_id,
                hierarquia_id = :hierarquia_id,
                codigo = :codigo,
                codigo_externo = :codigo_externo,
                matricula = :matricula,
                nome = :nome,
                cpf = :cpf,
                data_admissao = :data_admissao,
                observacoes = :observacoes
            WHERE id = :id
        ");

        $this->bindDados($stmt, $dados, $hierarquia);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function inativar(
        int $funcionarioId,
        int $empresaId,
        ?int $unidadeId,
        int $usuarioId,
        string $motivo,
        ?string $dataDesligamento = null
    ): bool {
        $motivo = trim($motivo);
        if ($motivo === '') {
            throw new RuntimeException('Informe o motivo da inativação.');
        }

        $sql = "
            UPDATE funcionarios
            SET ativo = 0,
                motivo_inativacao = :motivo,
                data_desligamento = :data_desligamento,
                inativado_por = :usuario_id
            WHERE id = :id
              AND empresa_id = :empresa_id
              AND ativo = 1
        ";

        $params = [
            ':motivo' => $motivo,
            ':data_desligamento' => $this->dataOuNull($dataDesligamento) ?? date('Y-m-d'),
            ':usuario_id' => $usuarioId,
            ':id' => $funcionarioId,
            ':empresa_id' => $empresaId,
        ];

        if ($unidadeId !== null && $unidadeId > 0) {
            $sql .= ' AND unidade_id = :unidade_id';
            $params[':unidade_id'] = $unidadeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function reativar(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE funcionarios
            SET ativo = 1,
                data_desligamento = NULL,
                motivo_inativacao = NULL,
                inativado_por = NULL
            WHERE id = :id
              AND ativo = 0
        ");
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    private function resolverHierarquia(array $dados): array
    {
        $hierarquiaId = (int)($dados['hierarquia_id'] ?? 0);
        if ($hierarquiaId <= 0) {
            throw new RuntimeException('Selecione o cargo na hierarquia da empresa.');
        }

        $stmt = $this->db->prepare("
            SELECT h.*
            FROM hierarquias h
            WHERE h.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $hierarquiaId]);
        $hierarquia = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$hierarquia) {
            throw new RuntimeException('A hierarquia selecionada não foi encontrada.');
        }

        $empresaInformada = (int)($dados['empresa_id'] ?? 0);
        if ($empresaInformada > 0 && $empresaInformada !== (int)$hierarquia['empresa_id']) {
            throw new RuntimeException('O cargo selecionado não pertence à empresa informada.');
        }

        $unidadeInformada = (int)($dados['unidade_id'] ?? 0);
        if ($unidadeInformada > 0 && $unidadeInformada !== (int)$hierarquia['unidade_id']) {
            throw new RuntimeException('O cargo selecionado não pertence à unidade informada.');
        }

        return $hierarquia;
    }

    private function bindDados(PDOStatement $stmt, array $dados, array $hierarquia): void
    {
        $stmt->bindValue(':empresa_id', (int)$hierarquia['empresa_id'], PDO::PARAM_INT);
        $stmt->bindValue(':unidade_id', (int)$hierarquia['unidade_id'], PDO::PARAM_INT);
        $stmt->bindValue(':hierarquia_id', (int)$hierarquia['id'], PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $this->textoOuNull($dados['codigo'] ?? null));
        $stmt->bindValue(':codigo_externo', $this->textoOuNull($dados['codigo_externo'] ?? null));
        $stmt->bindValue(':matricula', $this->textoOuNull($dados['matricula'] ?? null));
        $stmt->bindValue(':nome', trim((string)$dados['nome']));
        $stmt->bindValue(':cpf', $this->textoOuNull($dados['cpf'] ?? null));
        $stmt->bindValue(':data_admissao', $this->dataOuNull($dados['data_admissao'] ?? null));
        $stmt->bindValue(':observacoes', $this->textoOuNull($dados['observacoes'] ?? null));
    }

    private function textoOuNull(mixed $valor): ?string
    {
        $valor = trim((string)$valor);
        return $valor !== '' ? $valor : null;
    }

    private function dataOuNull(mixed $valor): ?string
    {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return null;
        }

        $data = DateTime::createFromFormat('Y-m-d', $valor);
        if (!$data || $data->format('Y-m-d') !== $valor) {
            throw new RuntimeException('Informe uma data válida.');
        }

        return $valor;
    }
}