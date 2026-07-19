# Agenda — Etapa 1

Arquivos alterados:

- `app/controllers/AgendasController.php`
- `app/models/Agenda.php`
- `routes/web.php`
- `nexus.sql`
- `database/migrations/2026_07_19_integracao_agenda_visita.sql`

## Entregas desta etapa

- criação transacional de Agenda + Visita Técnica;
- vínculo bidirecional `agendas.visita_tecnica_id` e `visitas_tecnicas.agenda_id`;
- histórico de criação, alteração, reagendamento, cancelamento, exclusão e conclusão;
- sincronização dos dados administrativos da Agenda com a Visita;
- bloqueio de edição após início do check-list;
- cancelamento e exclusão lógica sincronizados;
- conclusão somente após `visitas_tecnicas.status = FINALIZADA`;
- endpoint POST `/agenda/reagendar/{id}` preparado para o modal de reagendamento;
- permissão de alteração para administrador ou técnico responsável;
- visualização geral da agenda preservada;
- identidade visual, views, CSS e componentes não foram modificados.

## Observação

A tela atual de edição ainda não possui o modal de reagendamento. Por isso, alterações de data ou horário são bloqueadas na edição comum e devem ser enviadas ao endpoint de reagendamento com os campos:

- `data_agendada`
- `hora_inicio`
- `hora_fim`
- `motivo`
