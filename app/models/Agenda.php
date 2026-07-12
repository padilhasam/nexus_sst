<?php

require_once __DIR__ . '/../../core/Database.php';

class Agenda
{
    private PDO $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db = $database->getConnection();

        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Lista os agendamentos aplicando os filtros recebidos.
     */
    public function listarTodos(array $filtros = []): array
    {
        $where = [
            "a.status <> 'EXCLUIDO'"
        ];

        $params = [];

        if (!empty($filtros['status'])) {
            $where[] = 'a.status = :status';
            $params[':status'] = $filtros['status'];
        }

        if (!empty($filtros['data_inicio'])) {
            $where[] = 'a.data_agendada >= :data_inicio';
            $params[':data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $where[] = 'a.data_agendada <= :data_fim';
            $params[':data_fim'] = $filtros['data_fim'];
        }

        if (!empty($filtros['empresa_id'])) {
            $where[] = 'a.empresa_id = :empresa_id';
            $params[':empresa_id'] = (int)$filtros['empresa_id'];
        }

        if (!empty($filtros['tecnico_id'])) {
            $where[] = 'a.tecnico_id = :tecnico_id';
            $params[':tecnico_id'] = (int)$filtros['tecnico_id'];
        }

        $sql = "
            SELECT
                a.id,
                a.empresa_id,
                a.unidade_id,
                a.tecnico_id,
                a.veiculo_id,
                a.data_agendada,
                a.hora_inicio,
                a.hora_fim,
                a.titulo,
                a.objetivo,
                a.observacoes,
                a.responsavel_acompanhamento,
                a.prioridade,
                a.status,
                a.visita_tecnica_id,
                a.motivo_cancelamento,
                a.motivo_exclusao,
                a.criado_em,
                a.atualizado_em,
                a.cancelado_em,
                a.excluido_em,

                us.nome AS tecnico_nome,

                emp.razao_social AS empresa_nome,
                emp.nome_fantasia AS empresa_fantasia,

                uni.nome AS unidade_nome,

                vei.modelo AS veiculo_modelo,
                vei.placa AS veiculo_placa

            FROM agendas a

            INNER JOIN empresas emp
                ON emp.id = a.empresa_id

            INNER JOIN usuarios us
                ON us.id = a.tecnico_id

            LEFT JOIN unidades uni
                ON uni.id = a.unidade_id

            LEFT JOIN veiculos vei
                ON vei.id = a.veiculo_id

            WHERE " . implode(' AND ', $where) . "

            ORDER BY
                a.data_agendada ASC,
                a.hora_inicio ASC,
                a.criado_em DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Formata os agendamentos para utilização pelo FullCalendar.
     */
    public function eventosCalendario(array $filtros = []): array
    {
        $agendamentos = $this->listarTodos($filtros);
        $eventos = [];

        foreach ($agendamentos as $item) {
            if (empty($item['data_agendada'])) {
                continue;
            }

            $horaInicio = !empty($item['hora_inicio'])
                ? $item['hora_inicio']
                : '08:00:00';

            $horaFim = !empty($item['hora_fim'])
                ? $item['hora_fim']
                : null;

            $empresa = !empty($item['empresa_fantasia'])
                ? $item['empresa_fantasia']
                : ($item['empresa_nome'] ?? 'Empresa');

            $titulo = !empty($item['titulo'])
                ? $item['titulo']
                : $empresa;

            $eventos[] = [
                'id' => (int)$item['id'],
                'title' => $titulo,
                'start' => $item['data_agendada'] . 'T' . $horaInicio,
                'end' => $horaFim
                    ? $item['data_agendada'] . 'T' . $horaFim
                    : null,

                'url' => BASE_URL . '/agenda/visualizar/' . (int)$item['id'],

                'extendedProps' => [
                    'empresa' => $empresa,
                    'status' => $item['status'] ?? '',
                    'prioridade' => $item['prioridade'] ?? 'PADRAO',
                    'tecnico' => $item['tecnico_nome'] ?? '',
                    'unidade' => $item['unidade_nome'] ?? 'Matriz',
                    'objetivo' => $item['objetivo'] ?? '',
                    'responsavel' => $item['responsavel_acompanhamento'] ?? '',
                ],
            ];
        }

        return $eventos;
    }

    /**
     * Busca um agendamento específico.
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                a.*,

                us.nome AS tecnico_nome,

                emp.razao_social AS empresa_nome,
                emp.nome_fantasia AS empresa_fantasia,

                uni.nome AS unidade_nome,

                vei.modelo AS veiculo_modelo,
                vei.placa AS veiculo_placa

            FROM agendas a

            INNER JOIN empresas emp
                ON emp.id = a.empresa_id

            INNER JOIN usuarios us
                ON us.id = a.tecnico_id

            LEFT JOIN unidades uni
                ON uni.id = a.unidade_id

            LEFT JOIN veiculos vei
                ON vei.id = a.veiculo_id

            WHERE a.id = :id

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $registro = $stmt->fetch();

        return $registro ?: null;
    }

    /**
     * Verifica conflito de horário para técnico ou veículo.
     */
    public function existeConflitoIntervalo(
        int $tecnicoId,
        ?int $veiculoId,
        string $dataAgendada,
        string $horaInicio,
        string $horaFim,
        ?int $ignorarId = null
    ): ?array {
        $condicoesResponsavel = [
            'a.tecnico_id = :tecnico_id'
        ];

        if ($veiculoId !== null) {
            $condicoesResponsavel[] = 'a.veiculo_id = :veiculo_id';
        }

        $sql = "
            SELECT
                a.id,
                a.tecnico_id,
                a.veiculo_id,
                a.data_agendada,
                a.hora_inicio,
                a.hora_fim

            FROM agendas a

            WHERE a.data_agendada = :data_agendada

              AND a.status NOT IN (
                  'CANCELADO',
                  'EXCLUIDO',
                  'CONCLUIDO'
              )

              AND a.hora_inicio IS NOT NULL
              AND a.hora_fim IS NOT NULL

              AND (
                  " . implode(' OR ', $condicoesResponsavel) . "
              )

              AND (
                  :hora_inicio < a.hora_fim
                  AND :hora_fim > a.hora_inicio
              )
        ";

        if ($ignorarId !== null) {
            $sql .= ' AND a.id <> :ignorar_id';
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':data_agendada', $dataAgendada);
        $stmt->bindValue(':tecnico_id', $tecnicoId, PDO::PARAM_INT);
        $stmt->bindValue(':hora_inicio', $horaInicio);
        $stmt->bindValue(':hora_fim', $horaFim);

        if ($veiculoId !== null) {
            $stmt->bindValue(':veiculo_id', $veiculoId, PDO::PARAM_INT);
        }

        if ($ignorarId !== null) {
            $stmt->bindValue(':ignorar_id', $ignorarId, PDO::PARAM_INT);
        }

        $stmt->execute();

        $conflito = $stmt->fetch();

        return $conflito ?: null;
    }

    /**
     * Salva um novo agendamento.
     */
    public function salvar(array $dados): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "
                INSERT INTO agendas (
                    empresa_id,
                    unidade_id,
                    tecnico_id,
                    veiculo_id,
                    data_agendada,
                    hora_inicio,
                    hora_fim,
                    titulo,
                    objetivo,
                    observacoes,
                    responsavel_acompanhamento,
                    prioridade,
                    status,
                    criado_por
                ) VALUES (
                    :empresa_id,
                    :unidade_id,
                    :tecnico_id,
                    :veiculo_id,
                    :data_agendada,
                    :hora_inicio,
                    :hora_fim,
                    :titulo,
                    :objetivo,
                    :observacoes,
                    :responsavel_acompanhamento,
                    :prioridade,
                    :status,
                    :criado_por
                )
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':empresa_id' => (int)$dados['empresa_id'],
                ':unidade_id' => !empty($dados['unidade_id'])
                    ? (int)$dados['unidade_id']
                    : null,

                ':tecnico_id' => (int)$dados['tecnico_id'],

                ':veiculo_id' => !empty($dados['veiculo_id'])
                    ? (int)$dados['veiculo_id']
                    : null,

                ':data_agendada' => $dados['data_agendada'],
                ':hora_inicio' => $dados['hora_inicio'] ?: null,
                ':hora_fim' => $dados['hora_fim'] ?: null,

                ':titulo' => $dados['titulo'] ?? null,
                ':objetivo' => $dados['objetivo'] ?? null,
                ':observacoes' => $dados['observacoes'] ?? null,

                ':responsavel_acompanhamento' =>
                    $dados['responsavel_acompanhamento'] ?? null,

                ':prioridade' => $dados['prioridade'] ?? 'PADRAO',
                ':status' => $dados['status'] ?? 'AGENDADO',

                ':criado_por' => $_SESSION['usuario_id'] ?? null,
            ]);

            $agendaId = (int)$this->db->lastInsertId();

            $this->registrarHistorico(
                $agendaId,
                'CRIADA',
                'Agendamento criado.',
                null,
                $dados
            );

            $this->db->commit();

            return true;

        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log(
                '[Agenda::salvar] ' .
                $erro->getMessage()
            );

            return false;
        }
    }

    /**
     * Atualiza um agendamento existente.
     */
    public function atualizar(int $id, array $dados): bool
    {
        $anterior = $this->buscarPorId($id);

        if (!$anterior) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $sql = "
                UPDATE agendas SET
                    empresa_id = :empresa_id,
                    unidade_id = :unidade_id,
                    tecnico_id = :tecnico_id,
                    veiculo_id = :veiculo_id,
                    data_agendada = :data_agendada,
                    hora_inicio = :hora_inicio,
                    hora_fim = :hora_fim,
                    titulo = :titulo,
                    objetivo = :objetivo,
                    observacoes = :observacoes,
                    responsavel_acompanhamento =
                        :responsavel_acompanhamento,
                    prioridade = :prioridade,
                    status = :status,
                    atualizado_por = :atualizado_por

                WHERE id = :id
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':id' => $id,

                ':empresa_id' => (int)$dados['empresa_id'],

                ':unidade_id' => !empty($dados['unidade_id'])
                    ? (int)$dados['unidade_id']
                    : null,

                ':tecnico_id' => (int)$dados['tecnico_id'],

                ':veiculo_id' => !empty($dados['veiculo_id'])
                    ? (int)$dados['veiculo_id']
                    : null,

                ':data_agendada' => $dados['data_agendada'],
                ':hora_inicio' => $dados['hora_inicio'] ?: null,
                ':hora_fim' => $dados['hora_fim'] ?: null,

                ':titulo' => $dados['titulo'] ?? null,
                ':objetivo' => $dados['objetivo'] ?? null,
                ':observacoes' => $dados['observacoes'] ?? null,

                ':responsavel_acompanhamento' =>
                    $dados['responsavel_acompanhamento'] ?? null,

                ':prioridade' => $dados['prioridade'] ?? 'PADRAO',
                ':status' => $dados['status'] ?? 'AGENDADO',

                ':atualizado_por' => $_SESSION['usuario_id'] ?? null,
            ]);

            $acao = $this->identificarAcaoAlteracao($anterior, $dados);

            $this->registrarHistorico(
                $id,
                $acao,
                $dados['motivo'] ?? 'Agendamento alterado.',
                $anterior,
                $dados
            );

            $this->db->commit();

            return true;

        } catch (Throwable $erro) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log(
                '[Agenda::atualizar] ' .
                $erro->getMessage()
            );

            return false;
        }
    }

    /**
     * Cancela logicamente um agendamento.
     */
    public function cancelar(int $id, string $motivo): bool
    {
        $anterior = $this->buscarPorId($id);

        if (!$anterior || $anterior['status'] === 'EXCLUIDO') {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $sql = "
                UPDATE agendas SET
                    status = 'CANCELADO',
                    motivo_cancelamento = :motivo,
                    cancelado_por = :usuario_id,
                    cancelado_em = NOW(),
                    atualizado_por = :usuario_id_atualizacao

                WHERE id = :id
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':id' => $id,
                ':motivo' => $motivo,
                ':usuario_id' => $_SESSION['usuario_id'] ?? null,
                ':usuario_id_atualizacao' =>
                    $_SESSION['usuario_id'] ?? null,
            ]);

            $novo = $anterior;
            $novo['status'] = 'CANCELADO';
            $novo['motivo_cancelamento'] = $motivo;

            $this->registrarHistorico(
                $id,
                'CANCELADA',
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

            error_log(
                '[Agenda::cancelar] ' .
                $erro->getMessage()
            );

            return false;
        }
    }

    /**
     * Realiza exclusão lógica do agendamento.
     */
    public function excluir(int $id, string $motivo): bool
    {
        $anterior = $this->buscarPorId($id);

        if (!$anterior) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $sql = "
                UPDATE agendas SET
                    status = 'EXCLUIDO',
                    motivo_exclusao = :motivo,
                    excluido_por = :usuario_id,
                    excluido_em = NOW(),
                    atualizado_por = :usuario_id_atualizacao

                WHERE id = :id
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':id' => $id,
                ':motivo' => $motivo,
                ':usuario_id' => $_SESSION['usuario_id'] ?? null,
                ':usuario_id_atualizacao' =>
                    $_SESSION['usuario_id'] ?? null,
            ]);

            $novo = $anterior;
            $novo['status'] = 'EXCLUIDO';
            $novo['motivo_exclusao'] = $motivo;

            $this->registrarHistorico(
                $id,
                'EXCLUIDA',
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

            error_log(
                '[Agenda::excluir] ' .
                $erro->getMessage()
            );

            return false;
        }
    }

    /**
     * Registra histórico de criação, alteração, reagendamento,
     * cancelamento ou exclusão.
     */
    public function registrarHistorico(
        int $agendaId,
        string $acao,
        ?string $motivo = null,
        ?array $dadosAnteriores = null,
        ?array $dadosNovos = null
    ): bool {
        $sql = "
            INSERT INTO agenda_historico (
                agenda_id,
                usuario_id,
                acao,
                motivo,
                dados_anteriores,
                dados_novos
            ) VALUES (
                :agenda_id,
                :usuario_id,
                :acao,
                :motivo,
                :dados_anteriores,
                :dados_novos
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':agenda_id' => $agendaId,
            ':usuario_id' => $_SESSION['usuario_id'] ?? null,
            ':acao' => $acao,
            ':motivo' => $motivo,

            ':dados_anteriores' => $dadosAnteriores !== null
                ? json_encode(
                    $dadosAnteriores,
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES |
                    JSON_INVALID_UTF8_SUBSTITUTE
                )
                : null,

            ':dados_novos' => $dadosNovos !== null
                ? json_encode(
                    $dadosNovos,
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES |
                    JSON_INVALID_UTF8_SUBSTITUTE
                )
                : null,
        ]);
    }

    /**
     * Lista o histórico do agendamento.
     */
    public function listarHistorico(int $agendaId): array
    {
        $sql = "
            SELECT
                ah.*,
                us.nome AS usuario_nome

            FROM agenda_historico ah

            LEFT JOIN usuarios us
                ON us.id = ah.usuario_id

            WHERE ah.agenda_id = :agenda_id

            ORDER BY ah.criado_em DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':agenda_id', $agendaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Retorna os totais utilizados nos indicadores da Agenda.
     */
    public function obterIndicadores(array $filtros = []): array
    {
        $where = [
            "a.status <> 'EXCLUIDO'"
        ];

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $where[] = 'a.data_agendada >= :data_inicio';
            $params[':data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $where[] = 'a.data_agendada <= :data_fim';
            $params[':data_fim'] = $filtros['data_fim'];
        }

        if (!empty($filtros['empresa_id'])) {
            $where[] = 'a.empresa_id = :empresa_id';
            $params[':empresa_id'] = (int)$filtros['empresa_id'];
        }

        if (!empty($filtros['tecnico_id'])) {
            $where[] = 'a.tecnico_id = :tecnico_id';
            $params[':tecnico_id'] = (int)$filtros['tecnico_id'];
        }

        $sql = "
            SELECT
                COUNT(*) AS total,

                SUM(
                    CASE WHEN a.status = 'AGENDADO'
                    THEN 1 ELSE 0 END
                ) AS agendados,

                SUM(
                    CASE WHEN a.status = 'CONFIRMADO'
                    THEN 1 ELSE 0 END
                ) AS confirmados,

                SUM(
                    CASE WHEN a.status = 'REAGENDADO'
                    THEN 1 ELSE 0 END
                ) AS reagendados,

                SUM(
                    CASE WHEN a.status = 'CANCELADO'
                    THEN 1 ELSE 0 END
                ) AS cancelados,

                SUM(
                    CASE WHEN a.status = 'CONCLUIDO'
                    THEN 1 ELSE 0 END
                ) AS concluidos

            FROM agendas a

            WHERE " . implode(' AND ', $where);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $dados = $stmt->fetch() ?: [];

        return [
            'total' => (int)($dados['total'] ?? 0),
            'agendados' => (int)($dados['agendados'] ?? 0),
            'confirmados' => (int)($dados['confirmados'] ?? 0),
            'reagendados' => (int)($dados['reagendados'] ?? 0),
            'cancelados' => (int)($dados['cancelados'] ?? 0),
            'concluidos' => (int)($dados['concluidos'] ?? 0),
        ];
    }

    /**
     * Diferencia alteração simples de reagendamento.
     */
    private function identificarAcaoAlteracao(
        array $anterior,
        array $novo
    ): string {
        $dataMudou =
            ($anterior['data_agendada'] ?? null) !==
            ($novo['data_agendada'] ?? null);

        $inicioMudou =
            substr((string)($anterior['hora_inicio'] ?? ''), 0, 5) !==
            substr((string)($novo['hora_inicio'] ?? ''), 0, 5);

        $fimMudou =
            substr((string)($anterior['hora_fim'] ?? ''), 0, 5) !==
            substr((string)($novo['hora_fim'] ?? ''), 0, 5);

        return ($dataMudou || $inicioMudou || $fimMudou)
            ? 'REAGENDADA'
            : 'ALTERADA';
    }
}