<?php

require_once __DIR__ . '/../../core/Database.php';

class Visita
{
    private PDO $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();

        $this->db->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        $this->db->setAttribute(
            PDO::ATTR_DEFAULT_FETCH_MODE,
            PDO::FETCH_ASSOC
        );
    }

    /**
     * Lista as visitas técnicas não excluídas.
     */
    public function listarTodos(): array
    {
        $sql = "
            SELECT
            vt.*,

            e.razao_social      AS empresa_nome,
            e.nome_fantasia     AS empresa_fantasia,

            u.nome              AS usuario_nome,

            un.nome             AS unidade_nome,

            v.modelo            AS veiculo_modelo,
            v.placa             AS veiculo_placa

        FROM visitas_tecnicas vt

        INNER JOIN empresas e
                ON e.id = vt.empresa_id

        INNER JOIN usuarios u
                ON u.id = vt.usuario_id

        LEFT JOIN unidades un
            ON un.id = vt.unidade_id

        LEFT JOIN veiculos v
            ON v.id = vt.veiculo_id

        WHERE vt.status <> 'EXCLUIDA'

        ORDER BY
            vt.data_visita DESC,
            vt.hora_inicio DESC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Verifica conflito de técnico ou veículo dentro de um intervalo.
     *
     * A tabela de visitas possui apenas hora_visita. Portanto, considera-se
     * conflito quando o horário da visita existente estiver dentro do
     * intervalo informado.
     */
    public function existeConflitoIntervalo(
        int $usuarioId,
        ?int $veiculoId,
        string $dataVisita,
        string $horaInicio,
        string $horaFim,
        ?int $ignorarId = null
    ): ?array {
        $recursos = [
            'vt.usuario_id = :usuario_id'
        ];

        if ($veiculoId !== null) {
            $recursos[] = 'vt.veiculo_id = :veiculo_id';
        }

        $sql = "
            SELECT
                vt.id,
                vt.usuario_id,
                vt.veiculo_id,
                vt.data_visita,
                vt.hora_visita,

                u.nome AS usuario_nome,

                v.modelo AS veiculo_modelo,
                v.placa AS veiculo_placa

            FROM visitas_tecnicas vt

            LEFT JOIN usuarios u
                ON u.id = vt.usuario_id

            LEFT JOIN veiculos v
                ON v.id = vt.veiculo_id

            WHERE vt.data_visita = :data_visita

              AND vt.status NOT IN (
                  'CANCELADA',
                  'EXCLUIDA',
                  'FINALIZADA'
              )

              AND vt.hora_visita IS NOT NULL

              AND (
                  " . implode(' OR ', $recursos) . "
              )

              AND vt.hora_visita >= :hora_inicio
              AND vt.hora_visita < :hora_fim
        ";

        if ($ignorarId !== null) {
            $sql .= ' AND vt.id <> :ignorar_id';
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':data_visita',
            $dataVisita
        );

        $stmt->bindValue(
            ':usuario_id',
            $usuarioId,
            PDO::PARAM_INT
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
                ':veiculo_id',
                $veiculoId,
                PDO::PARAM_INT
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

    /**
     * Salva uma nova visita técnica.
     */
    public function salvar(array $dados): bool
    {
        $sql = "
            INSERT INTO visitas_tecnicas (
                empresa_id,
                unidade_id,
                usuario_id,
                data_visita,
                hora_visita,
                veiculo_id,
                responsavel_acompanhamento,
                objetivo,
                observacoes,
                status
            ) VALUES (
                :empresa_id,
                :unidade_id,
                :usuario_id,
                :data_visita,
                :hora_visita,
                :veiculo_id,
                :responsavel_acompanhamento,
                :objetivo,
                :observacoes,
                :status
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':empresa_id' => (int)$dados['empresa_id'],

            ':unidade_id' => !empty($dados['unidade_id'])
                ? (int)$dados['unidade_id']
                : null,

            ':usuario_id' => (int)$dados['usuario_id'],

            ':data_visita' => $dados['data_visita'],

            ':hora_visita' => $this->obterHoraVisita($dados),

            ':veiculo_id' => !empty($dados['veiculo_id'])
                ? (int)$dados['veiculo_id']
                : null,

            ':responsavel_acompanhamento' =>
                $this->normalizarTexto(
                    $dados['responsavel_acompanhamento'] ?? null
                ),

            ':objetivo' => $this->normalizarTexto(
                $dados['objetivo'] ?? null
            ),

            ':observacoes' => $this->normalizarTexto(
                $dados['observacoes'] ?? null
            ),

            ':status' => $dados['status'] ?? 'ABERTA',
        ]);
    }

    /**
     * Atualiza somente a data da visita.
     */
    public function updateData(int $id, string $novaData): bool
    {
        $sql = "
            UPDATE visitas_tecnicas
            SET data_visita = :data_visita
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':data_visita' => $novaData,
        ]);
    }

    /**
     * Busca uma visita técnica por ID.
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                vt.*,

                u.nome AS usuario_nome,
                u.nome AS tecnico_nome,

                v.modelo AS veiculo_modelo,
                v.placa AS veiculo_placa,

                e.razao_social AS empresa_nome,
                e.nome_fantasia AS empresa_fantasia,

                uni.nome AS unidade_nome

            FROM visitas_tecnicas vt

            INNER JOIN usuarios u
                ON u.id = vt.usuario_id

            INNER JOIN empresas e
                ON e.id = vt.empresa_id

            LEFT JOIN veiculos v
                ON v.id = vt.veiculo_id

            LEFT JOIN unidades uni
                ON uni.id = vt.unidade_id

            WHERE vt.id = :id

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

    /**
     * Atualiza os dados da visita técnica.
     */
    public function atualizar(int $id, array $dados): bool
    {
        $sql = "
            UPDATE visitas_tecnicas SET
                empresa_id = :empresa_id,
                unidade_id = :unidade_id,
                usuario_id = :usuario_id,
                data_visita = :data_visita,
                hora_visita = :hora_visita,
                veiculo_id = :veiculo_id,
                responsavel_acompanhamento =
                    :responsavel_acompanhamento,
                objetivo = :objetivo,
                observacoes = :observacoes

            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,

            ':empresa_id' => (int)$dados['empresa_id'],

            ':unidade_id' => !empty($dados['unidade_id'])
                ? (int)$dados['unidade_id']
                : null,

            ':usuario_id' => (int)$dados['usuario_id'],

            ':data_visita' => $dados['data_visita'],

            ':hora_visita' => $this->obterHoraVisita($dados),

            ':veiculo_id' => !empty($dados['veiculo_id'])
                ? (int)$dados['veiculo_id']
                : null,

            ':responsavel_acompanhamento' =>
                $this->normalizarTexto(
                    $dados['responsavel_acompanhamento'] ?? null
                ),

            ':objetivo' => $this->normalizarTexto(
                $dados['objetivo'] ?? null
            ),

            ':observacoes' => $this->normalizarTexto(
                $dados['observacoes'] ?? null
            ),
        ]);
    }

    /**
     * Exclusão lógica.
     */
    public function deletar(int $id): bool
    {
        $sql = "
            UPDATE visitas_tecnicas
            SET status = 'EXCLUIDA'
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
        ]);
    }

    /**
     * Atualiza o status da visita.
     */
    public function atualizarStatus(
        int $id,
        string $status
    ): bool {
        $statusPermitidos = [
            'ABERTA',
            'AGENDADA',
            'CONFIRMADA',
            'EM_ANDAMENTO',
            'CHECKLIST_INICIADO',
            'FINALIZADA',
            'CANCELADA',
            'EXCLUIDA',
        ];

        if (!in_array($status, $statusPermitidos, true)) {
            return false;
        }

        $sql = "
            UPDATE visitas_tecnicas
            SET status = :status
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    /**
     * Registra uma ação no histórico da visita.
     */
    public function registrarHistorico(
        int $visitaId,
        ?int $usuarioId,
        string $acao,
        ?string $statusAnterior = null,
        ?string $statusNovo = null,
        ?string $motivo = null
    ): bool {
        $sql = "
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
                :acao,
                :status_anterior,
                :status_novo,
                :motivo
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':visita_id' => $visitaId,
            ':usuario_id' => $usuarioId,
            ':acao' => $acao,
            ':status_anterior' => $statusAnterior,
            ':status_novo' => $statusNovo,
            ':motivo' => $this->normalizarTexto($motivo),
        ]);
    }

    /**
     * Lista o histórico de uma visita.
     */
    public function listarHistorico(int $visitaId): array
    {
        $sql = "
            SELECT
                vh.*,
                u.nome AS usuario_nome

            FROM visita_historico vh

            LEFT JOIN usuarios u
                ON u.id = vh.usuario_id

            WHERE vh.visita_id = :visita_id

            ORDER BY vh.criado_em DESC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':visita_id',
            $visitaId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Mantém compatibilidade temporária com formulários antigos.
     *
     * Prioridade:
     * 1. hora_visita;
     * 2. hora_inicio.
     */
    private function obterHoraVisita(array $dados): ?string
    {
        $hora = trim(
            (string)(
                $dados['hora_visita']
                ?? $dados['hora_inicio']
                ?? ''
            )
        );

        return $hora !== '' ? $hora : null;
    }

    private function normalizarTexto(
        ?string $valor
    ): ?string {
        $valor = trim((string)$valor);

        return $valor !== '' ? $valor : null;
    }
}