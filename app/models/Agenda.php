<?php

require_once __DIR__ . '/../../core/Database.php';

class Agenda
{
    private PDO $db;

    private const STATUS_VALIDOS = [
        'AGENDADO',
        'CONFIRMADO',
        'REAGENDADO',
        'CANCELADO',
        'CONCLUIDO',
        'EXCLUIDO',
    ];

    private const PRIORIDADES_VALIDAS = [
        'PADRAO',
        'URGENTE',
        'CRITICA',
    ];

    private const STATUS_VISITA_BLOQUEIAM_EDICAO = [
        'EM_ANDAMENTO',
        'CHECKLIST_INICIADO',
        'FINALIZADA',
        'CANCELADA',
        'EXCLUIDA',
    ];

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function listarTodos(array $filtros = []): array
    {
        $parametros = [];
        $sql = $this->selectBase() . $this->montarWhere($filtros, $parametros) . "
            ORDER BY a.data_agendada DESC, a.hora_inicio DESC, a.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function listarPaginado(array $filtros, int $pagina, int $porPagina): array
    {
        $pagina = max(1, $pagina);
        $porPagina = max(1, $porPagina);
        $offset = ($pagina - 1) * $porPagina;
        $parametros = [];

        $sql = $this->selectBase() . $this->montarWhere($filtros, $parametros) . "
            ORDER BY a.data_agendada DESC, a.hora_inicio DESC, a.id DESC
            LIMIT :limite OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $this->bindParametros($stmt, $parametros);
        $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contarTodos(array $filtros = []): int
    {
        $parametros = [];
        $sql = 'SELECT COUNT(*) FROM agendas a' . $this->montarWhere($filtros, $parametros);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return (int)$stmt->fetchColumn();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = $this->selectBase() . ' WHERE a.id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $registro = $stmt->fetch();

        return $registro ?: null;
    }

    public function buscarHistorico(int $agendaId): array
    {
        $sql = "
            SELECT
                ah.*,
                u.nome AS usuario_nome
            FROM agenda_historico ah
            LEFT JOIN usuarios u ON u.id = ah.usuario_id
            WHERE ah.agenda_id = :agenda_id
            ORDER BY ah.criado_em DESC, ah.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':agenda_id', $agendaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function validarReferencias(array $dados): array
    {
        $erros = [];

        if (!$this->registroExiste('empresas', (int)($dados['empresa_id'] ?? 0), false)) {
            $erros[] = 'A empresa selecionada não está disponível.';
        }

        $unidadeId = !empty($dados['unidade_id']) ? (int)$dados['unidade_id'] : null;
        if ($unidadeId !== null) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM unidades WHERE id = :id AND (empresa_id = :empresa_id OR empresa_id IS NULL)'
            );
            $stmt->execute([
                ':id' => $unidadeId,
                ':empresa_id' => (int)$dados['empresa_id'],
            ]);

            if ((int)$stmt->fetchColumn() === 0) {
                $erros[] = 'A unidade selecionada não pertence à empresa informada ou está inativa.';
            }
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM usuarios
             WHERE id = :id AND ativo = 1 AND tipo IN ('ADMIN', 'TECNICO')"
        );
        $stmt->execute([':id' => (int)($dados['tecnico_id'] ?? 0)]);
        if ((int)$stmt->fetchColumn() === 0) {
            $erros[] = 'O técnico responsável não está disponível.';
        }

        $veiculoId = !empty($dados['veiculo_id']) ? (int)$dados['veiculo_id'] : null;
        if ($veiculoId !== null && !$this->registroExiste('veiculos', $veiculoId, false)) {
            $erros[] = 'O veículo selecionado não está disponível.';
        }

        return $erros;
    }

    /**
     * Cria a agenda, a visita técnica e os históricos em uma única transação.
     */
    public function salvar(array $dados, int $usuarioId): int|false
    {
        $prioridade = strtoupper($dados['prioridade'] ?? 'PADRAO');
        if (!in_array($prioridade, self::PRIORIDADES_VALIDAS, true)) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO agendas (
                    empresa_id, unidade_id, tecnico_id, veiculo_id,
                    data_agendada, hora_inicio, hora_fim, titulo,
                    objetivo, observacoes, responsavel_acompanhamento,
                    prioridade, status, criado_por, criado_em
                ) VALUES (
                    :empresa_id, :unidade_id, :tecnico_id, :veiculo_id,
                    :data_agendada, :hora_inicio, :hora_fim, :titulo,
                    :objetivo, :observacoes, :responsavel_acompanhamento,
                    :prioridade, 'AGENDADO', :criado_por, NOW()
                )
            ");

            $stmt->execute($this->parametrosAgenda($dados, $usuarioId, false));
            $agendaId = (int)$this->db->lastInsertId();

            $visitaId = $this->criarVisita($agendaId, $dados, $usuarioId);

            $stmt = $this->db->prepare(
                'UPDATE agendas SET visita_tecnica_id = :visita_id WHERE id = :agenda_id'
            );
            $stmt->execute([
                ':visita_id' => $visitaId,
                ':agenda_id' => $agendaId,
            ]);

            $novo = $this->buscarRegistroTransacao($agendaId);
            $this->registrarHistoricoAgenda(
                $agendaId,
                $usuarioId,
                'CRIADA',
                'Agendamento criado.',
                null,
                null,
                $novo
            );
            $this->registrarHistoricoAgenda(
                $agendaId,
                $usuarioId,
                'VISITA_GERADA',
                'Visita técnica gerada automaticamente a partir do agendamento.',
                null,
                null,
                ['visita_tecnica_id' => $visitaId]
            );
            $this->registrarHistoricoVisita(
                $visitaId,
                $usuarioId,
                'CRIADA_PELA_AGENDA',
                null,
                'AGENDADA',
                null
            );

            $this->db->commit();
            return $agendaId;
        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $erro;
        }
    }

    /**
     * Atualiza dados administrativos sem alterar data ou horário.
     */
    public function atualizar(int $id, array $dados, int $usuarioId): bool
    {
        $prioridade = strtoupper($dados['prioridade'] ?? 'PADRAO');
        if (!in_array($prioridade, self::PRIORIDADES_VALIDAS, true)) {
            return false;
        }

        try {
            $this->db->beginTransaction();
            $anterior = $this->buscarRegistroTransacao($id, true);

            if (!$anterior || !$this->agendaEditavel($anterior)) {
                $this->db->rollBack();
                return false;
            }

            if ($this->agendaFoiReagendada($anterior, $dados)) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("
                UPDATE agendas SET
                    empresa_id = :empresa_id,
                    unidade_id = :unidade_id,
                    tecnico_id = :tecnico_id,
                    veiculo_id = :veiculo_id,
                    titulo = :titulo,
                    objetivo = :objetivo,
                    observacoes = :observacoes,
                    responsavel_acompanhamento = :responsavel_acompanhamento,
                    prioridade = :prioridade,
                    atualizado_por = :atualizado_por,
                    atualizado_em = NOW()
                WHERE id = :id
                  AND status NOT IN ('CANCELADO', 'CONCLUIDO', 'EXCLUIDO')
            ");

            $params = $this->parametrosAgenda($dados, $usuarioId, true);
            $params[':id'] = $id;
            unset(
                $params[':data_agendada'],
                $params[':hora_inicio'],
                $params[':hora_fim']
            );
            $stmt->execute($params);

            $visitaId = $this->garantirVisita($id, $anterior, $dados, $usuarioId);
            $this->sincronizarDadosVisita($visitaId, $dados, $usuarioId);

            $novo = $this->buscarRegistroTransacao($id);
            $this->registrarHistoricoAgenda(
                $id,
                $usuarioId,
                'ALTERADA',
                'Dados do agendamento atualizados.',
                null,
                $anterior,
                $novo
            );

            $this->db->commit();
            return true;
        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $erro;
        }
    }

    public function reagendar(
        int $id,
        string $data,
        string $horaInicio,
        string $horaFim,
        string $motivo,
        int $usuarioId
    ): bool {
        $motivo = trim($motivo);
        if ($motivo === '') {
            return false;
        }

        try {
            $this->db->beginTransaction();
            $anterior = $this->buscarRegistroTransacao($id, true);

            if (!$anterior || !$this->agendaEditavel($anterior)) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("
                UPDATE agendas SET
                    data_agendada = :data_agendada,
                    hora_inicio = :hora_inicio,
                    hora_fim = :hora_fim,
                    status = 'REAGENDADO',
                    atualizado_por = :usuario_id,
                    atualizado_em = NOW()
                WHERE id = :id
                  AND status NOT IN ('CANCELADO', 'CONCLUIDO', 'EXCLUIDO')
            ");
            $stmt->execute([
                ':data_agendada' => $data,
                ':hora_inicio' => $horaInicio,
                ':hora_fim' => $horaFim,
                ':usuario_id' => $usuarioId,
                ':id' => $id,
            ]);

            $visitaId = !empty($anterior['visita_tecnica_id'])
                ? (int)$anterior['visita_tecnica_id']
                : $this->garantirVisita($id, $anterior, [
                    'empresa_id' => $anterior['empresa_id'],
                    'unidade_id' => $anterior['unidade_id'],
                    'tecnico_id' => $anterior['tecnico_id'],
                    'veiculo_id' => $anterior['veiculo_id'],
                    'data_agendada' => $data,
                    'hora_inicio' => $horaInicio,
                    'objetivo' => $anterior['objetivo'],
                    'observacoes' => $anterior['observacoes'],
                    'responsavel_acompanhamento' => $anterior['responsavel_acompanhamento'],
                ], $usuarioId);

            $stmt = $this->db->prepare("
                UPDATE visitas_tecnicas SET
                    data_visita = :data_visita,
                    hora_visita = :hora_visita,
                    status = 'AGENDADA',
                    atualizado_por = :usuario_id,
                    atualizado_em = NOW()
                WHERE id = :id
                  AND status NOT IN ('EM_ANDAMENTO', 'CHECKLIST_INICIADO', 'FINALIZADA', 'CANCELADA', 'EXCLUIDA')
            ");
            $stmt->execute([
                ':data_visita' => $data,
                ':hora_visita' => $horaInicio,
                ':usuario_id' => $usuarioId,
                ':id' => $visitaId,
            ]);

            $novo = $this->buscarRegistroTransacao($id);
            $this->registrarHistoricoAgenda(
                $id,
                $usuarioId,
                'REAGENDADA',
                'Data ou horário do agendamento alterados.',
                $motivo,
                $anterior,
                $novo
            );
            $this->registrarHistoricoVisita(
                $visitaId,
                $usuarioId,
                'REAGENDADA',
                $anterior['visita_status'] ?? null,
                'AGENDADA',
                $motivo
            );

            $this->db->commit();
            return true;
        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $erro;
        }
    }

    public function cancelar(int $id, string $motivo, int $usuarioId): bool
    {
        return $this->alterarStatusComMotivo(
            $id,
            'CANCELADO',
            'CANCELADA',
            'CANCELADA',
            $motivo,
            $usuarioId
        );
    }

    public function excluir(int $id, string $motivo, int $usuarioId): bool
    {
        return $this->alterarStatusComMotivo(
            $id,
            'EXCLUIDO',
            'EXCLUIDA',
            'EXCLUIDA',
            $motivo,
            $usuarioId
        );
    }

    /**
     * A agenda só pode ser concluída depois da finalização da visita/check-list.
     */
    public function concluir(int $id, int $usuarioId): bool
    {
        try {
            $this->db->beginTransaction();
            $anterior = $this->buscarRegistroTransacao($id, true);

            if (
                !$anterior ||
                strtoupper((string)($anterior['visita_status'] ?? '')) !== 'FINALIZADA' ||
                in_array(strtoupper((string)$anterior['status']), ['CANCELADO', 'CONCLUIDO', 'EXCLUIDO'], true)
            ) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("
                UPDATE agendas SET
                    status = 'CONCLUIDO',
                    atualizado_por = :usuario_id,
                    atualizado_em = NOW()
                WHERE id = :id
            ");
            $stmt->execute([':usuario_id' => $usuarioId, ':id' => $id]);

            $novo = $this->buscarRegistroTransacao($id);
            $this->registrarHistoricoAgenda(
                $id,
                $usuarioId,
                'CONCLUIDA',
                'Agendamento concluído após a finalização da visita técnica.',
                null,
                $anterior,
                $novo
            );

            $this->db->commit();
            return true;
        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $erro;
        }
    }

    public function existeConflitoIntervalo(
        int $tecnicoId,
        ?int $veiculoId,
        string $dataAgendada,
        string $horaInicio,
        string $horaFim,
        ?int $ignorarId = null
    ): ?array {
        $sql = "
            SELECT
                a.id, a.tecnico_id, a.veiculo_id,
                a.data_agendada, a.hora_inicio, a.hora_fim,
                t.nome AS tecnico_nome,
                v.modelo AS veiculo_modelo,
                v.placa AS veiculo_placa,
                CASE WHEN a.tecnico_id = :tecnico_id_case THEN 1 ELSE 0 END AS conflito_tecnico,
                CASE
                    WHEN :veiculo_id_case IS NOT NULL AND a.veiculo_id = :veiculo_id_comparacao
                    THEN 1 ELSE 0
                END AS conflito_veiculo
            FROM agendas a
            INNER JOIN usuarios t ON t.id = a.tecnico_id
            LEFT JOIN veiculos v ON v.id = a.veiculo_id
            WHERE a.data_agendada = :data_agendada
              AND a.status NOT IN ('CANCELADO', 'CONCLUIDO', 'EXCLUIDO')
              AND (a.tecnico_id = :tecnico_id_filtro
        ";

        if ($veiculoId !== null) {
            $sql .= ' OR a.veiculo_id = :veiculo_id_filtro';
        }

        $sql .= ")
              AND :hora_inicio < a.hora_fim
              AND :hora_fim > a.hora_inicio
        ";

        if ($ignorarId !== null) {
            $sql .= ' AND a.id <> :ignorar_id';
        }

        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tecnico_id_case', $tecnicoId, PDO::PARAM_INT);
        $stmt->bindValue(':tecnico_id_filtro', $tecnicoId, PDO::PARAM_INT);
        $stmt->bindValue(':data_agendada', $dataAgendada);
        $stmt->bindValue(':hora_inicio', $horaInicio);
        $stmt->bindValue(':hora_fim', $horaFim);

        if ($veiculoId !== null) {
            $stmt->bindValue(':veiculo_id_case', $veiculoId, PDO::PARAM_INT);
            $stmt->bindValue(':veiculo_id_comparacao', $veiculoId, PDO::PARAM_INT);
            $stmt->bindValue(':veiculo_id_filtro', $veiculoId, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':veiculo_id_case', null, PDO::PARAM_NULL);
            $stmt->bindValue(':veiculo_id_comparacao', null, PDO::PARAM_NULL);
        }

        if ($ignorarId !== null) {
            $stmt->bindValue(':ignorar_id', $ignorarId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function eventosCalendario(array $filtros = []): array
    {
        return array_map(static function (array $item): array {
            $empresa = !empty($item['empresa_fantasia'])
                ? $item['empresa_fantasia']
                : ($item['empresa_nome'] ?? 'Empresa');

            $statusVisual = strtoupper((string)($item['status_visual'] ?? $item['status'] ?? ''));
            $evento = [
                'id' => (int)$item['id'],
                'title' => !empty($item['titulo']) ? $item['titulo'] : $empresa,
                'start' => $item['data_agendada'] . 'T' . $item['hora_inicio'],
                'end' => $item['data_agendada'] . 'T' . $item['hora_fim'],
                'url' => BASE_URL . '/agenda/visualizar/' . (int)$item['id'],
                'extendedProps' => [
                    'status' => $statusVisual,
                    'status_agenda' => $item['status'] ?? '',
                    'status_visita' => $item['visita_status'] ?? '',
                    'prioridade' => $item['prioridade'] ?? 'PADRAO',
                    'tecnico' => $item['tecnico_nome'] ?? '',
                    'unidade' => $item['unidade_nome'] ?? 'Matriz',
                ],
            ];

            // Mantém a identidade do calendário e acrescenta apenas o amarelo operacional.
            if ($statusVisual === 'EM_ANDAMENTO') {
                $evento['backgroundColor'] = '#f59e0b';
                $evento['borderColor'] = '#f59e0b';
                $evento['textColor'] = '#ffffff';
            }

            return $evento;
        }, $this->listarTodos($filtros));
    }

    public function obterIndicadores(array $filtros = []): array
    {
        $indicadores = [
            'total' => 0,
            'agendados' => 0,
            'confirmados' => 0,
            'reagendados' => 0,
            'cancelados' => 0,
            'concluidos' => 0,
        ];

        foreach ($this->listarTodos($filtros) as $item) {
            $indicadores['total']++;
            switch (strtoupper((string)($item['status'] ?? ''))) {
                case 'AGENDADO': $indicadores['agendados']++; break;
                case 'CONFIRMADO': $indicadores['confirmados']++; break;
                case 'REAGENDADO': $indicadores['reagendados']++; break;
                case 'CANCELADO': $indicadores['cancelados']++; break;
                case 'CONCLUIDO': $indicadores['concluidos']++; break;
            }
        }

        return $indicadores;
    }

    private function alterarStatusComMotivo(
        int $id,
        string $statusAgenda,
        string $acaoHistorico,
        string $statusVisita,
        string $motivo,
        int $usuarioId
    ): bool {
        $motivo = trim($motivo);
        if ($motivo === '') {
            return false;
        }

        try {
            $this->db->beginTransaction();
            $anterior = $this->buscarRegistroTransacao($id, true);

            if (!$anterior || !$this->agendaPodeSerCanceladaOuExcluida($anterior)) {
                $this->db->rollBack();
                return false;
            }

            if ($statusAgenda === 'CANCELADO') {
                $sql = "
                    UPDATE agendas SET
                        status = 'CANCELADO',
                        cancelado_por = :usuario_id,
                        motivo_cancelamento = :motivo,
                        cancelado_em = NOW(),
                        atualizado_por = :usuario_id_atualizacao,
                        atualizado_em = NOW()
                    WHERE id = :id
                ";
            } else {
                $sql = "
                    UPDATE agendas SET
                        status = 'EXCLUIDO',
                        excluido_por = :usuario_id,
                        motivo_exclusao = :motivo,
                        excluido_em = NOW(),
                        atualizado_por = :usuario_id_atualizacao,
                        atualizado_em = NOW()
                    WHERE id = :id
                ";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':usuario_id_atualizacao' => $usuarioId,
                ':motivo' => $motivo,
                ':id' => $id,
            ]);

            if (!empty($anterior['visita_tecnica_id'])) {
                $stmt = $this->db->prepare("
                    UPDATE visitas_tecnicas SET
                        status = :status,
                        atualizado_por = :usuario_id,
                        atualizado_em = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':status' => $statusVisita,
                    ':usuario_id' => $usuarioId,
                    ':id' => (int)$anterior['visita_tecnica_id'],
                ]);

                $this->registrarHistoricoVisita(
                    (int)$anterior['visita_tecnica_id'],
                    $usuarioId,
                    $acaoHistorico,
                    $anterior['visita_status'] ?? null,
                    $statusVisita,
                    $motivo
                );
            }

            $novo = $this->buscarRegistroTransacao($id);
            $this->registrarHistoricoAgenda(
                $id,
                $usuarioId,
                $acaoHistorico,
                $statusAgenda === 'CANCELADO'
                    ? 'Agendamento cancelado.'
                    : 'Agendamento excluído logicamente.',
                $motivo,
                $anterior,
                $novo
            );

            $this->db->commit();
            return true;
        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $erro;
        }
    }

    private function selectBase(): string
    {
        return "
            SELECT
                a.*,
                e.razao_social AS empresa_nome,
                e.nome_fantasia AS empresa_fantasia,
                un.nome AS unidade_nome,
                t.nome AS tecnico_nome,
                v.modelo AS veiculo_modelo,
                v.placa AS veiculo_placa,
                uc.nome AS criado_por_nome,
                ua.nome AS atualizado_por_nome,
                uca.nome AS cancelado_por_nome,
                ue.nome AS excluido_por_nome,
                vt.status AS visita_status,
                vt.iniciado_em AS visita_iniciada_em,
                vt.finalizado_em AS visita_finalizada_em,
                CASE
                    WHEN vt.status IN ('EM_ANDAMENTO', 'CHECKLIST_INICIADO') THEN 'EM_ANDAMENTO'
                    WHEN vt.status = 'FINALIZADA' THEN 'CONCLUIDO'
                    ELSE a.status
                END AS status_visual
            FROM agendas a
            INNER JOIN empresas e ON e.id = a.empresa_id
            INNER JOIN usuarios t ON t.id = a.tecnico_id
            LEFT JOIN unidades un ON un.id = a.unidade_id
            LEFT JOIN veiculos v ON v.id = a.veiculo_id
            LEFT JOIN usuarios uc ON uc.id = a.criado_por
            LEFT JOIN usuarios ua ON ua.id = a.atualizado_por
            LEFT JOIN usuarios uca ON uca.id = a.cancelado_por
            LEFT JOIN usuarios ue ON ue.id = a.excluido_por
            LEFT JOIN visitas_tecnicas vt ON vt.id = a.visita_tecnica_id
        ";
    }

    private function montarWhere(array $filtros, array &$parametros): string
    {
        $where = " WHERE a.status <> 'EXCLUIDO'";

        if (!empty($filtros['status']) && in_array(strtoupper($filtros['status']), self::STATUS_VALIDOS, true)) {
            $where .= ' AND a.status = :status';
            $parametros[':status'] = strtoupper($filtros['status']);
        }
        if (!empty($filtros['data_inicio'])) {
            $where .= ' AND a.data_agendada >= :data_inicio';
            $parametros[':data_inicio'] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $where .= ' AND a.data_agendada <= :data_fim';
            $parametros[':data_fim'] = $filtros['data_fim'];
        }
        if (!empty($filtros['empresa_id'])) {
            $where .= ' AND a.empresa_id = :empresa_id';
            $parametros[':empresa_id'] = (int)$filtros['empresa_id'];
        }
        if (!empty($filtros['tecnico_id'])) {
            $where .= ' AND a.tecnico_id = :tecnico_id';
            $parametros[':tecnico_id'] = (int)$filtros['tecnico_id'];
        }

        return $where;
    }

    private function bindParametros(PDOStatement $stmt, array $parametros): void
    {
        foreach ($parametros as $chave => $valor) {
            $stmt->bindValue($chave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    private function parametrosAgenda(array $dados, int $usuarioId, bool $atualizacao): array
    {
        $params = [
            ':empresa_id' => (int)$dados['empresa_id'],
            ':unidade_id' => !empty($dados['unidade_id']) ? (int)$dados['unidade_id'] : null,
            ':tecnico_id' => (int)$dados['tecnico_id'],
            ':veiculo_id' => !empty($dados['veiculo_id']) ? (int)$dados['veiculo_id'] : null,
            ':data_agendada' => $dados['data_agendada'],
            ':hora_inicio' => $dados['hora_inicio'],
            ':hora_fim' => $dados['hora_fim'],
            ':titulo' => $this->textoOuNull($dados['titulo'] ?? null),
            ':objetivo' => $this->textoOuNull($dados['objetivo'] ?? null),
            ':observacoes' => $this->textoOuNull($dados['observacoes'] ?? null),
            ':responsavel_acompanhamento' => $this->textoOuNull($dados['responsavel_acompanhamento'] ?? null),
            ':prioridade' => strtoupper($dados['prioridade'] ?? 'PADRAO'),
        ];

        $params[$atualizacao ? ':atualizado_por' : ':criado_por'] = $usuarioId;
        return $params;
    }

    private function criarVisita(int $agendaId, array $dados, int $usuarioId): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO visitas_tecnicas (
                agenda_id, empresa_id, unidade_id, usuario_id,
                data_visita, hora_visita, veiculo_id,
                responsavel_acompanhamento, objetivo, observacoes,
                status, atualizado_por, atualizado_em, criado_em
            ) VALUES (
                :agenda_id, :empresa_id, :unidade_id, :usuario_id,
                :data_visita, :hora_visita, :veiculo_id,
                :responsavel, :objetivo, :observacoes,
                'AGENDADA', :atualizado_por, NOW(), NOW()
            )
        ");
        $stmt->execute([
            ':agenda_id' => $agendaId,
            ':empresa_id' => (int)$dados['empresa_id'],
            ':unidade_id' => !empty($dados['unidade_id']) ? (int)$dados['unidade_id'] : null,
            ':usuario_id' => (int)$dados['tecnico_id'],
            ':data_visita' => $dados['data_agendada'],
            ':hora_visita' => $dados['hora_inicio'],
            ':veiculo_id' => !empty($dados['veiculo_id']) ? (int)$dados['veiculo_id'] : null,
            ':responsavel' => $this->textoOuNull($dados['responsavel_acompanhamento'] ?? null),
            ':objetivo' => $this->textoOuNull($dados['objetivo'] ?? null),
            ':observacoes' => $this->textoOuNull($dados['observacoes'] ?? null),
            ':atualizado_por' => $usuarioId,
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function garantirVisita(int $agendaId, array $agendaAtual, array $dados, int $usuarioId): int
    {
        if (!empty($agendaAtual['visita_tecnica_id'])) {
            return (int)$agendaAtual['visita_tecnica_id'];
        }

        $visitaId = $this->criarVisita($agendaId, $dados, $usuarioId);
        $stmt = $this->db->prepare(
            'UPDATE agendas SET visita_tecnica_id = :visita_id WHERE id = :agenda_id'
        );
        $stmt->execute([':visita_id' => $visitaId, ':agenda_id' => $agendaId]);

        $this->registrarHistoricoAgenda(
            $agendaId,
            $usuarioId,
            'VISITA_GERADA',
            'Visita técnica gerada para um agendamento legado.',
            null,
            null,
            ['visita_tecnica_id' => $visitaId]
        );
        $this->registrarHistoricoVisita($visitaId, $usuarioId, 'CRIADA_PELA_AGENDA', null, 'AGENDADA', null);

        return $visitaId;
    }

    private function sincronizarDadosVisita(int $visitaId, array $dados, int $usuarioId): void
    {
        $stmt = $this->db->prepare("
            UPDATE visitas_tecnicas SET
                empresa_id = :empresa_id,
                unidade_id = :unidade_id,
                usuario_id = :usuario_id,
                veiculo_id = :veiculo_id,
                responsavel_acompanhamento = :responsavel,
                objetivo = :objetivo,
                observacoes = :observacoes,
                atualizado_por = :atualizado_por,
                atualizado_em = NOW()
            WHERE id = :id
              AND status NOT IN ('EM_ANDAMENTO', 'CHECKLIST_INICIADO', 'FINALIZADA', 'CANCELADA', 'EXCLUIDA')
        ");
        $stmt->execute([
            ':empresa_id' => (int)$dados['empresa_id'],
            ':unidade_id' => !empty($dados['unidade_id']) ? (int)$dados['unidade_id'] : null,
            ':usuario_id' => (int)$dados['tecnico_id'],
            ':veiculo_id' => !empty($dados['veiculo_id']) ? (int)$dados['veiculo_id'] : null,
            ':responsavel' => $this->textoOuNull($dados['responsavel_acompanhamento'] ?? null),
            ':objetivo' => $this->textoOuNull($dados['objetivo'] ?? null),
            ':observacoes' => $this->textoOuNull($dados['observacoes'] ?? null),
            ':atualizado_por' => $usuarioId,
            ':id' => $visitaId,
        ]);
    }

    private function buscarRegistroTransacao(int $id, bool $bloquear = false): ?array
    {
        $sql = "
            SELECT a.*, vt.status AS visita_status
            FROM agendas a
            LEFT JOIN visitas_tecnicas vt ON vt.id = a.visita_tecnica_id
            WHERE a.id = :id
            LIMIT 1
        ";
        if ($bloquear) {
            $sql .= ' FOR UPDATE';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $registro = $stmt->fetch();

        return $registro ?: null;
    }

    private function registrarHistoricoAgenda(
        int $agendaId,
        int $usuarioId,
        string $acao,
        ?string $descricao,
        ?string $motivo,
        ?array $anterior,
        ?array $novo
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO agenda_historico (
                agenda_id, usuario_id, acao, descricao, motivo,
                dados_anteriores, dados_novos, criado_em
            ) VALUES (
                :agenda_id, :usuario_id, :acao, :descricao, :motivo,
                :dados_anteriores, :dados_novos, NOW()
            )
        ");
        $stmt->execute([
            ':agenda_id' => $agendaId,
            ':usuario_id' => $usuarioId,
            ':acao' => $acao,
            ':descricao' => $descricao,
            ':motivo' => $this->textoOuNull($motivo),
            ':dados_anteriores' => $this->jsonOuNull($anterior),
            ':dados_novos' => $this->jsonOuNull($novo),
        ]);
    }

    private function registrarHistoricoVisita(
        int $visitaId,
        int $usuarioId,
        string $acao,
        ?string $statusAnterior,
        ?string $statusNovo,
        ?string $motivo
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO visita_historico (
                visita_id, usuario_id, acao,
                status_anterior, status_novo, motivo, criado_em
            ) VALUES (
                :visita_id, :usuario_id, :acao,
                :status_anterior, :status_novo, :motivo, NOW()
            )
        ");
        $stmt->execute([
            ':visita_id' => $visitaId,
            ':usuario_id' => $usuarioId,
            ':acao' => $acao,
            ':status_anterior' => $this->textoOuNull($statusAnterior),
            ':status_novo' => $this->textoOuNull($statusNovo),
            ':motivo' => $this->textoOuNull($motivo),
        ]);
    }

    private function agendaEditavel(array $agenda): bool
    {
        if (in_array(strtoupper((string)($agenda['status'] ?? '')), ['CANCELADO', 'CONCLUIDO', 'EXCLUIDO'], true)) {
            return false;
        }

        return !in_array(
            strtoupper((string)($agenda['visita_status'] ?? '')),
            self::STATUS_VISITA_BLOQUEIAM_EDICAO,
            true
        );
    }

    private function agendaPodeSerCanceladaOuExcluida(array $agenda): bool
    {
        return $this->agendaEditavel($agenda);
    }

    private function agendaFoiReagendada(array $atual, array $dados): bool
    {
        return (string)$atual['data_agendada'] !== (string)$dados['data_agendada']
            || substr((string)$atual['hora_inicio'], 0, 5) !== substr((string)$dados['hora_inicio'], 0, 5)
            || substr((string)$atual['hora_fim'], 0, 5) !== substr((string)$dados['hora_fim'], 0, 5);
    }

    private function registroExiste(string $tabela, int $id, bool $somenteAtivo): bool
    {
        if (!in_array($tabela, ['empresas', 'veiculos'], true) || $id <= 0) {
            return false;
        }

        $sql = "SELECT COUNT(*) FROM {$tabela} WHERE id = :id";
        if ($somenteAtivo) {
            $sql .= ' AND ativo = 1';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function jsonOuNull(?array $dados): ?string
    {
        if ($dados === null) {
            return null;
        }

        $json = json_encode(
            $dados,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        return $json === false ? null : $json;
    }

    private function textoOuNull(?string $valor): ?string
    {
        $valor = trim((string)$valor);
        return $valor !== '' ? $valor : null;
    }
}
