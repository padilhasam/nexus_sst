<?php

require_once __DIR__ . '/../../core/Database.php';

class Visita {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function listarTodos() {
        $sql = "SELECT 
                    vt.*, 
                    u.nome AS usuario_nome, 
                    v.modelo AS veiculo_modelo, 
                    v.placa AS veiculo_placa,
                    e.razao_social AS empresa_nome,
                    e.nome_fantasia AS empresa_fantasia,
                    uni.nome AS unidade_nome
                FROM visitas_tecnicas vt
                INNER JOIN usuarios u ON vt.usuario_id = u.id
                INNER JOIN empresas e ON vt.empresa_id = e.id
                LEFT JOIN veiculos v ON vt.veiculo_id = v.id
                LEFT JOIN unidades uni ON vt.unidade_id = uni.id
                WHERE vt.status <> 'EXCLUIDA'
                ORDER BY vt.data_visita DESC, vt.hora_inicio DESC, vt.criado_em DESC";
                
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeConflitoIntervalo($usuarioId, $veiculoId, $dataVisita, $horaInicio, $horaFim, $ignorarId = null)
    {
        $sql = "SELECT 
                    vt.id,
                    vt.usuario_id,
                    vt.veiculo_id,
                    vt.data_visita,
                    vt.hora_inicio,
                    vt.hora_fim,
                    u.nome AS usuario_nome,
                    v.modelo AS veiculo_modelo,
                    v.placa AS veiculo_placa
                FROM visitas_tecnicas vt
                LEFT JOIN usuarios u ON vt.usuario_id = u.id
                LEFT JOIN veiculos v ON vt.veiculo_id = v.id
                WHERE vt.data_visita = :data_visita
                AND vt.status NOT IN ('CANCELADA', 'EXCLUIDA', 'FINALIZADA')
                AND vt.hora_inicio IS NOT NULL
                AND vt.hora_fim IS NOT NULL
                AND (
                        vt.usuario_id = :usuario_id";

        if (!empty($veiculoId)) {
            $sql .= " OR vt.veiculo_id = :veiculo_id";
        }

        $sql .= "
                )
                AND (
                        :hora_inicio < vt.hora_fim
                        AND :hora_fim > vt.hora_inicio
                )";

        if ($ignorarId) {
            $sql .= " AND vt.id <> :ignorar_id";
        }

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':data_visita', $dataVisita);
        $stmt->bindValue(':usuario_id', (int)$usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':hora_inicio', $horaInicio);
        $stmt->bindValue(':hora_fim', $horaFim);

        if (!empty($veiculoId)) {
            $stmt->bindValue(':veiculo_id', (int)$veiculoId, PDO::PARAM_INT);
        }

        if ($ignorarId) {
            $stmt->bindValue(':ignorar_id', (int)$ignorarId, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar($dados) {
        $sql = "INSERT INTO visitas_tecnicas 
                    (
                        empresa_id, 
                        unidade_id, 
                        usuario_id, 
                        data_visita, 
                        hora_inicio, 
                        hora_fim, 
                        veiculo_id, 
                        responsavel_acompanhamento, 
                        objetivo, 
                        observacoes, 
                        status
                    ) 
                VALUES 
                    (
                        :empresa_id, 
                        :unidade_id, 
                        :usuario_id, 
                        :data_visita, 
                        :hora_inicio, 
                        :hora_fim, 
                        :veiculo_id, 
                        :responsavel_acompanhamento, 
                        :objetivo, 
                        :observacoes, 
                        'ABERTA'
                    )";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':empresa_id'                 => $dados['empresa_id'],
            ':unidade_id'                 => !empty($dados['unidade_id']) ? $dados['unidade_id'] : null,
            ':usuario_id'                 => $dados['usuario_id'],
            ':data_visita'                => $dados['data_visita'],
            ':hora_inicio'                => $dados['hora_inicio'],
            ':hora_fim'                   => $dados['hora_fim'],
            ':veiculo_id'                 => !empty($dados['veiculo_id']) ? $dados['veiculo_id'] : null,
            ':responsavel_acompanhamento' => !empty($dados['responsavel_acompanhamento']) ? $dados['responsavel_acompanhamento'] : null,
            ':objetivo'                   => !empty($dados['objetivo']) ? $dados['objetivo'] : null,
            ':observacoes'                => !empty($dados['observacoes']) ? $dados['observacoes'] : null
        ]);
    }

    public function updateData($id, $novaData) {
        $sql = "UPDATE visitas_tecnicas 
                SET data_visita = :data_visita 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id'          => (int)$id,
            ':data_visita' => $novaData
        ]);
    }

    public function buscarPorId($id) {
        $sql = "SELECT 
                    vt.*, 
                    u.nome AS usuario_nome, 
                    v.modelo AS veiculo_modelo, 
                    v.placa AS veiculo_placa,
                    e.razao_social AS empresa_nome,
                    e.nome_fantasia AS empresa_fantasia,
                    uni.nome AS unidade_nome
                FROM visitas_tecnicas vt
                INNER JOIN usuarios u ON vt.usuario_id = u.id
                INNER JOIN empresas e ON vt.empresa_id = e.id
                LEFT JOIN veiculos v ON vt.veiculo_id = v.id
                LEFT JOIN unidades uni ON vt.unidade_id = uni.id
                WHERE vt.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE visitas_tecnicas SET 
                    empresa_id = :empresa_id,
                    unidade_id = :unidade_id,
                    usuario_id = :usuario_id,
                    data_visita = :data_visita,
                    hora_inicio = :hora_inicio,
                    hora_fim = :hora_fim,
                    veiculo_id = :veiculo_id,
                    responsavel_acompanhamento = :responsavel_acompanhamento,
                    objetivo = :objetivo,
                    observacoes = :observacoes
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id'                         => (int)$id,
            ':empresa_id'                 => $dados['empresa_id'],
            ':unidade_id'                 => !empty($dados['unidade_id']) ? $dados['unidade_id'] : null,
            ':usuario_id'                 => $dados['usuario_id'],
            ':data_visita'                => $dados['data_visita'],
            ':hora_inicio'                => $dados['hora_inicio'],
            ':hora_fim'                   => $dados['hora_fim'],
            ':veiculo_id'                 => !empty($dados['veiculo_id']) ? $dados['veiculo_id'] : null,
            ':responsavel_acompanhamento' => $dados['responsavel_acompanhamento'] ?? null,
            ':objetivo'                   => $dados['objetivo'] ?? null,
            ':observacoes'                => $dados['observacoes'] ?? null
        ]);
    }

    public function deletar($id)
    {
        $sql = "UPDATE visitas_tecnicas 
                SET status = 'EXCLUIDA' 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => (int)$id
        ]);
    }

    public function atualizarStatus($id, $status) {
        $sql = "UPDATE visitas_tecnicas 
                SET status = :status 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':status' => $status, 
            ':id' => (int)$id
        ]);
    }

    public function registrarHistorico($visitaId, $usuarioId, $acao, $statusAnterior = null, $statusNovo = null, $motivo = null)
    {
        $sql = "INSERT INTO visita_historico 
                (visita_id, usuario_id, acao, status_anterior, status_novo, motivo)
                VALUES 
                (:visita_id, :usuario_id, :acao, :status_anterior, :status_novo, :motivo)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':visita_id'       => (int)$visitaId,
            ':usuario_id'      => $usuarioId ? (int)$usuarioId : null,
            ':acao'            => $acao,
            ':status_anterior' => $statusAnterior,
            ':status_novo'     => $statusNovo,
            ':motivo'          => $motivo
        ]);
    }
}