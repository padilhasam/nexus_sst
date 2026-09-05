<?php

class Hierarquia extends Model
{
    public function listarTudo(): array
    {
        return $this->query("
            SELECT
                h.*,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                u.nome AS unidade_nome,
                u.empresa_id AS unidade_empresa_id,
                s.nome AS setor_nome,
                s.ativo AS setor_ativo,
                c.nome AS cargo_nome,
                c.cbo,
                c.ativo AS cargo_ativo,
                COUNT(DISTINCT f.id) AS total_funcionarios,
                COUNT(DISTINCT gc.id) AS total_ghes
            FROM hierarquias h
            INNER JOIN empresas e ON e.id = h.empresa_id
            INNER JOIN unidades u ON u.id = h.unidade_id
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            LEFT JOIN funcionarios f ON f.hierarquia_id = h.id
            LEFT JOIN ghe_cargos gc ON gc.hierarquia_id = h.id
            GROUP BY
                h.id, h.empresa_id, h.unidade_id, h.setor_id, h.cargo_id,
                h.created_at, h.updated_at,
                e.nome_fantasia, e.razao_social,
                u.nome, u.empresa_id,
                s.nome, s.ativo,
                c.nome, c.cbo, c.ativo
            ORDER BY COALESCE(e.nome_fantasia, e.razao_social), u.nome, s.nome, c.nome
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array
    {
        $registro = $this->query('SELECT * FROM hierarquias WHERE id = :id LIMIT 1', [':id' => $id])
            ->fetch(PDO::FETCH_ASSOC);
        return $registro ?: null;
    }

    public function buscarCompletaPorId(int $id): ?array
    {
        $registro = $this->query("
            SELECT
                h.*,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                u.nome AS unidade_nome,
                s.nome AS setor_nome,
                c.nome AS cargo_nome,
                c.cbo
            FROM hierarquias h
            INNER JOIN empresas e ON e.id = h.empresa_id
            INNER JOIN unidades u ON u.id = h.unidade_id
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            WHERE h.id = :id
            LIMIT 1
        ", [':id' => $id])->fetch(PDO::FETCH_ASSOC);
        return $registro ?: null;
    }

    public function existe(int $empresaId, int $unidadeId, int $setorId, int $cargoId, int $ignorarId = 0): ?array
    {
        $sql = "
            SELECT id FROM hierarquias
            WHERE empresa_id = :empresa_id
              AND unidade_id = :unidade_id
              AND setor_id = :setor_id
              AND cargo_id = :cargo_id
        ";
        $params = [
            ':empresa_id' => $empresaId,
            ':unidade_id' => $unidadeId,
            ':setor_id' => $setorId,
            ':cargo_id' => $cargoId,
        ];
        if ($ignorarId > 0) {
            $sql .= ' AND id <> :ignorar_id';
            $params[':ignorar_id'] = $ignorarId;
        }
        $sql .= ' LIMIT 1';
        $registro = $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
        return $registro ?: null;
    }

    public function salvar(array $dados): int
    {
        $dados = $this->validarContexto($dados);
        if ($this->existe($dados['empresa_id'], $dados['unidade_id'], $dados['setor_id'], $dados['cargo_id'])) {
            throw new RuntimeException('Esta combinação já existe na hierarquia.');
        }

        $this->query("
            INSERT INTO hierarquias (empresa_id, unidade_id, setor_id, cargo_id)
            VALUES (:empresa_id, :unidade_id, :setor_id, :cargo_id)
        ", $this->params($dados));
        return (int)$this->db->lastInsertId();
    }

    public function salvarEmLote(array $dados): array
    {
        $empresaId = (int)($dados['empresa_id'] ?? 0);
        $unidadeId = (int)($dados['unidade_id'] ?? 0);
        $this->validarEmpresaUnidade($empresaId, $unidadeId);

        $vinculosBrutos = is_array($dados['vinculos'] ?? null) ? $dados['vinculos'] : [];
        $vinculos = [];

        foreach ($vinculosBrutos as $vinculo) {
            $partes = array_map('intval', explode(':', (string)$vinculo, 2));
            if (count($partes) !== 2 || $partes[0] <= 0 || $partes[1] <= 0) {
                continue;
            }
            $vinculos[$partes[0] . ':' . $partes[1]] = [
                'setor_id' => $partes[0],
                'cargo_id' => $partes[1],
            ];
        }

        if ($vinculos === []) {
            throw new RuntimeException('Selecione ao menos um cargo dentro de um setor para montar a hierarquia.');
        }

        $setoresIds = array_values(array_unique(array_column($vinculos, 'setor_id')));
        $cargosIds = array_values(array_unique(array_column($vinculos, 'cargo_id')));
        $this->validarCadastrosAtivosEmLote('setores', $setoresIds);
        $this->validarCadastrosAtivosEmLote('cargos', $cargosIds);

        $inseridos = 0;
        $existentes = 0;

        try {
            $this->db->beginTransaction();
            $buscar = $this->db->prepare("
                SELECT id
                FROM hierarquias
                WHERE empresa_id = :empresa_id
                  AND unidade_id = :unidade_id
                  AND setor_id = :setor_id
                  AND cargo_id = :cargo_id
                LIMIT 1
            ");
            $inserir = $this->db->prepare("
                INSERT INTO hierarquias (empresa_id, unidade_id, setor_id, cargo_id)
                VALUES (:empresa_id, :unidade_id, :setor_id, :cargo_id)
            ");

            foreach ($vinculos as $vinculo) {
                $params = [
                    ':empresa_id' => $empresaId,
                    ':unidade_id' => $unidadeId,
                    ':setor_id' => $vinculo['setor_id'],
                    ':cargo_id' => $vinculo['cargo_id'],
                ];
                $buscar->execute($params);
                if ($buscar->fetchColumn()) {
                    $existentes++;
                    continue;
                }
                $inserir->execute($params);
                $inseridos++;
            }

            $this->db->commit();
        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $erro;
        }

        return [
            'inseridos' => $inseridos,
            'existentes' => $existentes,
            'total' => count($vinculos),
            'empresa_id' => $empresaId,
            'unidade_id' => $unidadeId,
        ];
    }

    public function atualizar(int $id, array $dados): bool
    {
        $atual = $this->buscarPorId($id);
        if (!$atual) {
            throw new RuntimeException('Hierarquia não encontrada.');
        }
        $dados = $this->validarContexto($dados);

        $alterouCombinacao =
            (int)$atual['empresa_id'] !== $dados['empresa_id'] ||
            (int)$atual['unidade_id'] !== $dados['unidade_id'] ||
            (int)$atual['setor_id'] !== $dados['setor_id'] ||
            (int)$atual['cargo_id'] !== $dados['cargo_id'];
        if ($alterouCombinacao) {
            $vinculos = $this->contarVinculos($id);
            if ($vinculos['funcionarios'] > 0 || $vinculos['ghes'] > 0) {
                throw new RuntimeException(sprintf(
                    'Esta hierarquia não pode ser remanejada porque possui %d funcionário(s) e %d vínculo(s) com GHE. Crie uma nova hierarquia e transfira os registros de forma controlada.',
                    $vinculos['funcionarios'],
                    $vinculos['ghes']
                ));
            }
        }
        if ($this->existe($dados['empresa_id'], $dados['unidade_id'], $dados['setor_id'], $dados['cargo_id'], $id)) {
            throw new RuntimeException('Esta combinação já existe na hierarquia.');
        }
        $params = $this->params($dados);
        $params[':id'] = $id;
        return $this->query("
            UPDATE hierarquias SET
                empresa_id = :empresa_id,
                unidade_id = :unidade_id,
                setor_id = :setor_id,
                cargo_id = :cargo_id
            WHERE id = :id
        ", $params)->rowCount() >= 0;
    }

    public function excluir(int $id): bool
    {
        $vinculos = $this->contarVinculos($id);
        if ($vinculos['funcionarios'] > 0 || $vinculos['ghes'] > 0) {
            throw new RuntimeException(sprintf(
                'Não é possível excluir: esta hierarquia possui %d funcionário(s) e %d vínculo(s) com GHE.',
                $vinculos['funcionarios'],
                $vinculos['ghes']
            ));
        }
        $stmt = $this->query('DELETE FROM hierarquias WHERE id = :id', [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function alocarFuncionarios(int $hierarquiaId, array $funcionariosIds): int
    {
        $hierarquia = $this->buscarPorId($hierarquiaId);
        if (!$hierarquia) {
            throw new RuntimeException('Cargo da hierarquia não encontrado.');
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $funcionariosIds))));
        if ($ids === []) {
            throw new RuntimeException('Selecione ao menos um funcionário para alocar neste cargo.');
        }

        $placeholders = [];
        $params = [
            ':empresa_id' => (int)$hierarquia['empresa_id'],
            ':unidade_id' => (int)$hierarquia['unidade_id'],
        ];
        foreach ($ids as $indice => $id) {
            $chave = ':funcionario_' . $indice;
            $placeholders[] = $chave;
            $params[$chave] = $id;
        }

        $sql = "
            SELECT id
            FROM funcionarios
            WHERE ativo = 1
              AND empresa_id = :empresa_id
              AND unidade_id = :unidade_id
              AND id IN (" . implode(',', $placeholders) . ")
        ";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, (int)$valor, PDO::PARAM_INT);
        }
        $stmt->execute();
        $validos = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        if (count($validos) !== count($ids)) {
            throw new RuntimeException('Um ou mais funcionários não pertencem à empresa e unidade selecionadas.');
        }

        $placeholdersUpdate = [];
        $paramsUpdate = [':hierarquia_id' => $hierarquiaId];
        foreach ($validos as $indice => $id) {
            $chave = ':id_' . $indice;
            $placeholdersUpdate[] = $chave;
            $paramsUpdate[$chave] = $id;
        }

        $stmt = $this->db->prepare("
            UPDATE funcionarios
            SET hierarquia_id = :hierarquia_id
            WHERE id IN (" . implode(',', $placeholdersUpdate) . ")
        ");
        foreach ($paramsUpdate as $chave => $valor) {
            $stmt->bindValue($chave, (int)$valor, PDO::PARAM_INT);
        }
        $stmt->execute();

        return count($validos);
    }

    public function listarFuncionariosPorEmpresa(int $empresaId): array
    {
        return $this->query("
            SELECT
                f.id,
                f.nome,
                f.matricula,
                f.codigo,
                f.unidade_id,
                f.hierarquia_id,
                h.setor_id,
                h.cargo_id,
                s.nome AS setor_nome,
                c.nome AS cargo_nome
            FROM funcionarios f
            INNER JOIN hierarquias h ON h.id = f.hierarquia_id
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            WHERE f.empresa_id = :empresa_id
              AND f.ativo = 1
            ORDER BY f.unidade_id, f.nome
        ", [':empresa_id' => $empresaId])->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarVinculos(int $id): array
    {
        $funcionarios = (int)$this->query(
            'SELECT COUNT(*) FROM funcionarios WHERE hierarquia_id = :id',
            [':id' => $id]
        )->fetchColumn();
        $ghes = (int)$this->query(
            'SELECT COUNT(*) FROM ghe_cargos WHERE hierarquia_id = :id',
            [':id' => $id]
        )->fetchColumn();
        return compact('funcionarios', 'ghes');
    }

    public function contarEmpresas(): int
    {
        return (int)$this->db->query('SELECT COUNT(DISTINCT empresa_id) FROM hierarquias')->fetchColumn();
    }

    public function contarUnidades(): int
    {
        return (int)$this->db->query('SELECT COUNT(DISTINCT unidade_id) FROM hierarquias')->fetchColumn();
    }

    public function contarSetores(): int
    {
        return (int)$this->db->query('SELECT COUNT(DISTINCT setor_id) FROM hierarquias')->fetchColumn();
    }

    public function contarCargos(): int
    {
        return (int)$this->db->query('SELECT COUNT(DISTINCT cargo_id) FROM hierarquias')->fetchColumn();
    }

    public function listarEmpresasEstruturadas(): array
    {
        return $this->query("
            SELECT
                e.id,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                COUNT(DISTINCT h.unidade_id) AS total_unidades,
                COUNT(DISTINCT h.setor_id) AS total_setores,
                COUNT(DISTINCT h.cargo_id) AS total_cargos,
                COUNT(h.id) AS total_hierarquias
            FROM hierarquias h
            INNER JOIN empresas e ON e.id = h.empresa_id
            GROUP BY e.id, e.nome_fantasia, e.razao_social
            ORDER BY empresa_nome
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarEstruturaPorEmpresa(int $empresaId): array
    {
        return $this->query("
            SELECT
                h.*,
                u.nome AS unidade_nome,
                s.nome AS setor_nome,
                c.nome AS cargo_nome,
                c.cbo,
                COUNT(DISTINCT f.id) AS total_funcionarios,
                COUNT(DISTINCT gc.id) AS total_ghes
            FROM hierarquias h
            INNER JOIN unidades u ON u.id = h.unidade_id
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            LEFT JOIN funcionarios f ON f.hierarquia_id = h.id AND f.ativo = 1
            LEFT JOIN ghe_cargos gc ON gc.hierarquia_id = h.id
            WHERE h.empresa_id = :empresa_id
            GROUP BY
                h.id, h.empresa_id, h.unidade_id, h.setor_id, h.cargo_id,
                h.created_at, h.updated_at,
                u.nome, s.nome, c.nome, c.cbo
            ORDER BY u.nome, s.nome, c.nome
        ", [':empresa_id' => $empresaId])->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarEmpresaNaHierarquia(int $empresaId): ?array
    {
        $registro = $this->query("
            SELECT id, COALESCE(nome_fantasia, razao_social) AS empresa_nome, cnpj
            FROM empresas
            WHERE id = :id
            LIMIT 1
        ", [':id' => $empresaId])->fetch(PDO::FETCH_ASSOC);
        return $registro ?: null;
    }

    private function validarContexto(array $dados): array
    {
        $normalizados = [
            'empresa_id' => (int)($dados['empresa_id'] ?? 0),
            'unidade_id' => (int)($dados['unidade_id'] ?? 0),
            'setor_id' => (int)($dados['setor_id'] ?? 0),
            'cargo_id' => (int)($dados['cargo_id'] ?? 0),
        ];
        foreach ($normalizados as $valor) {
            if ($valor <= 0) {
                throw new RuntimeException('Preencha Empresa, Unidade, Setor e Cargo.');
            }
        }

        $unidadeValida = (int)$this->query("
            SELECT COUNT(*)
            FROM unidades u
            INNER JOIN empresas e ON e.id = u.empresa_id
            WHERE u.id = :unidade_id
              AND u.empresa_id = :empresa_id
              AND u.ativo = 1
              AND e.ativo = 1
        ", [
            ':unidade_id' => $normalizados['unidade_id'],
            ':empresa_id' => $normalizados['empresa_id'],
        ])->fetchColumn();
        if ($unidadeValida === 0) {
            throw new RuntimeException('A unidade selecionada não pertence à empresa informada ou está inativa.');
        }

        if (!$this->cadastroAtivoExiste('setores', $normalizados['setor_id'])) {
            throw new RuntimeException('O setor selecionado não existe ou está inativo.');
        }
        if (!$this->cadastroAtivoExiste('cargos', $normalizados['cargo_id'])) {
            throw new RuntimeException('O cargo selecionado não existe ou está inativo.');
        }

        return $normalizados;
    }

    private function validarEmpresaUnidade(int $empresaId, int $unidadeId): void
    {
        if ($empresaId <= 0 || $unidadeId <= 0) {
            throw new RuntimeException('Selecione a empresa e uma unidade vinculada a ela.');
        }

        $valida = (int)$this->query("
            SELECT COUNT(*)
            FROM unidades u
            INNER JOIN empresas e ON e.id = u.empresa_id
            WHERE u.id = :unidade_id
              AND u.empresa_id = :empresa_id
              AND u.ativo = 1
              AND e.ativo = 1
        ", [
            ':unidade_id' => $unidadeId,
            ':empresa_id' => $empresaId,
        ])->fetchColumn();

        if ($valida === 0) {
            throw new RuntimeException('A unidade selecionada não pertence à empresa informada ou está inativa.');
        }
    }

    private function validarCadastrosAtivosEmLote(string $tabela, array $ids): void
    {
        if (!in_array($tabela, ['setores', 'cargos'], true) || $ids === []) {
            throw new RuntimeException('A seleção da hierarquia é inválida.');
        }

        $placeholders = [];
        $params = [];
        foreach ($ids as $indice => $id) {
            $chave = ':id_' . $indice;
            $placeholders[] = $chave;
            $params[$chave] = (int)$id;
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$tabela} WHERE ativo = 1 AND id IN (" . implode(',', $placeholders) . ')'
        );
        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor, PDO::PARAM_INT);
        }
        $stmt->execute();

        if ((int)$stmt->fetchColumn() !== count($ids)) {
            throw new RuntimeException('Um ou mais ' . ($tabela === 'setores' ? 'setores' : 'cargos') . ' selecionados estão inativos ou não existem.');
        }
    }

    private function cadastroAtivoExiste(string $tabela, int $id): bool
    {
        if (!in_array($tabela, ['setores', 'cargos'], true)) {
            return false;
        }
        return (int)$this->query(
            "SELECT COUNT(*) FROM {$tabela} WHERE id = :id AND ativo = 1",
            [':id' => $id]
        )->fetchColumn() > 0;
    }

    private function params(array $dados): array
    {
        return [
            ':empresa_id' => $dados['empresa_id'],
            ':unidade_id' => $dados['unidade_id'],
            ':setor_id' => $dados['setor_id'],
            ':cargo_id' => $dados['cargo_id'],
        ];
    }
}
