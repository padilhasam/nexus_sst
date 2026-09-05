# NEXUS SST — Etapa 3: Hierarquia, Funcionários e GHE/Riscos

## Objetivo

Tornar funcionais as três primeiras etapas operacionais do check-list, mantendo o layout aprovado e a integração já existente entre Agenda, Visita Técnica e Check-list.

## Funcionalidades entregues

### Hierarquia

- listagem da estrutura da empresa/unidade utilizada na visita;
- vínculo de setor e cargo já cadastrados;
- criação rápida de novo setor e/ou cargo dentro do check-list;
- exibição de código, CBO e quantidade de funcionários ativos por vínculo;
- bloqueio de alteração após conclusão ou cancelamento do check-list.

### Funcionários

- inclusão de funcionário diretamente na hierarquia selecionada;
- matrícula, código interno, CPF, admissão e observações;
- listagem de ativos e inativos;
- inativação com data e motivo, sem excluir o histórico;
- preservação do vínculo com empresa, unidade, setor e cargo.

### GHE e riscos

- criação de GHE com código, nome, descrição e observações;
- vínculo de um GHE a um ou mais cargos da hierarquia;
- aplicação de riscos por categoria;
- registro de fonte geradora, meio de propagação, frequência, tempo de exposição e intensidade;
- sinalização visual de riscos quantificáveis;
- remoção controlada de risco;
- inativação de GHE com preservação histórica.

### Segurança e experiência de uso

- CSRF em todas as gravações;
- operações de alteração exclusivamente por POST;
- confirmação para inativação e remoção;
- prevenção de envio duplicado dos formulários;
- validação de pertencimento do GHE e do risco ao check-list;
- máscara de CPF no cadastro em campo;
- responsividade preservada para tablet e celular.

## Banco de dados

Para atualizar uma instalação criada antes desta etapa, execute:

`database/migrations/2026_07_21_checklist_hierarquia_funcionarios_ghe.sql`

O arquivo `nexus.sql` completo já contém as tabelas e colunas desta etapa.

## Arquivos principais alterados

- `app/controllers/ChecklistsController.php`
- `app/models/GHE.php`
- `app/views/checklists/visualizar.php`
- `app/views/checklists/partials/hierarquia.php`
- `app/views/checklists/partials/funcionarios.php`
- `app/views/checklists/partials/ghe-riscos.php`
- `public/css/pages/checklists.css`
- `public/js/pages/checklists.js`
- `routes/web.php`

## Próxima etapa recomendada

Implementar EPI/EPC e Evidências com vínculo flexível ao GHE e ao risco; em seguida, gerar automaticamente as solicitações de quantificação para cada risco configurado como quantificável.
