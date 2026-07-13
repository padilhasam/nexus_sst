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

    public function __construct()
    {
        $this->db = (new Database())->getConnection();

        $this->db->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        $this->db->setAttribute(
            PDO::ATTR_DEFAULT_FETCH_MODE,
            PDO::FETCH_ASSOC
        );
    }

    public function listarTodos(array $filtros = []): array
    {
        $parametros = [];

        $sql = "
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
                ue.nome AS excluido_por_nome

            FROM agendas a

            INNER JOIN empresas e
                ON e.id = a.empresa_id

            INNER JOIN usuarios t
                ON t.id = a.tecnico_id

            LEFT JOIN unidades un
                ON un.id = a.unidade_id

            LEFT JOIN veiculos v
                ON v.id = a.veiculo_id

            LEFT JOIN usuarios uc
                ON uc.id = a.criado_por

            LEFT JOIN usuarios ua
                ON ua.id = a.atualizado_por

            LEFT JOIN usuarios uca
                ON uca.id = a.cancelado_por

            LEFT JOIN usuarios ue
                ON ue.id = a.excluido_por

            WHERE a.status <> 'EXCLUIDO'
        ";

        if (!empty($filtros['status'])) {
            $sql .= ' AND a.status = :status';
            $parametros[':status'] =
                strtoupper($filtros['status']);
        }

        if (!empty($filtros['data_inicio'])) {
            $sql .= '
                AND a.data_agendada >= :data_inicio
            ';

            $parametros[':data_inicio'] =
                $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= '
                AND a.data_agendada <= :data_fim
            ';

            $parametros[':data_fim'] =
                $filtros['data_fim'];
        }

        if (!empty($filtros['empresa_id'])) {
            $sql .= '
                AND a.empresa_id = :empresa_id
            ';

            $parametros[':empresa_id'] =
                (int)$filtros['empresa_id'];
        }

        if (!empty($filtros['tecnico_id'])) {
            $sql .= '
                AND a.tecnico_id = :tecnico_id
            ';

            $parametros[':tecnico_id'] =
                (int)$filtros['tecnico_id'];
        }

        $sql .= "
            ORDER BY
                a.data_agendada DESC,
                a.hora_inicio DESC,
                a.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
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
                ue.nome AS excluido_por_nome

            FROM agendas a

            INNER JOIN empresas e
                ON e.id = a.empresa_id

            INNER JOIN usuarios t
                ON t.id = a.tecnico_id

            LEFT JOIN unidades un
                ON un.id = a.unidade_id

            LEFT JOIN veiculos v
                ON v.id = a.veiculo_id

            LEFT JOIN usuarios uc
                ON uc.id = a.criado_por

            LEFT JOIN usuarios ua
                ON ua.id = a.atualizado_por

            LEFT JOIN usuarios uca
                ON uca.id = a.cancelado_por

            LEFT JOIN usuarios ue
                ON ue.id = a.excluido_por

            WHERE a.id = :id

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $registro = $stmt->fetch();

        return $registro ?: null;
    }

    public function salvar(
        array $dados,
        int $usuarioId
    ): int|false {
        $prioridade = strtoupper(
            $dados['prioridade'] ?? 'PADRAO'
        );

        $status = strtoupper(
            $dados['status'] ?? 'AGENDADO'
        );

        if (!in_array(
            $prioridade,
            self::PRIORIDADES_VALIDAS,
            true
        )) {
            return false;
        }

        if (!in_array(
            $status,
            self::STATUS_VALIDOS,
            true
        )) {
            return false;
        }

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
                criado_por,
                criado_em
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
                :criado_por,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $ok = $stmt->execute([
            ':empresa_id' =>
                (int)$dados['empresa_id'],

            ':unidade_id' =>
                !empty($dados['unidade_id'])
                    ? (int)$dados['unidade_id']
                    : null,

            ':tecnico_id' =>
                (int)$dados['tecnico_id'],

            ':veiculo_id' =>
                !empty($dados['veiculo_id'])
                    ? (int)$dados['veiculo_id']
                    : null,

            ':data_agendada' =>
                $dados['data_agendada'],

            ':hora_inicio' =>
                $dados['hora_inicio'],

            ':hora_fim' =>
                $dados['hora_fim'],

            ':titulo' =>
                $this->textoOuNull(
                    $dados['titulo'] ?? null
                ),

            ':objetivo' =>
                $this->textoOuNull(
                    $dados['objetivo'] ?? null
                ),

            ':observacoes' =>
                $this->textoOuNull(
                    $dados['observacoes'] ?? null
                ),

            ':responsavel_acompanhamento' =>
                $this->textoOuNull(
                    $dados['responsavel_acompanhamento']
                    ?? null
                ),

            ':prioridade' => $prioridade,
            ':status' => $status,
            ':criado_por' => $usuarioId,
        ]);

        if (!$ok) {
            return false;
        }

        return (int)$this->db->lastInsertId();
    }

    public function atualizar(
        int $id,
        array $dados,
        int $usuarioId
    ): bool {
        $atual = $this->buscarPorId($id);

        if (!$atual) {
            return false;
        }

        if (in_array(
            strtoupper($atual['status'] ?? ''),
            ['CANCELADO', 'CONCLUIDO', 'EXCLUIDO'],
            true
        )) {
            return false;
        }

        $prioridade = strtoupper(
            $dados['prioridade'] ?? 'PADRAO'
        );

        $status = strtoupper(
            $dados['status']
            ?? $atual['status']
            ?? 'AGENDADO'
        );

        if (!in_array(
            $prioridade,
            self::PRIORIDADES_VALIDAS,
            true
        )) {
            return false;
        }

        if (!in_array(
            $status,
            self::STATUS_VALIDOS,
            true
        )) {
            return false;
        }

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
                atualizado_por = :atualizado_por,
                atualizado_em = NOW()

            WHERE id = :id
              AND status NOT IN (
                  'CANCELADO',
                  'CONCLUIDO',
                  'EXCLUIDO'
              )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,

            ':empresa_id' =>
                (int)$dados['empresa_id'],

            ':unidade_id' =>
                !empty($dados['unidade_id'])
                    ? (int)$dados['unidade_id']
                    : null,

            ':tecnico_id' =>
                (int)$dados['tecnico_id'],

            ':veiculo_id' =>
                !empty($dados['veiculo_id'])
                    ? (int)$dados['veiculo_id']
                    : null,

            ':data_agendada' =>
                $dados['data_agendada'],

            ':hora_inicio' =>
                $dados['hora_inicio'],

            ':hora_fim' =>
                $dados['hora_fim'],

            ':titulo' =>
                $this->textoOuNull(
                    $dados['titulo'] ?? null
                ),

            ':objetivo' =>
                $this->textoOuNull(
                    $dados['objetivo'] ?? null
                ),

            ':observacoes' =>
                $this->textoOuNull(
                    $dados['observacoes'] ?? null
                ),

            ':responsavel_acompanhamento' =>
                $this->textoOuNull(
                    $dados['responsavel_acompanhamento']
                    ?? null
                ),

            ':prioridade' => $prioridade,
            ':status' => $status,
            ':atualizado_por' => $usuarioId,
        ]);
    }

    public function cancelar(
        int $id,
        string $motivo,
        int $usuarioId
    ): bool {
        $agenda = $this->buscarPorId($id);

        if (!$agenda) {
            return false;
        }

        if (in_array(
            strtoupper($agenda['status'] ?? ''),
            ['CANCELADO', 'CONCLUIDO', 'EXCLUIDO'],
            true
        )) {
            return false;
        }

        $sql = "
            UPDATE agendas SET
                status = 'CANCELADO',
                cancelado_por = :cancelado_por,
                motivo_cancelamento = :motivo_cancelamento,
                cancelado_em = NOW(),
                atualizado_por = :atualizado_por,
                atualizado_em = NOW()

            WHERE id = :id
              AND status NOT IN (
                  'CANCELADO',
                  'CONCLUIDO',
                  'EXCLUIDO'
              )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':cancelado_por' => $usuarioId,
            ':atualizado_por' => $usuarioId,
            ':motivo_cancelamento' => $motivo,
        ]);
    }

    public function excluir(
        int $id,
        string $motivo,
        int $usuarioId
    ): bool {
        $agenda = $this->buscarPorId($id);

        if (!$agenda) {
            return false;
        }

        if (
            strtoupper($agenda['status'] ?? '') ===
            'EXCLUIDO'
        ) {
            return false;
        }

        $sql = "
            UPDATE agendas SET
                status = 'EXCLUIDO',
                excluido_por = :excluido_por,
                motivo_exclusao = :motivo_exclusao,
                excluido_em = NOW(),
                atualizado_por = :atualizado_por,
                atualizado_em = NOW()

            WHERE id = :id
              AND status <> 'EXCLUIDO'
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':excluido_por' => $usuarioId,
            ':atualizado_por' => $usuarioId,
            ':motivo_exclusao' => $motivo,
        ]);
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
                a.id,
                a.tecnico_id,
                a.veiculo_id,
                a.data_agendada,
                a.hora_inicio,
                a.hora_fim,

                t.nome AS tecnico_nome,

                v.modelo AS veiculo_modelo,
                v.placa AS veiculo_placa,

                CASE
                    WHEN a.tecnico_id = :tecnico_id
                    THEN 1
                    ELSE 0
                END AS conflito_tecnico,

                CASE
                    WHEN :veiculo_id_verificacao IS NOT NULL
                     AND a.veiculo_id = :veiculo_id_comparacao
                    THEN 1
                    ELSE 0
                END AS conflito_veiculo

            FROM agendas a

            INNER JOIN usuarios t
                ON t.id = a.tecnico_id

            LEFT JOIN veiculos v
                ON v.id = a.veiculo_id

            WHERE a.data_agendada = :data_agendada

              AND a.status NOT IN (
                  'CANCELADO',
                  'CONCLUIDO',
                  'EXCLUIDO'
              )

              AND (
                  a.tecnico_id = :tecnico_id_comparacao
        ";

        if ($veiculoId !== null) {
            $sql .= "
                  OR a.veiculo_id = :veiculo_id_filtro
            ";
        }

        $sql .= "
              )

              AND :hora_inicio < a.hora_fim
              AND :hora_fim > a.hora_inicio
        ";

        if ($ignorarId !== null) {
            $sql .= "
              AND a.id <> :ignorar_id
            ";
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':tecnico_id',
            $tecnicoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':tecnico_id_comparacao',
            $tecnicoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':data_agendada',
            $dataAgendada
        );

        $stmt->bindValue(
            ':hora_inicio',
            $horaInicio
        );

        $stmt->bindValue(
            ':hora_fim',
            $horaFim
        );

        if ($veiculoId !== null) {
            $stmt->bindValue(
                ':veiculo_id_verificacao',
                $veiculoId,
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':veiculo_id_comparacao',
                $veiculoId,
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':veiculo_id_filtro',
                $veiculoId,
                PDO::PARAM_INT
            );
        } else {
            $stmt->bindValue(
                ':veiculo_id_verificacao',
                null,
                PDO::PARAM_NULL
            );

            $stmt->bindValue(
                ':veiculo_id_comparacao',
                null,
                PDO::PARAM_NULL
            );
        }

        if ($ignorarId !== null) {
            $stmt->bindValue(
                ':ignorar_id',
                $ignorarId,
                PDO::PARAM_INT
            );
        }

        $stmt->execute();

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function eventosCalendario(
        array $filtros = []
    ): array {
        $agendamentos = $this->listarTodos($filtros);

        return array_map(
            static function (array $item): array {
                $empresa = !empty($item['empresa_fantasia'])
                    ? $item['empresa_fantasia']
                    : ($item['empresa_nome'] ?? 'Empresa');

                return [
                    'id' => (int)$item['id'],

                    'title' => !empty($item['titulo'])
                        ? $item['titulo']
                        : $empresa,

                    'start' =>
                        $item['data_agendada'] .
                        'T' .
                        $item['hora_inicio'],

                    'end' =>
                        $item['data_agendada'] .
                        'T' .
                        $item['hora_fim'],

                    'url' =>
                        BASE_URL .
                        '/agenda/visualizar/' .
                        (int)$item['id'],

                    'extendedProps' => [
                        'status' =>
                            $item['status'] ?? '',

                        'prioridade' =>
                            $item['prioridade'] ?? 'PADRAO',

                        'tecnico' =>
                            $item['tecnico_nome'] ?? '',

                        'unidade' =>
                            $item['unidade_nome'] ?? 'Matriz',
                    ],
                ];
            },
            $agendamentos
        );
    }

    public function obterIndicadores(
        array $filtros = []
    ): array {
        $itens = $this->listarTodos($filtros);

        $indicadores = [
            'total' => count($itens),
            'agendados' => 0,
            'confirmados' => 0,
            'reagendados' => 0,
            'cancelados' => 0,
            'concluidos' => 0,
        ];

        foreach ($itens as $item) {
            $status = strtoupper(
                $item['status'] ?? ''
            );

            switch ($status) {
                case 'AGENDADO':
                    $indicadores['agendados']++;
                    break;

                case 'CONFIRMADO':
                    $indicadores['confirmados']++;
                    break;

                case 'REAGENDADO':
                    $indicadores['reagendados']++;
                    break;

                case 'CANCELADO':
                    $indicadores['cancelados']++;
                    break;

                case 'CONCLUIDO':
                    $indicadores['concluidos']++;
                    break;
            }
        }

        return $indicadores;
    }

    private function textoOuNull(
        ?string $valor
    ): ?string {
        $valor = trim((string)$valor);

        return $valor !== ''
            ? $valor
            : null;
    }
}