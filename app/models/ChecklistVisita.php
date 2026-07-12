<?php

class ChecklistVisita extends Model
{
    public function buscarPorVisita($visitaId)
    {
        $sql = "SELECT * FROM checklists_visita WHERE visita_id = :visita_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':visita_id', $visitaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function iniciarPorVisita($visitaId, $usuarioId)
    {
        $this->db->beginTransaction();

        try {
            $sqlVisita = "SELECT * FROM visitas_tecnicas WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sqlVisita);
            $stmt->bindValue(':id', $visitaId, PDO::PARAM_INT);
            $stmt->execute();
            $visita = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$visita) {
                throw new Exception('Visita não encontrada.');
            }

            $checklistExistente = $this->buscarPorVisita($visitaId);

            if ($checklistExistente) {
                $this->db->commit();
                return $checklistExistente['id'];
            }

            $statusAnterior = $visita['status'];

            $sqlChecklist = "
                INSERT INTO checklists_visita 
                (visita_id, empresa_id, unidade_id, usuario_id, responsavel_acompanhamento, status)
                VALUES 
                (:visita_id, :empresa_id, :unidade_id, :usuario_id, :responsavel_acompanhamento, 'EM_ANDAMENTO')
            ";

            $stmt = $this->db->prepare($sqlChecklist);
            $stmt->bindValue(':visita_id', $visita['id'], PDO::PARAM_INT);
            $stmt->bindValue(':empresa_id', $visita['empresa_id'], PDO::PARAM_INT);
            $stmt->bindValue(':unidade_id', $visita['unidade_id'], $visita['unidade_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':responsavel_acompanhamento', $visita['responsavel_acompanhamento']);
            $stmt->execute();

            $checklistId = $this->db->lastInsertId();

            $sqlUpdate = "
                UPDATE visitas_tecnicas 
                SET status = 'CHECKLIST_INICIADO' 
                WHERE id = :id
            ";

            $stmt = $this->db->prepare($sqlUpdate);
            $stmt->bindValue(':id', $visitaId, PDO::PARAM_INT);
            $stmt->execute();

            $sqlHistorico = "
                INSERT INTO visita_historico 
                (visita_id, usuario_id, acao, status_anterior, status_novo, motivo)
                VALUES
                (:visita_id, :usuario_id, 'INICIO_CHECKLIST', :status_anterior, 'CHECKLIST_INICIADO', 'Checklist iniciado pelo técnico.')
            ";

            $stmt = $this->db->prepare($sqlHistorico);
            $stmt->bindValue(':visita_id', $visitaId, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':status_anterior', $statusAnterior);
            $stmt->execute();

            $this->db->commit();

            return $checklistId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function buscarDadosTela($checklistId)
    {
        $sql = "
            SELECT 
                cv.*,
                vt.data_visita,
                vt.hora_visita,
                vt.objetivo,
                e.razao_social AS empresa_nome,
                e.cnpj AS empresa_cnpj,
                u.razao_social AS unidade_nome,
                u.cnpj AS unidade_cnpj
            FROM checklists_visita cv
            INNER JOIN visitas_tecnicas vt ON vt.id = cv.visita_id
            INNER JOIN empresas e ON e.id = cv.empresa_id
            LEFT JOIN unidades u ON u.id = cv.unidade_id
            WHERE cv.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $checklistId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}