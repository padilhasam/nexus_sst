<?php

class GHE extends Model
{
    public function listar(array $filtros, int $usuarioId, string $tipoUsuario): array
    {
        $where = ['1 = 1'];
        $params = [];

        if (!$this->usuarioAdministrador($tipoUsuario)) {
            $where[] = 'cv.usuario_id = :usuario_id';
            $params[':usuario_id'] = $usuarioId;
        }

        $empresaId = (int)($filtros['empresa_id'] ?? 0);
        if ($empresaId > 0) {
            $where[] = 'g.empresa_id = :empresa_id';
            $params[':empresa_id'] = $empresaId;
        }

        $unidadeId = (int)($filtros['unidade_id'] ?? 0);
        if ($unidadeId > 0) {
            $where[] = 'g.unidade_id = :unidade_id';
            $params[':unidade_id'] = $unidadeId;
        }

        $status = strtoupper(trim((string)($filtros['status'] ?? '')));
        if ($status === 'ATIVO') {
            $where[] = 'g.ativo = 1';
        } elseif ($status === 'INATIVO') {
            $where[] = 'g.ativo = 0';
        }

        $busca = trim((string)($filtros['busca'] ?? ''));
        if ($busca !== '') {
            $where[] = "(
                g.codigo LIKE :busca
                OR g.nome LIKE :busca
                OR g.descricao LIKE :busca
                OR e.razao_social LIKE :busca
                OR e.nome_fantasia LIKE :busca
                OR un.nome LIKE :busca
                OR tec.nome LIKE :busca
            )";
            $params[':busca'] = '%' . $busca . '%';
        }

        $sql = "
            SELECT
                g.*,
                cv.status AS checklist_status,
                cv.usuario_id AS tecnico_id,
                vt.data_visita,
                vt.hora_visita,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                e.cnpj AS empresa_cnpj,
                un.nome AS unidade_nome,
                un.cnpj AS unidade_cnpj,
                tec.nome AS tecnico_nome,
                criador.nome AS criado_por_nome,
                (
                    SELECT COUNT(*)
                    FROM ghe_cargos gc
                    WHERE gc.ghe_id = g.id
                ) AS total_cargos,
                (
                    SELECT COUNT(*)
                    FROM ghe_riscos gr
                    WHERE gr.ghe_id = g.id
                ) AS total_riscos,
                (
                    SELECT COUNT(*)
                    FROM ghe_riscos grq
                    WHERE grq.ghe_id = g.id
                      AND grq.exige_quantificacao = 1
                ) AS total_quantificaveis
            FROM ghes g
            INNER JOIN checklists_visita cv ON cv.id = g.checklist_id
            INNER JOIN visitas_tecnicas vt ON vt.id = cv.visita_id
            INNER JOIN empresas e ON e.id = g.empresa_id
            INNER JOIN usuarios tec ON tec.id = cv.usuario_id
            INNER JOIN usuarios criador ON criador.id = g.criado_por
            LEFT JOIN unidades un ON un.id = g.unidade_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY
                g.ativo DESC,
                CASE cv.status
                    WHEN 'EM_ANDAMENTO' THEN 1
                    WHEN 'ABERTO' THEN 2
                    WHEN 'CONCLUIDO' THEN 3
                    WHEN 'CANCELADO' THEN 4
                    ELSE 5
                END,
                vt.data_visita DESC,
                g.codigo ASC,
                g.nome ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obterIndicadores(int $usuarioId, string $tipoUsuario): array
    {
        $where = [];
        $params = [];

        if (!$this->usuarioAdministrador($tipoUsuario)) {
            $where[] = 'cv.usuario_id = :usuario_id';
            $params[':usuario_id'] = $usuarioId;
        }

        $sql = "
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN g.ativo = 1 THEN 1 ELSE 0 END) AS ativos,
                SUM(CASE WHEN g.ativo = 0 THEN 1 ELSE 0 END) AS inativos,
                COALESCE(SUM((
                    SELECT COUNT(*)
                    FROM ghe_riscos gr
                    WHERE gr.ghe_id = g.id
                      AND gr.exige_quantificacao = 1
                )), 0) AS quantificaveis
            FROM ghes g
            INNER JOIN checklists_visita cv ON cv.id = g.checklist_id
        ";

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int)($resultado['total'] ?? 0),
            'ativos' => (int)($resultado['ativos'] ?? 0),
            'inativos' => (int)($resultado['inativos'] ?? 0),
            'quantificaveis' => (int)($resultado['quantificaveis'] ?? 0),
        ];
    }

    public function listarChecklistsDisponiveis(int $usuarioId, string $tipoUsuario): array
    {
        $where = ["cv.status IN ('ABERTO', 'EM_ANDAMENTO')"];
        $params = [];

        if (!$this->usuarioAdministrador($tipoUsuario)) {
            $where[] = 'cv.usuario_id = :usuario_id';
            $params[':usuario_id'] = $usuarioId;
        }

        $sql = "
            SELECT
                cv.id,
                cv.status,
                cv.empresa_id,
                cv.unidade_id,
                cv.usuario_id,
                vt.data_visita,
                vt.hora_visita,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.cnpj AS empresa_cnpj,
                un.nome AS unidade_nome,
                tec.nome AS tecnico_nome
            FROM checklists_visita cv
            INNER JOIN visitas_tecnicas vt ON vt.id = cv.visita_id
            INNER JOIN empresas e ON e.id = cv.empresa_id
            INNER JOIN usuarios tec ON tec.id = cv.usuario_id
            LEFT JOIN unidades un ON un.id = cv.unidade_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY vt.data_visita DESC, vt.hora_visita DESC, cv.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarContextoChecklist(
        int $checklistId,
        int $usuarioId,
        string $tipoUsuario
    ): ?array {
        $sql = "
            SELECT
                cv.id,
                cv.status,
                cv.empresa_id,
                cv.unidade_id,
                cv.usuario_id,
                vt.data_visita,
                vt.hora_visita,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.cnpj AS empresa_cnpj,
                un.nome AS unidade_nome,
                tec.nome AS tecnico_nome
            FROM checklists_visita cv
            INNER JOIN visitas_tecnicas vt ON vt.id = cv.visita_id
            INNER JOIN empresas e ON e.id = cv.empresa_id
            INNER JOIN usuarios tec ON tec.id = cv.usuario_id
            LEFT JOIN unidades un ON un.id = cv.unidade_id
            WHERE cv.id = :checklist_id
        ";

        $params = [':checklist_id' => $checklistId];

        if (!$this->usuarioAdministrador($tipoUsuario)) {
            $sql .= ' AND cv.usuario_id = :usuario_id';
            $params[':usuario_id'] = $usuarioId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        return $registro ?: null;
    }

    public function listarHierarquiasPorChecklist(int $checklistId): array
    {
        $contexto = $this->buscarContextoChecklistSemPermissao($checklistId);
        if (!$contexto) {
            return [];
        }

        $sql = "
            SELECT
                h.id,
                h.empresa_id,
                h.unidade_id,
                h.setor_id,
                h.cargo_id,
                s.nome AS setor_nome,
                c.nome AS cargo_nome,
                c.cbo,
                un.nome AS unidade_nome
            FROM hierarquias h
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            LEFT JOIN unidades un ON un.id = h.unidade_id
            WHERE h.empresa_id = :empresa_id
        ";

        $params = [':empresa_id' => (int)$contexto['empresa_id']];

        if (!empty($contexto['unidade_id'])) {
            $sql .= ' AND h.unidade_id = :unidade_id';
            $params[':unidade_id'] = (int)$contexto['unidade_id'];
        }

        $sql .= ' ORDER BY un.nome, s.nome, c.nome';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorChecklist(int $checklistId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM ghes
            WHERE checklist_id = :checklist_id AND ativo = 1
            ORDER BY codigo ASC, nome ASC
        ");
        $stmt->execute([':checklist_id' => $checklistId]);
        $ghes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ghes as &$ghe) {
            $ghe['cargos'] = $this->listarCargos((int)$ghe['id']);
            $ghe['riscos'] = $this->listarRiscos((int)$ghe['id']);
        }
        unset($ghe);

        return $ghes;
    }

    public function buscarPorId(
        int $gheId,
        int $usuarioId,
        string $tipoUsuario
    ): ?array {
        $sql = "
            SELECT
                g.*,
                cv.status AS checklist_status,
                cv.usuario_id AS tecnico_id,
                vt.data_visita,
                vt.hora_visita,
                COALESCE(e.nome_fantasia, e.razao_social) AS empresa_nome,
                e.razao_social AS empresa_razao_social,
                e.cnpj AS empresa_cnpj,
                un.nome AS unidade_nome,
                un.cnpj AS unidade_cnpj,
                tec.nome AS tecnico_nome,
                criador.nome AS criado_por_nome
            FROM ghes g
            INNER JOIN checklists_visita cv ON cv.id = g.checklist_id
            INNER JOIN visitas_tecnicas vt ON vt.id = cv.visita_id
            INNER JOIN empresas e ON e.id = g.empresa_id
            INNER JOIN usuarios tec ON tec.id = cv.usuario_id
            INNER JOIN usuarios criador ON criador.id = g.criado_por
            LEFT JOIN unidades un ON un.id = g.unidade_id
            WHERE g.id = :ghe_id
        ";

        $params = [':ghe_id' => $gheId];

        if (!$this->usuarioAdministrador($tipoUsuario)) {
            $sql .= ' AND cv.usuario_id = :usuario_id';
            $params[':usuario_id'] = $usuarioId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $ghe = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ghe) {
            return null;
        }

        $ghe['cargos'] = $this->listarCargos($gheId);
        $ghe['riscos'] = $this->listarRiscos($gheId);

        return $ghe;
    }

    public function salvar(array $dados, array $hierarquiasIds): int
    {
        if (empty($hierarquiasIds)) {
            throw new RuntimeException('Selecione ao menos um cargo para compor o GHE.');
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO ghes (
                    checklist_id, empresa_id, unidade_id, codigo, nome,
                    descricao, observacoes, criado_por
                ) VALUES (
                    :checklist_id, :empresa_id, :unidade_id, :codigo, :nome,
                    :descricao, :observacoes, :criado_por
                )
            ");
            $stmt->bindValue(':checklist_id', (int)$dados['checklist_id'], PDO::PARAM_INT);
            $stmt->bindValue(':empresa_id', (int)$dados['empresa_id'], PDO::PARAM_INT);
            $this->bindNullableInt($stmt, ':unidade_id', $dados['unidade_id'] ?? null);
            $stmt->bindValue(':codigo', strtoupper(trim((string)$dados['codigo'])));
            $stmt->bindValue(':nome', trim((string)$dados['nome']));
            $stmt->bindValue(':descricao', $this->textoOuNull($dados['descricao'] ?? null));
            $stmt->bindValue(':observacoes', $this->textoOuNull($dados['observacoes'] ?? null));
            $stmt->bindValue(':criado_por', (int)$dados['criado_por'], PDO::PARAM_INT);
            $stmt->execute();

            $gheId = (int)$this->db->lastInsertId();
            $this->vincularHierarquias(
                $gheId,
                $hierarquiasIds,
                (int)$dados['empresa_id'],
                isset($dados['unidade_id']) && $dados['unidade_id'] !== null
                    ? (int)$dados['unidade_id']
                    : null
            );

            $this->db->commit();
            return $gheId;
        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($erro instanceof PDOException && $erro->getCode() === '23000') {
                throw new RuntimeException('Já existe um GHE com este código neste check-list.');
            }
            throw $erro;
        }
    }

    public function atualizar(int $gheId, array $dados, array $hierarquiasIds): bool
    {
        if (empty($hierarquiasIds)) {
            throw new RuntimeException('Selecione ao menos um cargo para compor o GHE.');
        }

        $ghe = $this->buscarBasicoPorId($gheId);
        if (!$ghe) {
            throw new RuntimeException('GHE não encontrado.');
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE ghes
                SET codigo = :codigo,
                    nome = :nome,
                    descricao = :descricao,
                    observacoes = :observacoes
                WHERE id = :id
            ");
            $stmt->execute([
                ':codigo' => strtoupper(trim((string)$dados['codigo'])),
                ':nome' => trim((string)$dados['nome']),
                ':descricao' => $this->textoOuNull($dados['descricao'] ?? null),
                ':observacoes' => $this->textoOuNull($dados['observacoes'] ?? null),
                ':id' => $gheId,
            ]);

            $delete = $this->db->prepare('DELETE FROM ghe_cargos WHERE ghe_id = :ghe_id');
            $delete->execute([':ghe_id' => $gheId]);

            $this->vincularHierarquias(
                $gheId,
                $hierarquiasIds,
                (int)$ghe['empresa_id'],
                !empty($ghe['unidade_id']) ? (int)$ghe['unidade_id'] : null
            );

            $this->db->commit();
            return true;
        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($erro instanceof PDOException && $erro->getCode() === '23000') {
                throw new RuntimeException('Já existe um GHE com este código neste check-list.');
            }
            throw $erro;
        }
    }

    public function inativar(int $gheId): bool
    {
        $stmt = $this->db->prepare('UPDATE ghes SET ativo = 0 WHERE id = :id AND ativo = 1');
        $stmt->execute([':id' => $gheId]);
        return $stmt->rowCount() > 0;
    }

    public function reativar(int $gheId): bool
    {
        $stmt = $this->db->prepare('UPDATE ghes SET ativo = 1 WHERE id = :id AND ativo = 0');
        $stmt->execute([':id' => $gheId]);
        return $stmt->rowCount() > 0;
    }

    public function adicionarRisco(int $gheId, int $checklistId, array $dados): int
    {
        $stmt = $this->db->prepare("
            SELECT g.*, r.unidade_medida, r.exige_quantificacao
            FROM ghes g
            CROSS JOIN riscos r
            WHERE g.id = :ghe_id
              AND g.checklist_id = :checklist_id
              AND g.ativo = 1
              AND r.id = :risco_id
              AND r.ativo = 1
            LIMIT 1
        ");
        $stmt->execute([
            ':ghe_id' => $gheId,
            ':checklist_id' => $checklistId,
            ':risco_id' => (int)$dados['risco_id'],
        ]);
        $contexto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$contexto) {
            throw new RuntimeException('GHE ou risco inválido para este check-list.');
        }

        $stmt = $this->db->prepare("
            INSERT INTO ghe_riscos (
                ghe_id, risco_id, fonte_geradora, meio_propagacao,
                frequencia, tempo_exposicao, intensidade, unidade_medida,
                exige_quantificacao, observacoes
            ) VALUES (
                :ghe_id, :risco_id, :fonte_geradora, :meio_propagacao,
                :frequencia, :tempo_exposicao, :intensidade, :unidade_medida,
                :exige_quantificacao, :observacoes
            )
        ");
        $stmt->execute([
            ':ghe_id' => $gheId,
            ':risco_id' => (int)$dados['risco_id'],
            ':fonte_geradora' => $this->textoOuNull($dados['fonte_geradora'] ?? null),
            ':meio_propagacao' => $this->textoOuNull($dados['meio_propagacao'] ?? null),
            ':frequencia' => $this->enumOuNull($dados['frequencia'] ?? null, [
                'EVENTUAL', 'ESPORADICA', 'INTERMITENTE', 'HABITUAL', 'PERMANENTE'
            ]),
            ':tempo_exposicao' => $this->enumOuNull($dados['tempo_exposicao'] ?? null, [
                'MUITO_BAIXO', 'BAIXO', 'MODERADO', 'ALTO', 'MUITO_ALTO'
            ]),
            ':intensidade' => $this->textoOuNull($dados['intensidade'] ?? null),
            ':unidade_medida' => $this->textoOuNull($contexto['unidade_medida'] ?? null),
            ':exige_quantificacao' => (int)($contexto['exige_quantificacao'] ?? 0),
            ':observacoes' => $this->textoOuNull($dados['observacoes'] ?? null),
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function removerRisco(int $gheId, int $gheRiscoId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM ghe_riscos
            WHERE id = :risco_id
              AND ghe_id = :ghe_id
        ");
        $stmt->execute([
            ':risco_id' => $gheRiscoId,
            ':ghe_id' => $gheId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function checklistEditavel(string $status): bool
    {
        return in_array(strtoupper(trim($status)), ['ABERTO', 'EM_ANDAMENTO'], true);
    }

    private function buscarContextoChecklistSemPermissao(int $checklistId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, empresa_id, unidade_id, usuario_id, status
            FROM checklists_visita
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $checklistId]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        return $registro ?: null;
    }

    private function buscarBasicoPorId(int $gheId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, checklist_id, empresa_id, unidade_id, ativo
            FROM ghes
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $gheId]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        return $registro ?: null;
    }

    private function vincularHierarquias(int $gheId, array $ids, int $empresaId, ?int $unidadeId): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            throw new RuntimeException('Selecione ao menos um cargo válido para compor o GHE.');
        }

        $sql = 'SELECT id FROM hierarquias WHERE empresa_id = :empresa_id';
        $params = [':empresa_id' => $empresaId];
        if ($unidadeId !== null) {
            $sql .= ' AND unidade_id = :unidade_id';
            $params[':unidade_id'] = $unidadeId;
        }

        $placeholders = [];
        foreach ($ids as $indice => $id) {
            $chave = ':hierarquia_' . $indice;
            $placeholders[] = $chave;
            $params[$chave] = $id;
        }
        $sql .= ' AND id IN (' . implode(',', $placeholders) . ')';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, (int)$valor, PDO::PARAM_INT);
        }
        $stmt->execute();
        $validos = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        if (count($validos) !== count($ids)) {
            throw new RuntimeException('Um ou mais cargos selecionados não pertencem à hierarquia da visita.');
        }

        $insert = $this->db->prepare(
            'INSERT INTO ghe_cargos (ghe_id, hierarquia_id) VALUES (:ghe_id, :hierarquia_id)'
        );
        foreach ($validos as $id) {
            $insert->execute([':ghe_id' => $gheId, ':hierarquia_id' => $id]);
        }
    }

    private function listarCargos(int $gheId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                h.id AS hierarquia_id,
                h.unidade_id,
                s.nome AS setor_nome,
                c.nome AS cargo_nome,
                c.cbo,
                un.nome AS unidade_nome
            FROM ghe_cargos gc
            INNER JOIN hierarquias h ON h.id = gc.hierarquia_id
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            LEFT JOIN unidades un ON un.id = h.unidade_id
            WHERE gc.ghe_id = :ghe_id
            ORDER BY un.nome, s.nome, c.nome
        ");
        $stmt->execute([':ghe_id' => $gheId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarRiscos(int $gheId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                gr.*,
                r.codigo AS risco_codigo,
                r.nome AS risco_nome,
                r.categoria,
                r.tipo_avaliacao,
                r.normas_aplicaveis,
                r.limite_nr15,
                r.nivel_acao
            FROM ghe_riscos gr
            INNER JOIN riscos r ON r.id = gr.risco_id
            WHERE gr.ghe_id = :ghe_id
            ORDER BY r.categoria, r.nome, gr.id
        ");
        $stmt->execute([':ghe_id' => $gheId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function bindNullableInt(PDOStatement $stmt, string $param, mixed $valor): void
    {
        if ($valor === null || $valor === '' || (int)$valor <= 0) {
            $stmt->bindValue($param, null, PDO::PARAM_NULL);
            return;
        }
        $stmt->bindValue($param, (int)$valor, PDO::PARAM_INT);
    }

    private function textoOuNull(mixed $valor): ?string
    {
        $valor = trim((string)$valor);
        return $valor !== '' ? $valor : null;
    }

    private function enumOuNull(mixed $valor, array $permitidos): ?string
    {
        $valor = strtoupper(trim((string)$valor));
        return in_array($valor, $permitidos, true) ? $valor : null;
    }

    private function usuarioAdministrador(string $tipoUsuario): bool
    {
        return in_array(strtoupper(trim($tipoUsuario)), ['ADMIN', 'ADMINISTRADOR'], true);
    }
}