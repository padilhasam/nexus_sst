<?php

require_once __DIR__ . '/../../core/Database.php';

class Visita
{
    private PDO $db;

    private const STATUS_ABERTOS = [
        'ABERTA',
        'AGENDADA',
        'CONFIRMADA',
    ];

    private const STATUS_VALIDOS = [
        'ABERTA',
        'AGENDADA',
        'CONFIRMADA',
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

    /**
     * Fila operacional das visitas.
     *
     * ADMIN visualiza todas. Os demais perfis visualizam somente as visitas
     * atribuídas ao próprio usuário. A aba "abertas" contém apenas visitas
     * que ainda não iniciaram o check-list.
     */
    public function listarFila(
        int $usuarioId,
        string $tipoUsuario,
        string $aba = 'abertas',
        array $filtros = []
    ): array {
        $parametros = [];
        $where = $this->montarEscopoUsuario($usuarioId, $tipoUsuario, $parametros);
        $where .= " AND vt.status <> 'EXCLUIDA'";

        switch ($aba) {
            case 'concluidas':
                $where .= " AND (vt.status = 'FINALIZADA' OR cv.status = 'CONCLUIDO')";
                break;

            case 'todas':
                break;

            case 'abertas':
            default:
                $where .= "
                    AND vt.status IN ('ABERTA', 'AGENDADA', 'CONFIRMADA')
                    AND (cv.id IS NULL OR cv.status = 'CANCELADO')
                ";
                break;
        }

        if (!empty($filtros['prioridade'])) {
            $prioridade = strtoupper(trim((string)$filtros['prioridade']));
            if (in_array($prioridade, ['PADRAO', 'URGENTE', 'CRITICA'], true)) {
                $where .= ' AND COALESCE(a.prioridade, cv.prioridade, \'PADRAO\') = :prioridade';
                $parametros[':prioridade'] = $prioridade;
            }
        }

        if (!empty($filtros['data_inicio'])) {
            $where .= ' AND vt.data_visita >= :data_inicio';
            $parametros[':data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $where .= ' AND vt.data_visita <= :data_fim';
            $parametros[':data_fim'] = $filtros['data_fim'];
        }

        $ordem = $aba === 'abertas'
            ? "
                ORDER BY
                    CASE COALESCE(a.prioridade, cv.prioridade, 'PADRAO')
                        WHEN 'CRITICA' THEN 1
                        WHEN 'URGENTE' THEN 2
                        ELSE 3
                    END ASC,
                    vt.data_visita ASC,
                    vt.hora_visita ASC,
                    vt.id ASC
            "
            : ' ORDER BY vt.data_visita DESC, vt.hora_visita DESC, vt.id DESC';

        $sql = $this->selectBase() . $where . $ordem;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    /**
     * Indicadores utilizados pelas abas da fila de visitas.
     */
    public function obterIndicadores(int $usuarioId, string $tipoUsuario): array
    {
        $parametros = [];
        $where = $this->montarEscopoUsuario($usuarioId, $tipoUsuario, $parametros);
        $where .= " AND vt.status <> 'EXCLUIDA'";

        $sql = "
            SELECT
                SUM(
                    CASE
                        WHEN vt.status IN ('ABERTA', 'AGENDADA', 'CONFIRMADA')
                         AND (cv.id IS NULL OR cv.status = 'CANCELADO')
                        THEN 1 ELSE 0
                    END
                ) AS abertas,
                SUM(
                    CASE
                        WHEN vt.status IN ('EM_ANDAMENTO', 'CHECKLIST_INICIADO')
                          OR cv.status = 'EM_ANDAMENTO'
                        THEN 1 ELSE 0
                    END
                ) AS andamento,
                SUM(
                    CASE
                        WHEN vt.status = 'FINALIZADA' OR cv.status = 'CONCLUIDO'
                        THEN 1 ELSE 0
                    END
                ) AS concluidas,
                COUNT(*) AS total
            FROM visitas_tecnicas vt
            LEFT JOIN agendas a ON a.id = vt.agenda_id
            LEFT JOIN checklists_visita cv ON cv.visita_id = vt.id
            {$where}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        $resultado = $stmt->fetch() ?: [];

        return [
            'abertas' => (int)($resultado['abertas'] ?? 0),
            'andamento' => (int)($resultado['andamento'] ?? 0),
            'concluidas' => (int)($resultado['concluidas'] ?? 0),
            'total' => (int)($resultado['total'] ?? 0),
        ];
    }

    /**
     * Compatibilidade com pontos antigos que ainda solicitam todas as visitas.
     */
    public function listarTodos(): array
    {
        $sql = $this->selectBase() . "
            WHERE vt.status <> 'EXCLUIDA'
            ORDER BY vt.data_visita DESC, vt.hora_visita DESC, vt.id DESC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = $this->selectBase() . ' WHERE vt.id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $registro = $stmt->fetch();

        return $registro ?: null;
    }

    public function usuarioPodeAcessar(array $visita, int $usuarioId, string $tipoUsuario): bool
    {
        if ($this->usuarioAdministrador($tipoUsuario)) {
            return true;
        }

        return (int)($visita['usuario_id'] ?? 0) === $usuarioId;
    }

    public function podeIniciarChecklist(array $visita): bool
    {
        $status = strtoupper((string)($visita['status'] ?? ''));
        $checklistStatus = strtoupper((string)($visita['checklist_status'] ?? ''));

        if (!in_array($status, self::STATUS_ABERTOS, true)) {
            return false;
        }

        return $checklistStatus === '' || $checklistStatus === 'CANCELADO';
    }

    /**
     * Consulta usada no início transacional do check-list.
     */
    public function buscarParaAtualizacao(int $id): ?array
    {
        $sql = "
            SELECT
                vt.*,
                a.id AS agenda_ref_id,
                a.status AS agenda_status,
                a.prioridade AS agenda_prioridade,
                a.hora_inicio AS agenda_hora_inicio,
                a.hora_fim AS agenda_hora_fim
            FROM visitas_tecnicas vt
            LEFT JOIN agendas a ON a.id = vt.agenda_id
            WHERE vt.id = :id
            LIMIT 1
            FOR UPDATE
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $registro = $stmt->fetch();

        return $registro ?: null;
    }

    public function existeConflitoIntervalo(
        int $usuarioId,
        ?int $veiculoId,
        string $dataVisita,
        string $horaInicio,
        string $horaFim,
        ?int $ignorarId = null
    ): ?array {
        $recursos = ['a.tecnico_id = :usuario_id'];
        if ($veiculoId !== null) {
            $recursos[] = 'a.veiculo_id = :veiculo_id';
        }

        $sql = "
            SELECT
                vt.id,
                vt.usuario_id,
                vt.veiculo_id,
                vt.data_visita,
                vt.hora_visita,
                a.hora_inicio,
                a.hora_fim,
                u.nome AS usuario_nome,
                v.modelo AS veiculo_modelo,
                v.placa AS veiculo_placa
            FROM visitas_tecnicas vt
            LEFT JOIN agendas a ON a.id = vt.agenda_id
            LEFT JOIN usuarios u ON u.id = vt.usuario_id
            LEFT JOIN veiculos v ON v.id = vt.veiculo_id
            WHERE vt.data_visita = :data_visita
              AND vt.status NOT IN ('CANCELADA', 'EXCLUIDA', 'FINALIZADA')
              AND (" . implode(' OR ', $recursos) . ")
              AND COALESCE(a.hora_inicio, vt.hora_visita) < :hora_fim
              AND COALESCE(a.hora_fim, ADDTIME(vt.hora_visita, '01:00:00')) > :hora_inicio
        ";

        if ($ignorarId !== null) {
            $sql .= ' AND vt.id <> :ignorar_id';
        }

        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':data_visita', $dataVisita);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':hora_inicio', $horaInicio);
        $stmt->bindValue(':hora_fim', $horaFim);

        if ($veiculoId !== null) {
            $stmt->bindValue(':veiculo_id', $veiculoId, PDO::PARAM_INT);
        }
        if ($ignorarId !== null) {
            $stmt->bindValue(':ignorar_id', $ignorarId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    /**
     * Mantido apenas para compatibilidade com registros manuais antigos.
     * O fluxo principal cria a visita por meio da Agenda.
     */
    public function salvar(array $dados): bool
    {
        $sql = "
            INSERT INTO visitas_tecnicas (
                empresa_id, unidade_id, usuario_id, data_visita,
                hora_visita, veiculo_id, responsavel_acompanhamento,
                objetivo, observacoes, status
            ) VALUES (
                :empresa_id, :unidade_id, :usuario_id, :data_visita,
                :hora_visita, :veiculo_id, :responsavel_acompanhamento,
                :objetivo, :observacoes, :status
            )
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':empresa_id' => (int)$dados['empresa_id'],
            ':unidade_id' => !empty($dados['unidade_id']) ? (int)$dados['unidade_id'] : null,
            ':usuario_id' => (int)$dados['usuario_id'],
            ':data_visita' => $dados['data_visita'],
            ':hora_visita' => $this->obterHoraVisita($dados),
            ':veiculo_id' => !empty($dados['veiculo_id']) ? (int)$dados['veiculo_id'] : null,
            ':responsavel_acompanhamento' => $this->normalizarTexto($dados['responsavel_acompanhamento'] ?? null),
            ':objetivo' => $this->normalizarTexto($dados['objetivo'] ?? null),
            ':observacoes' => $this->normalizarTexto($dados['observacoes'] ?? null),
            ':status' => in_array(($dados['status'] ?? ''), self::STATUS_VALIDOS, true)
                ? $dados['status']
                : 'ABERTA',
        ]);
    }

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
                responsavel_acompanhamento = :responsavel_acompanhamento,
                objetivo = :objetivo,
                observacoes = :observacoes,
                atualizado_em = NOW()
            WHERE id = :id
              AND agenda_id IS NULL
              AND status IN ('ABERTA', 'AGENDADA', 'CONFIRMADA')
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':empresa_id' => (int)$dados['empresa_id'],
            ':unidade_id' => !empty($dados['unidade_id']) ? (int)$dados['unidade_id'] : null,
            ':usuario_id' => (int)$dados['usuario_id'],
            ':data_visita' => $dados['data_visita'],
            ':hora_visita' => $this->obterHoraVisita($dados),
            ':veiculo_id' => !empty($dados['veiculo_id']) ? (int)$dados['veiculo_id'] : null,
            ':responsavel_acompanhamento' => $this->normalizarTexto($dados['responsavel_acompanhamento'] ?? null),
            ':objetivo' => $this->normalizarTexto($dados['objetivo'] ?? null),
            ':observacoes' => $this->normalizarTexto($dados['observacoes'] ?? null),
        ]);
    }

    /**
     * Alterações de data de visitas vinculadas devem ocorrer por reagendamento
     * na Agenda, para manter histórico e sincronização.
     */
    public function updateData(int $id, string $novaData): bool
    {
        $stmt = $this->db->prepare("
            UPDATE visitas_tecnicas
            SET data_visita = :data_visita, atualizado_em = NOW()
            WHERE id = :id
              AND agenda_id IS NULL
              AND status IN ('ABERTA', 'AGENDADA', 'CONFIRMADA')
        ");

        return $stmt->execute([':id' => $id, ':data_visita' => $novaData]);
    }

    public function atualizarData(int $id, string $novaData): bool
    {
        return $this->updateData($id, $novaData);
    }

    public function deletar(int $id): bool
    {
        return $this->atualizarStatus($id, 'EXCLUIDA');
    }

    public function atualizarStatus(int $id, string $status): bool
    {
        $status = strtoupper(trim($status));
        if (!in_array($status, self::STATUS_VALIDOS, true)) {
            return false;
        }

        $camposTempo = '';
        if (in_array($status, ['EM_ANDAMENTO', 'CHECKLIST_INICIADO'], true)) {
            $camposTempo = ', iniciado_em = COALESCE(iniciado_em, NOW())';
        } elseif ($status === 'FINALIZADA') {
            $camposTempo = ', finalizado_em = COALESCE(finalizado_em, NOW())';
        }

        $stmt = $this->db->prepare("
            UPDATE visitas_tecnicas
            SET status = :status, atualizado_em = NOW() {$camposTempo}
            WHERE id = :id
        ");

        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function registrarHistorico(
        int $visitaId,
        ?int $usuarioId,
        string $acao,
        ?string $statusAnterior = null,
        ?string $statusNovo = null,
        ?string $motivo = null
    ): bool {
        $stmt = $this->db->prepare("
            INSERT INTO visita_historico (
                visita_id, usuario_id, acao,
                status_anterior, status_novo, motivo
            ) VALUES (
                :visita_id, :usuario_id, :acao,
                :status_anterior, :status_novo, :motivo
            )
        ");

        return $stmt->execute([
            ':visita_id' => $visitaId,
            ':usuario_id' => $usuarioId,
            ':acao' => $acao,
            ':status_anterior' => $statusAnterior,
            ':status_novo' => $statusNovo,
            ':motivo' => $this->normalizarTexto($motivo),
        ]);
    }

    public function listarHistorico(int $visitaId): array
    {
        $stmt = $this->db->prepare("
            SELECT vh.*, u.nome AS usuario_nome
            FROM visita_historico vh
            LEFT JOIN usuarios u ON u.id = vh.usuario_id
            WHERE vh.visita_id = :visita_id
            ORDER BY vh.criado_em DESC, vh.id DESC
        ");
        $stmt->bindValue(':visita_id', $visitaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function selectBase(): string
    {
        return "
            SELECT
                vt.*,
                a.id AS agenda_ref_id,
                a.status AS agenda_status,
                a.prioridade AS prioridade,
                a.titulo AS agenda_titulo,
                a.hora_inicio AS hora_inicio,
                a.hora_fim AS hora_fim,
                u.nome AS usuario_nome,
                u.nome AS tecnico_nome,
                v.modelo AS veiculo_modelo,
                v.placa AS veiculo_placa,
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
                uni.nome AS unidade_nome,
                uni.razao_social AS unidade_razao_social,
                uni.nome_fantasia AS unidade_fantasia,
                uni.cnpj AS unidade_cnpj,
                uni.endereco AS unidade_endereco,
                uni.numero AS unidade_numero,
                uni.complemento AS unidade_complemento,
                uni.bairro AS unidade_bairro,
                uni.cidade AS unidade_cidade,
                uni.estado AS unidade_uf,
                uni.cep AS unidade_cep,
                cv.id AS checklist_id,
                cv.status AS checklist_status,
                cv.data_inicio AS checklist_iniciado_em,
                cv.data_fim AS checklist_finalizado_em
            FROM visitas_tecnicas vt
            INNER JOIN usuarios u ON u.id = vt.usuario_id
            INNER JOIN empresas e ON e.id = vt.empresa_id
            LEFT JOIN agendas a ON a.id = vt.agenda_id
            LEFT JOIN veiculos v ON v.id = vt.veiculo_id
            LEFT JOIN unidades uni ON uni.id = vt.unidade_id
            LEFT JOIN checklists_visita cv ON cv.visita_id = vt.id
        ";
    }

    private function montarEscopoUsuario(int $usuarioId, string $tipoUsuario, array &$parametros): string
    {
        $where = ' WHERE 1 = 1';

        if (!$this->usuarioAdministrador($tipoUsuario)) {
            $where .= ' AND vt.usuario_id = :usuario_logado_id';
            $parametros[':usuario_logado_id'] = $usuarioId;
        }

        return $where;
    }

    private function usuarioAdministrador(string $tipoUsuario): bool
    {
        $tipo = strtoupper(trim($tipoUsuario));
        return in_array($tipo, ['ADMIN', 'ADMINISTRADOR'], true);
    }

    private function obterHoraVisita(array $dados): ?string
    {
        $hora = trim((string)($dados['hora_visita'] ?? $dados['hora_inicio'] ?? ''));
        return $hora !== '' ? $hora : null;
    }

    private function normalizarTexto(?string $valor): ?string
    {
        $valor = trim((string)$valor);
        return $valor !== '' ? $valor : null;
    }
}
