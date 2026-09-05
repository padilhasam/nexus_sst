# NEXUS SST — pacote de desenvolvimento

Sistema MVC em PHP para Agenda, Visitas Técnicas, Check-lists, GHEs, riscos ocupacionais, quantificações e não conformidades.

## Etapas funcionais incluídas

1. **Agenda integrada à Visita Técnica**
   - criação transacional de Agenda + Visita;
   - histórico de alterações, reagendamento, cancelamento, exclusão e conclusão;
   - status visual sincronizado com o FullCalendar.

2. **Fila de Visitas e início do Check-list**
   - ordenação por prioridade e data;
   - acesso por técnico responsável e administrador;
   - início do check-list com mudança automática da visita para andamento.

3. **Hierarquia, Funcionários e GHE/Riscos**
   - montagem da estrutura da unidade dentro do check-list;
   - inclusão e inativação histórica de funcionários;
   - criação de GHEs, vínculo com cargos e aplicação de riscos;
   - validação mínima antes da finalização do check-list.

## Identidade visual

O shell aprovado com sidebar, topbar, cards, abas e responsividade foi preservado. As novas funcionalidades utilizam as classes do design system existente em `public/css/pages/checklists.css`.

## Banco de dados

Para uma instalação nova, importe `nexus.sql`.

Para atualizar uma instalação anterior, execute as migrations em ordem:

1. `database/migrations/2026_07_19_integracao_agenda_visita.sql`
2. `database/migrations/2026_07_19_backfill_visitas_agendamentos.sql` — somente para registros antigos sem visita vinculada
3. `database/migrations/2026_07_21_checklist_hierarquia_funcionarios_ghe.sql`

## Documentação das etapas

- `AGENDA_ETAPA_1.md`
- `VISITAS_ETAPA_2.md`
- `CHECKLIST_ETAPA_3.md`

## Próximos módulos

As abas de EPI/EPC, Evidências, Fiscalização, Não Conformidades, Assinaturas e a geração automática das quantificações ainda devem ser implementadas sobre a estrutura já preparada no banco.
