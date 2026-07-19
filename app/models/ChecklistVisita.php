<?php

class ChecklistVisita extends Model
{
    private const STATUS_VISITA_INICIAVEIS = [
        'ABERTA',
        'AGENDADA',
        'CONFIRMADA',
    ];

    public function buscarPorVisita(int $visitaId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM checklists_visita WHERE visita_id = :visita_id LIMIT 1'
        );
        $stmt->bindValue(':visita_id', $visitaId, PDO::PARAM_INT);
        $stmt->execute();
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        return $registro ?: null;
    }

    /**
     * Inicia o check-list e sincroniza a visita em uma única transação.
     * A Agenda passa a ser exibida em amarelo pelo status da visita.
     */
    public function iniciarPorVisita(
        int $visitaId,
        int $usuarioId,
        bool $usuarioAdministrador = false
    ): int {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                SELECT
                    vt.*,
                    a.id AS agenda_ref_id,
                    a.status AS agenda_status,
                    a.prioridade AS agenda_prioridade
                FROM visitas_tecnicas vt
                LEFT JOIN agendas a ON a.id = vt.agenda_id
                WHERE vt.id = :id
                LIMIT 1
                FOR UPDATE
            ");
            $stmt->bindValue(':id', $visitaId, PDO::PARAM_INT);
            $stmt->execute();
            $visita = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$visita) {
                throw new RuntimeException('Visita técnica não encontrada.');
            }

            if (!$usuarioAdministrador && (int)$visita['usuario_id'] !== $usuarioId) {
                throw new RuntimeException('Esta visita técnica está atribuída a outro TST.');
            }

            $statusVisita = strtoupper((string)$visita['status']);
            if (in_array($statusVisita, ['CANCELADA', 'EXCLUIDA'], true)) {
                throw new RuntimeException('Não é possível iniciar o check-list de uma visita cancelada ou excluída.');
            }

            $stmt = $this->db->prepare("
                SELECT *
                FROM checklists_visita
                WHERE visita_id = :visita_id
                LIMIT 1
                FOR UPDATE
            ");
            $stmt->bindValue(':visita_id', $visitaId, PDO::PARAM_INT);
            $stmt->execute();
            $checklistExistente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($checklistExistente) {
                $statusChecklist = strtoupper((string)$checklistExistente['status']);

                if ($statusChecklist === 'CONCLUIDO') {
                    $this->db->commit();
                    return (int)$checklistExistente['id'];
                }

                if ($statusChecklist !== 'EM_ANDAMENTO') {
                    $stmt = $this->db->prepare("
                        UPDATE checklists_visita
                        SET status = 'EM_ANDAMENTO',
                            data_inicio = COALESCE(data_inicio, NOW()),
                            data_fim = NULL
                        WHERE id = :id
                    ");
                    $stmt->execute([':id' => (int)$checklistExistente['id']]);
                }

                $this->sincronizarInicioVisita(
                    $visitaId,
                    $usuarioId,
                    $statusVisita
                );

                $this->db->commit();
                return (int)$checklistExistente['id'];
            }

            if (!in_array($statusVisita, self::STATUS_VISITA_INICIAVEIS, true)) {
                throw new RuntimeException('Esta visita técnica não está disponível para iniciar o check-list.');
            }

            $stmt = $this->db->prepare("
                INSERT INTO checklists_visita (
                    visita_id,
                    empresa_id,
                    unidade_id,
                    usuario_id,
                    prioridade,
                    responsavel_acompanhamento,
                    status,
                    data_inicio
                ) VALUES (
                    :visita_id,
                    :empresa_id,
                    :unidade_id,
                    :usuario_id,
                    :prioridade,
                    :responsavel_acompanhamento,
                    'EM_ANDAMENTO',
                    NOW()
                )
            ");
            $stmt->bindValue(':visita_id', $visitaId, PDO::PARAM_INT);
            $stmt->bindValue(':empresa_id', (int)$visita['empresa_id'], PDO::PARAM_INT);

            if (!empty($visita['unidade_id'])) {
                $stmt->bindValue(':unidade_id', (int)$visita['unidade_id'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':unidade_id', null, PDO::PARAM_NULL);
            }

            // O responsável técnico permanece sendo o TST designado na Agenda.
            $stmt->bindValue(':usuario_id', (int)$visita['usuario_id'], PDO::PARAM_INT);
            $stmt->bindValue(
                ':prioridade',
                strtoupper((string)($visita['agenda_prioridade'] ?? 'PADRAO'))
            );
            $stmt->bindValue(
                ':responsavel_acompanhamento',
                $this->textoOuNull($visita['responsavel_acompanhamento'] ?? null)
            );
            $stmt->execute();

            $checklistId = (int)$this->db->lastInsertId();
            $this->sincronizarInicioVisita($visitaId, $usuarioId, $statusVisita);

            if (!empty($visita['agenda_ref_id'])) {
                $this->registrarHistoricoAgendaInicio(
                    (int)$visita['agenda_ref_id'],
                    $usuarioId,
                    $visitaId,
                    $checklistId,
                    $visita['agenda_status'] ?? null
                );
            }

            $this->db->commit();
            return $checklistId;
        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $erro;
        }
    }

    public function listarTodos(
        int $usuarioId,
        string $tipoUsuario,
        string $aba = 'andamento',
        array $filtros = []
    ): array {
        $where = ['1 = 1'];
        $parametros = [];

        if (!$this->usuarioAdministrador($tipoUsuario)) {
            $where[] = 'cv.usuario_id = :usuario_id';
            $parametros[':usuario_id'] = $usuarioId;
        }

        $where[] = match ($aba) {
            'concluidos' => "cv.status = 'CONCLUIDO'",
            'cancelados' => "cv.status = 'CANCELADO'",
            'todos' => '1 = 1',
            default => "cv.status IN ('ABERTO', 'EM_ANDAMENTO')",
        };

        $prioridade = strtoupper(trim((string)($filtros['prioridade'] ?? '')));
        if (in_array($prioridade, ['PADRAO', 'URGENTE', 'CRITICA'], true)) {
            $where[] = 'cv.prioridade = :prioridade';
            $parametros[':prioridade'] = $prioridade;
        }

        $dataInicio = trim((string)($filtros['data_inicio'] ?? ''));
        if ($dataInicio !== '') {
            $where[] = 'vt.data_visita >= :data_inicio';
            $parametros[':data_inicio'] = $dataInicio;
        }

        $dataFim = trim((string)($filtros['data_fim'] ?? ''));
        if ($dataFim !== '') {
            $where[] = 'vt.data_visita <= :data_fim';
            $parametros[':data_fim'] = $dataFim;
        }

        $sql = "
            SELECT
                cv.*,
                vt.status AS visita_status,
                vt.data_visita,
                vt.hora_visita,
                vt.objetivo,
                vt.observacoes AS visita_observacoes,
                vt.iniciado_em AS visita_iniciada_em,
                vt.finalizado_em AS visita_finalizada_em,
                a.hora_fim,
                a.titulo AS agenda_titulo,
                e.razao_social AS empresa_nome,
                e.nome_fantasia AS empresa_fantasia,
                e.cnpj AS empresa_cnpj,
                un.nome AS unidade_nome,
                un.nome_fantasia AS unidade_fantasia,
                un.cnpj AS unidade_cnpj,
                tec.nome AS tecnico_nome,
                (
                    SELECT COUNT(*)
                    FROM ghes g
                    WHERE g.checklist_id = cv.id
                      AND g.ativo = 1
                ) AS total_ghes,
                (
                    SELECT COUNT(*)
                    FROM ghe_riscos gr
                    INNER JOIN ghes g2 ON g2.id = gr.ghe_id
                    WHERE g2.checklist_id = cv.id
                      AND g2.ativo = 1
                ) AS total_riscos
            FROM checklists_visita cv
            INNER JOIN visitas_tecnicas vt ON vt.id = cv.visita_id
            INNER JOIN empresas e ON e.id = cv.empresa_id
            INNER JOIN usuarios tec ON tec.id = cv.usuario_id
            LEFT JOIN agendas a ON a.id = vt.agenda_id
            LEFT JOIN unidades un ON un.id = cv.unidade_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY
                CASE cv.status
                    WHEN 'EM_ANDAMENTO' THEN 1
                    WHEN 'ABERTO' THEN 2
                    WHEN 'CONCLUIDO' THEN 3
                    WHEN 'CANCELADO' THEN 4
                    ELSE 5
                END,
                CASE cv.prioridade
                    WHEN 'CRITICA' THEN 1
                    WHEN 'URGENTE' THEN 2
                    ELSE 3
                END,
                vt.data_visita DESC,
                vt.hora_visita DESC,
                cv.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obterIndicadores(int $usuarioId, string $tipoUsuario): array
    {
        $where = [];
        $parametros = [];

        if (!$this->usuarioAdministrador($tipoUsuario)) {
            $where[] = 'cv.usuario_id = :usuario_id';
            $parametros[':usuario_id'] = $usuarioId;
        }

        $sql = "
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN cv.status IN ('ABERTO', 'EM_ANDAMENTO') THEN 1 ELSE 0 END) AS andamento,
                SUM(CASE WHEN cv.status = 'CONCLUIDO' THEN 1 ELSE 0 END) AS concluidos,
                SUM(CASE WHEN cv.status = 'CANCELADO' THEN 1 ELSE 0 END) AS cancelados
            FROM checklists_visita cv
        ";

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int)($resultado['total'] ?? 0),
            'andamento' => (int)($resultado['andamento'] ?? 0),
            'concluidos' => (int)($resultado['concluidos'] ?? 0),
            'cancelados' => (int)($resultado['cancelados'] ?? 0),
        ];
    }

    public function buscarDadosTela(int $checklistId): ?array
    {
        $sql = "
            SELECT
                cv.*,
                vt.agenda_id,
                vt.data_visita,
                vt.hora_visita,
                vt.objetivo,
                vt.observacoes,
                vt.status AS visita_status,
                vt.iniciado_em AS visita_iniciada_em,
                vt.finalizado_em AS visita_finalizada_em,
                a.hora_fim,
                a.titulo AS agenda_titulo,
                a.status AS agenda_status,
                a.prioridade AS agenda_prioridade,
                e.codigo AS empresa_codigo,
                e.razao_social AS empresa_nome,
                e.nome_fantasia AS empresa_fantasia,
                e.cnpj AS empresa_cnpj,
                e.endereco AS empresa_endereco,
                e.logradouro AS empresa_logradouro,
                e.numero AS empresa_numero,
                e.complemento AS empresa_complemento,
                e.bairro AS empresa_bairro,
                e.cidade AS empresa_cidade,
                e.estado AS empresa_uf,
                e.cep AS empresa_cep,
                un.nome AS unidade_nome,
                un.razao_social AS unidade_razao_social,
                un.nome_fantasia AS unidade_fantasia,
                un.cnpj AS unidade_cnpj,
                un.endereco AS unidade_endereco,
                un.numero AS unidade_numero,
                un.complemento AS unidade_complemento,
                un.bairro AS unidade_bairro,
                un.cidade AS unidade_cidade,
                un.estado AS unidade_uf,
                un.cep AS unidade_cep,
                tec.nome AS tecnico_nome,
                tec.registro_profissional AS tecnico_registro
            FROM checklists_visita cv
            INNER JOIN visitas_tecnicas vt ON vt.id = cv.visita_id
            INNER JOIN empresas e ON e.id = cv.empresa_id
            INNER JOIN usuarios tec ON tec.id = cv.usuario_id
            LEFT JOIN agendas a ON a.id = vt.agenda_id
            LEFT JOIN unidades un ON un.id = cv.unidade_id
            WHERE cv.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $checklistId, PDO::PARAM_INT);
        $stmt->execute();
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        return $registro ?: null;
    }

    public function estruturaOperacionalDisponivel(): bool
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
              AND table_name IN ('funcionarios', 'ghes', 'ghe_cargos', 'ghe_riscos')
        ");
        $tabelas = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = 'checklists_visita'
              AND column_name IN ('ultima_aba', 'atualizado_em')
        ");

        return $tabelas === 4 && (int)$stmt->fetchColumn() === 2;
    }

    public function listarHierarquiaContexto(array $checklist): array
    {
        $sql = "
            SELECT
                h.id,
                h.empresa_id,
                h.unidade_id,
                h.setor_id,
                h.cargo_id,
                un.nome AS unidade_nome,
                s.codigo AS setor_codigo,
                s.nome AS setor_nome,
                c.codigo AS cargo_codigo,
                c.nome AS cargo_nome,
                c.cbo,
                COUNT(f.id) AS total_funcionarios,
                SUM(CASE WHEN f.ativo = 1 THEN 1 ELSE 0 END) AS funcionarios_ativos
            FROM hierarquias h
            INNER JOIN unidades un ON un.id = h.unidade_id
            INNER JOIN setores s ON s.id = h.setor_id
            INNER JOIN cargos c ON c.id = h.cargo_id
            LEFT JOIN funcionarios f ON f.hierarquia_id = h.id
            WHERE h.empresa_id = :empresa_id
        ";
        $params = [':empresa_id' => (int)$checklist['empresa_id']];

        if (!empty($checklist['unidade_id'])) {
            $sql .= ' AND h.unidade_id = :unidade_id';
            $params[':unidade_id'] = (int)$checklist['unidade_id'];
        }

        $sql .= "
            GROUP BY h.id, h.empresa_id, h.unidade_id, h.setor_id, h.cargo_id,
                     un.nome, s.codigo, s.nome, c.codigo, c.nome, c.cbo
            ORDER BY un.nome, s.nome, c.nome
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarSetoresAtivos(): array
    {
        return $this->db->query(
            'SELECT id, codigo, nome FROM setores WHERE ativo = 1 ORDER BY nome'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarCargosAtivos(): array
    {
        return $this->db->query(
            'SELECT id, codigo, nome, cbo FROM cargos WHERE ativo = 1 ORDER BY nome'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarRiscosAtivos(): array
    {
        return $this->db->query("
            SELECT id, codigo, categoria, nome, tipo_avaliacao,
                   unidade_medida, exige_quantificacao, limite_nr15, nivel_acao
            FROM riscos
            WHERE ativo = 1
            ORDER BY categoria, nome
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvarLinhaHierarquia(int $checklistId, array $dados): int
    {
        $checklist = $this->buscarContextoOperacional($checklistId);
        $this->garantirEditavel($checklist);

        if (empty($checklist['unidade_id'])) {
            throw new RuntimeException('Defina uma unidade no agendamento antes de montar a hierarquia.');
        }

        try {
            $this->db->beginTransaction();

            $setorId = (int)($dados['setor_id'] ?? 0);
            if ($setorId > 0 && !$this->cadastroAtivoExiste('setores', $setorId)) {
                throw new RuntimeException('O setor selecionado não está disponível.');
            }
            if ($setorId <= 0) {
                $nomeSetor = trim((string)($dados['novo_setor'] ?? ''));
                if ($nomeSetor === '') {
                    throw new RuntimeException('Selecione ou informe um setor.');
                }
                $setorId = $this->localizarOuCriarSetor($nomeSetor, $dados['setor_descricao'] ?? null);
            }

            $cargoId = (int)($dados['cargo_id'] ?? 0);
            if ($cargoId > 0 && !$this->cadastroAtivoExiste('cargos', $cargoId)) {
                throw new RuntimeException('O cargo selecionado não está disponível.');
            }
            if ($cargoId <= 0) {
                $nomeCargo = trim((string)($dados['novo_cargo'] ?? ''));
                if ($nomeCargo === '') {
                    throw new RuntimeException('Selecione ou informe um cargo.');
                }
                $cargoId = $this->localizarOuCriarCargo(
                    $nomeCargo,
                    $dados['cbo'] ?? null,
                    $dados['cargo_descricao'] ?? null
                );
            }

            $stmt = $this->db->prepare("
                SELECT id FROM hierarquias
                WHERE empresa_id = :empresa_id
                  AND unidade_id = :unidade_id
                  AND setor_id = :setor_id
                  AND cargo_id = :cargo_id
                LIMIT 1
            ");
            $params = [
                ':empresa_id' => (int)$checklist['empresa_id'],
                ':unidade_id' => (int)$checklist['unidade_id'],
                ':setor_id' => $setorId,
                ':cargo_id' => $cargoId,
            ];
            $stmt->execute($params);
            $existente = $stmt->fetchColumn();

            if ($existente) {
                $hierarquiaId = (int)$existente;
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO hierarquias (empresa_id, unidade_id, setor_id, cargo_id)
                    VALUES (:empresa_id, :unidade_id, :setor_id, :cargo_id)
                ");
                $stmt->execute($params);
                $hierarquiaId = (int)$this->db->lastInsertId();
            }

            $this->atualizarAbaInterno($checklistId, 'hierarquia');
            $this->db->commit();
            return $hierarquiaId;
        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $erro;
        }
    }

    public function marcarUltimaAba(int $checklistId, string $aba): void
    {
        $permitidas = ['dados', 'hierarquia', 'funcionarios', 'ghe-riscos'];
        if (!in_array($aba, $permitidas, true)) {
            return;
        }
        $this->atualizarAbaInterno($checklistId, $aba);
    }

    public function calcularProgresso(int $checklistId, array $checklist): array
    {
        $hierarquias = (int)$this->contarContexto('hierarquias', $checklist);
        $funcionarios = (int)$this->contarContexto('funcionarios', $checklist, 'ativo = 1');

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM ghes WHERE checklist_id = :id AND ativo = 1');
        $stmt->execute([':id' => $checklistId]);
        $ghes = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM ghe_riscos gr
            INNER JOIN ghes g ON g.id = gr.ghe_id
            WHERE g.checklist_id = :id AND g.ativo = 1
        ");
        $stmt->execute([':id' => $checklistId]);
        $riscos = (int)$stmt->fetchColumn();

        $percentual = 10;
        if ($hierarquias > 0) $percentual += 25;
        if ($funcionarios > 0) $percentual += 20;
        if ($ghes > 0) $percentual += 20;
        if ($riscos > 0) $percentual += 25;
        if (strtoupper((string)$checklist['status']) === 'CONCLUIDO') $percentual = 100;

        return compact('hierarquias', 'funcionarios', 'ghes', 'riscos', 'percentual');
    }

    private function buscarContextoOperacional(int $checklistId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM checklists_visita WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $checklistId]);
        $checklist = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$checklist) {
            throw new RuntimeException('Check-list não encontrado.');
        }
        return $checklist;
    }

    private function garantirEditavel(array $checklist): void
    {
        if (in_array(strtoupper((string)$checklist['status']), ['CONCLUIDO', 'CANCELADO'], true)) {
            throw new RuntimeException('Este check-list não permite novas alterações.');
        }
    }

    private function cadastroAtivoExiste(string $tabela, int $id): bool
    {
        if (!in_array($tabela, ['setores', 'cargos'], true)) {
            return false;
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$tabela} WHERE id = :id AND ativo = 1");
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function localizarOuCriarSetor(string $nome, mixed $descricao): int
    {
        $stmt = $this->db->prepare('SELECT id FROM setores WHERE nome = :nome LIMIT 1');
        $stmt->execute([':nome' => $nome]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;

        $stmt = $this->db->prepare("
            INSERT INTO setores (codigo, nome, descricao, ativo)
            VALUES (:codigo, :nome, :descricao, 1)
        ");
        $stmt->execute([
            ':codigo' => 'SET-' . strtoupper(bin2hex(random_bytes(4))),
            ':nome' => $nome,
            ':descricao' => $this->textoOuNull($descricao),
        ]);
        return (int)$this->db->lastInsertId();
    }

    private function localizarOuCriarCargo(string $nome, mixed $cbo, mixed $descricao): int
    {
        $stmt = $this->db->prepare('SELECT id FROM cargos WHERE nome = :nome LIMIT 1');
        $stmt->execute([':nome' => $nome]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;

        $stmt = $this->db->prepare("
            INSERT INTO cargos (codigo, nome, cbo, descricao, ativo)
            VALUES (:codigo, :nome, :cbo, :descricao, 1)
        ");
        $stmt->execute([
            ':codigo' => 'CAR-' . strtoupper(bin2hex(random_bytes(4))),
            ':nome' => $nome,
            ':cbo' => $this->textoOuNull($cbo),
            ':descricao' => $this->textoOuNull($descricao),
        ]);
        return (int)$this->db->lastInsertId();
    }

    private function atualizarAbaInterno(int $checklistId, string $aba): void
    {
        $stmt = $this->db->prepare("
            UPDATE checklists_visita
            SET ultima_aba = :aba, atualizado_em = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':aba' => $aba, ':id' => $checklistId]);
    }

    private function contarContexto(string $tabela, array $checklist, ?string $extra = null): int
    {
        if (!in_array($tabela, ['hierarquias', 'funcionarios'], true)) {
            return 0;
        }
        $sql = "SELECT COUNT(*) FROM {$tabela} WHERE empresa_id = :empresa_id";
        $params = [':empresa_id' => (int)$checklist['empresa_id']];
        if (!empty($checklist['unidade_id'])) {
            $sql .= ' AND unidade_id = :unidade_id';
            $params[':unidade_id'] = (int)$checklist['unidade_id'];
        }
        if ($extra !== null) $sql .= ' AND ' . $extra;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function usuarioPodeAcessar(
        array $checklist,
        int $usuarioId,
        string $tipoUsuario
    ): bool {
        if ($this->usuarioAdministrador($tipoUsuario)) {
            return true;
        }

        return (int)($checklist['usuario_id'] ?? 0) === $usuarioId;
    }

    private function sincronizarInicioVisita(
        int $visitaId,
        int $usuarioId,
        string $statusAnterior
    ): void {
        $stmt = $this->db->prepare("
            UPDATE visitas_tecnicas
            SET status = 'CHECKLIST_INICIADO',
                iniciado_em = COALESCE(iniciado_em, NOW()),
                atualizado_por = :usuario_id,
                atualizado_em = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':id' => $visitaId,
        ]);

        if ($statusAnterior !== 'CHECKLIST_INICIADO') {
            $stmt = $this->db->prepare("
                INSERT INTO visita_historico (
                    visita_id,
                    usuario_id,
                    acao,
                    status_anterior,
                    status_novo,
                    motivo
                ) VALUES (
                    :visita_id,
                    :usuario_id,
                    'INICIO_CHECKLIST',
                    :status_anterior,
                    'CHECKLIST_INICIADO',
                    'Check-list iniciado pelo técnico responsável.'
                )
            ");
            $stmt->execute([
                ':visita_id' => $visitaId,
                ':usuario_id' => $usuarioId,
                ':status_anterior' => $statusAnterior,
            ]);
        }
    }

    private function registrarHistoricoAgendaInicio(
        int $agendaId,
        int $usuarioId,
        int $visitaId,
        int $checklistId,
        ?string $statusAgenda
    ): void {
        $dadosAnteriores = json_encode([
            'status_agenda' => $statusAgenda,
            'status_visita' => 'AGENDADA',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $dadosNovos = json_encode([
            'status_agenda' => $statusAgenda,
            'status_visita' => 'CHECKLIST_INICIADO',
            'visita_id' => $visitaId,
            'checklist_id' => $checklistId,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $stmt = $this->db->prepare("
            INSERT INTO agenda_historico (
                agenda_id,
                usuario_id,
                acao,
                descricao,
                dados_anteriores,
                dados_novos
            ) VALUES (
                :agenda_id,
                :usuario_id,
                'ALTERADA',
                'Check-list iniciado. A visita passou para Em andamento.',
                :dados_anteriores,
                :dados_novos
            )
        ");
        $stmt->execute([
            ':agenda_id' => $agendaId,
            ':usuario_id' => $usuarioId,
            ':dados_anteriores' => $dadosAnteriores,
            ':dados_novos' => $dadosNovos,
        ]);
    }

    private function usuarioAdministrador(string $tipoUsuario): bool
    {
        return in_array(
            strtoupper(trim($tipoUsuario)),
            ['ADMIN', 'ADMINISTRADOR'],
            true
        );
    }

    private function textoOuNull(?string $valor): ?string
    {
        $valor = trim((string)$valor);
        return $valor !== '' ? $valor : null;
    }
}
