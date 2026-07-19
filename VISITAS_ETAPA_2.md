# NEXUS SST — Etapa 2: Visitas Técnicas e início do Check-list

## Objetivo

Conectar o fluxo já implementado na Agenda à fila operacional de Visitas Técnicas, sem alterar a identidade visual do sistema.

## Fluxo implementado

1. A Agenda cria a Visita Técnica vinculada.
2. A tela Visitas Técnicas mostra os registros reais do banco.
3. ADMIN visualiza todas as visitas.
4. Os demais usuários visualizam somente as visitas atribuídas ao próprio usuário.
5. A fila Em Aberto é ordenada por prioridade e, depois, data e horário.
6. Ao iniciar o check-list:
   - o check-list é criado ou recuperado;
   - a visita muda para `CHECKLIST_INICIADO`;
   - `iniciado_em` é preenchido;
   - o histórico da visita e da agenda é registrado;
   - a visita deixa a aba Em Aberto;
   - o check-list passa a aparecer em `/checklists`;
   - o FullCalendar passa a mostrar o compromisso em amarelo por meio do `status_visual` já existente na Agenda.

## Segurança e consistência do fluxo

- O início do check-list aceita somente POST.
- Somente o TST responsável ou o ADMIN pode iniciar e acessar o check-list.
- Visitas canceladas ou excluídas não podem iniciar check-list.
- Data, técnico, empresa e veículo devem ser alterados pela Agenda, evitando duas fontes de informação.
- Cancelamento e exclusão devem permanecer na Agenda, onde o motivo e o histórico são obrigatórios.

## Arquivos principais alterados

- `app/controllers/VisitasController.php`
- `app/models/Visita.php`
- `app/controllers/ChecklistsController.php`
- `app/models/ChecklistVisita.php`
- `app/views/visitas/index.php`
- `app/views/visitas/visualizar.php`
- `app/views/checklists/index.php`
- `app/views/checklists/visualizar.php`
- `routes/web.php`

## Migration para registros antigos

Execute, nessa ordem, apenas quando necessário:

1. `database/migrations/2026_07_19_integracao_agenda_visita.sql`
2. `database/migrations/2026_07_19_backfill_visitas_agendamentos.sql`

O segundo script gera visitas para agendamentos antigos que ainda não possuem vínculo. Ele não duplica os registros já integrados.

## Identidade visual

Nenhum arquivo CSS foi alterado. Foram preservadas as classes, cores, componentes, header, footer, sidebar, cards, botões, abas e estrutura responsiva existentes.

## Validação

Todos os 207 arquivos PHP fora da pasta `vendor` foram validados com `php -l`, sem erros de sintaxe.

## Próxima etapa recomendada

Implementar as abas funcionais do check-list, começando por Hierarquia, Funcionários e GHE/Riscos, com salvamento parcial e cálculo real de progresso. A finalização do check-list deve ser habilitada somente depois dessas estruturas estarem persistidas.
