-- NEXUS SST
-- Gera visitas técnicas para agendamentos antigos que ainda não possuem vínculo.
-- Execute depois de 2026_07_19_integracao_agenda_visita.sql quando necessário.
-- O script é idempotente: agendamentos já vinculados não serão duplicados.

START TRANSACTION;

DROP TEMPORARY TABLE IF EXISTS tmp_agendas_sem_visita;

CREATE TEMPORARY TABLE tmp_agendas_sem_visita AS
SELECT a.id AS agenda_id
FROM agendas a
LEFT JOIN visitas_tecnicas vt ON vt.agenda_id = a.id
WHERE a.visita_tecnica_id IS NULL
  AND vt.id IS NULL
  AND a.status <> 'EXCLUIDO';

INSERT INTO visitas_tecnicas (
    agenda_id,
    empresa_id,
    unidade_id,
    usuario_id,
    data_visita,
    hora_visita,
    veiculo_id,
    responsavel_acompanhamento,
    objetivo,
    observacoes,
    status,
    iniciado_em,
    finalizado_em,
    atualizado_por,
    atualizado_em,
    criado_em
)
SELECT
    a.id,
    a.empresa_id,
    a.unidade_id,
    a.tecnico_id,
    a.data_agendada,
    a.hora_inicio,
    a.veiculo_id,
    a.responsavel_acompanhamento,
    a.objetivo,
    a.observacoes,
    CASE a.status
        WHEN 'CONFIRMADO' THEN 'CONFIRMADA'
        WHEN 'CANCELADO' THEN 'CANCELADA'
        WHEN 'CONCLUIDO' THEN 'FINALIZADA'
        ELSE 'AGENDADA'
    END,
    CASE WHEN a.status = 'CONCLUIDO' THEN a.atualizado_em ELSE NULL END,
    CASE WHEN a.status = 'CONCLUIDO' THEN a.atualizado_em ELSE NULL END,
    COALESCE(a.atualizado_por, a.criado_por),
    a.atualizado_em,
    a.criado_em
FROM agendas a
INNER JOIN tmp_agendas_sem_visita tmp ON tmp.agenda_id = a.id;

UPDATE agendas a
INNER JOIN visitas_tecnicas vt ON vt.agenda_id = a.id
INNER JOIN tmp_agendas_sem_visita tmp ON tmp.agenda_id = a.id
SET a.visita_tecnica_id = vt.id
WHERE a.visita_tecnica_id IS NULL;

INSERT INTO visita_historico (
    visita_id,
    usuario_id,
    acao,
    status_anterior,
    status_novo,
    motivo
)
SELECT
    vt.id,
    COALESCE(a.atualizado_por, a.criado_por),
    'MIGRACAO_AGENDA',
    NULL,
    vt.status,
    'Visita técnica gerada para agendamento existente durante a integração dos módulos.'
FROM tmp_agendas_sem_visita tmp
INNER JOIN agendas a ON a.id = tmp.agenda_id
INNER JOIN visitas_tecnicas vt ON vt.agenda_id = a.id;

INSERT INTO agenda_historico (
    agenda_id,
    usuario_id,
    acao,
    descricao,
    dados_novos
)
SELECT
    a.id,
    COALESCE(a.atualizado_por, a.criado_por),
    'VISITA_GERADA',
    'Visita técnica gerada durante a integração dos módulos Agenda e Visitas Técnicas.',
    JSON_OBJECT('visita_tecnica_id', vt.id, 'origem', 'MIGRACAO')
FROM tmp_agendas_sem_visita tmp
INNER JOIN agendas a ON a.id = tmp.agenda_id
INNER JOIN visitas_tecnicas vt ON vt.agenda_id = a.id;

DROP TEMPORARY TABLE IF EXISTS tmp_agendas_sem_visita;

COMMIT;
