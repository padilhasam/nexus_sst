-- NEXUS SST
-- Integração transacional entre Agenda e Visitas Técnicas.
-- Execute este arquivo SOMENTE se o banco em uso ainda possuir a versão antiga
-- da tabela visitas_tecnicas, sem agenda_id e campos de acompanhamento.
-- O arquivo nexus.sql atualizado já contém essa estrutura.

ALTER TABLE visitas_tecnicas
    ADD COLUMN agenda_id INT NULL AFTER id,
    ADD COLUMN iniciado_em DATETIME NULL AFTER status,
    ADD COLUMN finalizado_em DATETIME NULL AFTER iniciado_em,
    ADD COLUMN atualizado_por INT NULL AFTER finalizado_em,
    ADD COLUMN atualizado_em DATETIME NULL AFTER atualizado_por,
    ADD UNIQUE KEY uk_visita_agenda (agenda_id),
    ADD KEY idx_visita_status_data (status, data_visita, hora_visita),
    ADD KEY idx_visita_usuario_status (usuario_id, status),
    ADD KEY fk_visita_atualizado_por (atualizado_por),
    ADD CONSTRAINT fk_visita_agenda
        FOREIGN KEY (agenda_id) REFERENCES agendas(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_visita_atualizado_por
        FOREIGN KEY (atualizado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE;

-- Vincula visitas que já possuam correspondência explícita na agenda.
UPDATE visitas_tecnicas vt
INNER JOIN agendas a ON a.visita_tecnica_id = vt.id
SET vt.agenda_id = a.id
WHERE vt.agenda_id IS NULL;
