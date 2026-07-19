<?php

require_once __DIR__ . '/../helpers/ToastHelper.php';

class AgendasController extends Controller
{
    private Agenda $agendaModel;

    private const STATUS_EDITAVEIS = [
        'AGENDADO',
        'CONFIRMADO',
        'REAGENDADO',
    ];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->agendaModel = $this->model('Agenda');
    }

    public function index(): void
    {
        $filtros = $this->obterFiltros();
        $paginaAtual = max(1, filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1);
        $porPaginaPermitidos = [10, 20, 50];
        $porPagina = filter_input(INPUT_GET, 'por_pagina', FILTER_VALIDATE_INT) ?: 10;

        if (!in_array($porPagina, $porPaginaPermitidos, true)) {
            $porPagina = 10;
        }

        $listas = $this->obterListasFormulario();
        $totalRegistros = $this->agendaModel->contarTodos($filtros);
        $totalPaginas = max(1, (int)ceil($totalRegistros / $porPagina));
        $paginaAtual = min($paginaAtual, $totalPaginas);

        $eventosJson = json_encode(
            $this->agendaModel->eventosCalendario($filtros),
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_INVALID_UTF8_SUBSTITUTE
        );

        $this->view('agenda/index', [
            'rotaAtual' => 'agenda',
            'pageTitle' => 'Agenda',
            'pageSubtitle' => 'Calendário e acompanhamento completo dos agendamentos técnicos.',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => BASE_URL . '/dashboard'],
                ['label' => 'Agenda', 'url' => null],
            ],
            'pageActionUrl' => BASE_URL . '/agenda/criar',
            'pageActionLabel' => 'Novo Agendamento',
            'pageActionIcon' => 'fa-solid fa-plus',
            'css' => 'agenda.css',
            'agendamentos' => $this->agendaModel->listarPaginado(
                $filtros,
                $paginaAtual,
                $porPagina
            ),
            'eventosJson' => $eventosJson !== false ? $eventosJson : '[]',
            'indicadores' => $this->agendaModel->obterIndicadores($filtros),
            'empresas' => $listas['empresas'],
            'usuarios' => $listas['usuarios'],
            'filtros' => $filtros,
            'paginacao' => [
                'pagina_atual' => $paginaAtual,
                'por_pagina' => $porPagina,
                'total_registros' => $totalRegistros,
                'total_paginas' => $totalPaginas,
                'opcoes_por_pagina' => $porPaginaPermitidos,
            ],
        ]);
    }

    public function criar(): void
    {
        $this->exigirPerfilEscrita();
        $listas = $this->obterListasFormulario();

        $this->view('agenda/criar', [
            'rotaAtual' => 'agenda',
            'pageTitle' => 'Novo Agendamento',
            'pageSubtitle' => 'Cadastre um novo agendamento técnico.',
            'css' => 'agenda.css',
            'empresas' => $listas['empresas'],
            'unidades' => $listas['unidades'],
            'usuarios' => $listas['usuarios'],
            'veiculos' => $listas['veiculos'],
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPost('/agenda');
        $this->exigirPerfilEscrita();

        $dados = $this->dadosFormulario();
        $dados['status'] = 'AGENDADO';
        $dados['tecnico_id'] = $this->normalizarTecnicoResponsavel($dados['tecnico_id']);

        if (!$this->validarDados($dados) || !$this->validarReferencias($dados)) {
            $this->redirecionar('/agenda/criar');
        }

        try {
            $conflito = $this->agendaModel->existeConflitoIntervalo(
                (int)$dados['tecnico_id'],
                $dados['veiculo_id'],
                $dados['data_agendada'],
                $dados['hora_inicio'],
                $dados['hora_fim']
            );

            if ($conflito) {
                ToastHelper::warning($this->mensagemConflito($conflito));
                $this->redirecionar('/agenda/criar');
            }

            $id = $this->agendaModel->salvar($dados, $this->usuarioLogadoId());
            if (!$id) {
                throw new RuntimeException('Não foi possível cadastrar o agendamento.');
            }

            ToastHelper::success('Agendamento e visita técnica cadastrados com sucesso.');
            $this->redirecionar('/agenda');
        } catch (Throwable $erro) {
            $this->registrarErroInterno($erro);
            ToastHelper::error('Erro ao cadastrar o agendamento. Nenhum registro foi gravado.');
            $this->redirecionar('/agenda/criar');
        }
    }

    public function visualizar($id = null): void
    {
        $id = $this->validarId($id);
        $agendamento = $this->obterAgendamentoOuRedirecionar($id);

        $this->view('agenda/visualizar', [
            'rotaAtual' => 'agenda',
            'pageTitle' => 'Detalhes do Agendamento',
            'pageSubtitle' => 'Consulta das informações do agendamento.',
            'css' => 'agenda.css',
            'agendamento' => $agendamento,
            'historico' => $this->agendaModel->buscarHistorico($id),
        ]);
    }

    public function editar($id = null): void
    {
        $id = $this->validarId($id);
        $agendamento = $this->obterAgendamentoOuRedirecionar($id);
        $this->exigirPermissaoNoAgendamento($agendamento);
        $this->exigirAgendaEditavel($agendamento);
        $listas = $this->obterListasFormulario();

        $this->view('agenda/editar', [
            'rotaAtual' => 'agenda',
            'pageTitle' => 'Editar Agendamento',
            'pageSubtitle' => 'Atualize as informações do agendamento.',
            'css' => 'agenda.css',
            'agendamento' => $agendamento,
            'empresas' => $listas['empresas'],
            'unidades' => $listas['unidades'],
            'usuarios' => $listas['usuarios'],
            'veiculos' => $listas['veiculos'],
        ]);
    }

    /**
     * Atualiza apenas os dados administrativos. Mudanças de data/horário
     * devem passar pela ação reagendar, que exige motivo.
     */
    public function atualizar($id = null): void
    {
        $this->exigirPost('/agenda');
        $id = $this->validarId($id);
        $atual = $this->obterAgendamentoOuRedirecionar($id);
        $this->exigirPermissaoNoAgendamento($atual);
        $this->exigirAgendaEditavel($atual);

        $dados = $this->dadosFormulario();
        $dados['tecnico_id'] = $this->normalizarTecnicoResponsavel($dados['tecnico_id']);
        $dados['status'] = strtoupper((string)$atual['status']);

        if ($this->horarioFoiAlterado($atual, $dados)) {
            ToastHelper::warning(
                'Para alterar a data ou o horário, utilize o reagendamento e informe o motivo.'
            );
            $this->redirecionar('/agenda/editar/' . $id);
        }

        if (!$this->validarDados($dados) || !$this->validarReferencias($dados)) {
            $this->redirecionar('/agenda/editar/' . $id);
        }

        try {
            $conflito = $this->agendaModel->existeConflitoIntervalo(
                (int)$dados['tecnico_id'],
                $dados['veiculo_id'],
                $dados['data_agendada'],
                $dados['hora_inicio'],
                $dados['hora_fim'],
                $id
            );

            if ($conflito) {
                ToastHelper::warning($this->mensagemConflito($conflito));
                $this->redirecionar('/agenda/editar/' . $id);
            }

            if (!$this->agendaModel->atualizar($id, $dados, $this->usuarioLogadoId())) {
                ToastHelper::error('Não foi possível atualizar o agendamento.');
                $this->redirecionar('/agenda/editar/' . $id);
            }

            ToastHelper::success('Agendamento e visita técnica atualizados com sucesso.');
            $this->redirecionar('/agenda');
        } catch (Throwable $erro) {
            $this->registrarErroInterno($erro);
            ToastHelper::error('Erro ao atualizar o agendamento.');
            $this->redirecionar('/agenda/editar/' . $id);
        }
    }

    /**
     * Endpoint preparado para o modal de reagendamento.
     */
    public function reagendar($id = null): void
    {
        $this->exigirPost('/agenda');
        $id = $this->validarId($id);
        $atual = $this->obterAgendamentoOuRedirecionar($id);
        $this->exigirPermissaoNoAgendamento($atual);
        $this->exigirAgendaEditavel($atual);

        $data = trim($_POST['data_agendada'] ?? '');
        $horaInicio = trim($_POST['hora_inicio'] ?? '');
        $horaFim = trim($_POST['hora_fim'] ?? '');
        $motivo = trim($_POST['motivo'] ?? $_POST['motivo_reagendamento'] ?? '');

        if (!$this->dataValida($data) || !$this->horaValida($horaInicio) || !$this->horaValida($horaFim)) {
            ToastHelper::warning('Informe uma data e horários válidos para o reagendamento.');
            $this->redirecionar('/agenda/editar/' . $id);
        }
        if ($horaInicio >= $horaFim) {
            ToastHelper::warning('O horário final deve ser maior que o horário inicial.');
            $this->redirecionar('/agenda/editar/' . $id);
        }
        if ($motivo === '') {
            ToastHelper::warning('Informe o motivo do reagendamento.');
            $this->redirecionar('/agenda/editar/' . $id);
        }

        try {
            $conflito = $this->agendaModel->existeConflitoIntervalo(
                (int)$atual['tecnico_id'],
                !empty($atual['veiculo_id']) ? (int)$atual['veiculo_id'] : null,
                $data,
                $horaInicio,
                $horaFim,
                $id
            );

            if ($conflito) {
                ToastHelper::warning($this->mensagemConflito($conflito));
                $this->redirecionar('/agenda/editar/' . $id);
            }

            if (!$this->agendaModel->reagendar(
                $id,
                $data,
                $horaInicio,
                $horaFim,
                $motivo,
                $this->usuarioLogadoId()
            )) {
                ToastHelper::error('Não foi possível reagendar o compromisso.');
                $this->redirecionar('/agenda/editar/' . $id);
            }

            ToastHelper::success('Agendamento reagendado com sucesso.');
            $this->redirecionar('/agenda');
        } catch (Throwable $erro) {
            $this->registrarErroInterno($erro);
            ToastHelper::error('Erro ao reagendar o compromisso.');
            $this->redirecionar('/agenda/editar/' . $id);
        }
    }

    public function concluir($id = null): void
    {
        $this->exigirPost('/agenda');
        $id = $this->validarId($id);
        $agendamento = $this->obterAgendamentoOuRedirecionar($id);
        $this->exigirPermissaoNoAgendamento($agendamento);

        if (strtoupper((string)($agendamento['visita_status'] ?? '')) !== 'FINALIZADA') {
            ToastHelper::warning(
                'A agenda será concluída automaticamente após a finalização da visita técnica e do check-list.'
            );
            $this->redirecionar('/agenda');
        }

        try {
            if (!$this->agendaModel->concluir($id, $this->usuarioLogadoId())) {
                ToastHelper::error('Não foi possível concluir o agendamento.');
                $this->redirecionar('/agenda');
            }

            ToastHelper::success('Agendamento concluído com sucesso.');
            $this->redirecionar('/agenda');
        } catch (Throwable $erro) {
            $this->registrarErroInterno($erro);
            ToastHelper::error('Erro ao concluir o agendamento.');
            $this->redirecionar('/agenda');
        }
    }

    public function cancelar($id = null): void
    {
        $this->alterarStatusComMotivo($id, 'cancelamento', 'cancelar');
    }

    public function excluir($id = null): void
    {
        $this->alterarStatusComMotivo($id, 'exclusão', 'excluir');
    }

    private function alterarStatusComMotivo($id, string $operacao, string $metodoModel): void
    {
        $this->exigirPost('/agenda');
        $id = $this->validarId($id);
        $motivo = trim($_POST['motivo'] ?? '');

        if ($motivo === '') {
            ToastHelper::warning('Informe o motivo da ' . $operacao . '.');
            $this->redirecionar('/agenda/editar/' . $id);
        }

        $agendamento = $this->obterAgendamentoOuRedirecionar($id);
        $this->exigirPermissaoNoAgendamento($agendamento);
        $this->exigirAgendaEditavel($agendamento);

        try {
            $ok = $this->agendaModel->{$metodoModel}(
                $id,
                $motivo,
                $this->usuarioLogadoId()
            );

            if (!$ok) {
                ToastHelper::error('Não foi possível realizar a ' . $operacao . ' do agendamento.');
                $this->redirecionar('/agenda/editar/' . $id);
            }

            ToastHelper::success('Agendamento ' . ($metodoModel === 'cancelar' ? 'cancelado' : 'excluído') . ' com sucesso.');
            $this->redirecionar('/agenda');
        } catch (Throwable $erro) {
            $this->registrarErroInterno($erro);
            ToastHelper::error('Erro ao realizar a ' . $operacao . ' do agendamento.');
            $this->redirecionar('/agenda/editar/' . $id);
        }
    }

    private function obterFiltros(): array
    {
        $status = strtoupper(trim($_GET['status'] ?? ''));
        $permitidos = ['AGENDADO', 'CONFIRMADO', 'REAGENDADO', 'CANCELADO', 'CONCLUIDO'];

        return [
            'status' => in_array($status, $permitidos, true) ? $status : '',
            'data_inicio' => $this->normalizarDataFiltro($_GET['data_inicio'] ?? ''),
            'data_fim' => $this->normalizarDataFiltro($_GET['data_fim'] ?? ''),
            'empresa_id' => filter_input(INPUT_GET, 'empresa_id', FILTER_VALIDATE_INT) ?: null,
            'tecnico_id' => filter_input(INPUT_GET, 'tecnico_id', FILTER_VALIDATE_INT) ?: null,
        ];
    }

    private function dadosFormulario(): array
    {
        return [
            'empresa_id' => filter_input(INPUT_POST, 'empresa_id', FILTER_VALIDATE_INT) ?: null,
            'unidade_id' => filter_input(INPUT_POST, 'unidade_id', FILTER_VALIDATE_INT) ?: null,
            'tecnico_id' => filter_input(INPUT_POST, 'tecnico_id', FILTER_VALIDATE_INT) ?: null,
            'veiculo_id' => filter_input(INPUT_POST, 'veiculo_id', FILTER_VALIDATE_INT) ?: null,
            'data_agendada' => trim($_POST['data_agendada'] ?? ''),
            'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
            'hora_fim' => trim($_POST['hora_fim'] ?? ''),
            'titulo' => trim($_POST['titulo'] ?? ''),
            'objetivo' => trim($_POST['objetivo'] ?? ''),
            'observacoes' => trim($_POST['observacoes'] ?? ''),
            'responsavel_acompanhamento' => trim($_POST['responsavel_acompanhamento'] ?? ''),
            'prioridade' => strtoupper(trim($_POST['prioridade'] ?? 'PADRAO')),
            'status' => strtoupper(trim($_POST['status'] ?? 'AGENDADO')),
        ];
    }

    private function validarDados(array $dados): bool
    {
        if (
            empty($dados['empresa_id']) ||
            empty($dados['tecnico_id']) ||
            empty($dados['data_agendada']) ||
            empty($dados['hora_inicio']) ||
            empty($dados['hora_fim'])
        ) {
            ToastHelper::warning('Preencha empresa, técnico, data, horário inicial e horário final.');
            return false;
        }
        if (!$this->dataValida($dados['data_agendada'])) {
            ToastHelper::warning('Informe uma data válida.');
            return false;
        }
        if (!$this->horaValida($dados['hora_inicio']) || !$this->horaValida($dados['hora_fim'])) {
            ToastHelper::warning('Informe horários válidos.');
            return false;
        }
        if ($dados['hora_inicio'] >= $dados['hora_fim']) {
            ToastHelper::warning('O horário final deve ser maior que o horário inicial.');
            return false;
        }
        if (!in_array($dados['prioridade'], ['PADRAO', 'URGENTE', 'CRITICA'], true)) {
            ToastHelper::warning('Prioridade inválida.');
            return false;
        }

        return true;
    }

    private function validarReferencias(array $dados): bool
    {
        $erros = $this->agendaModel->validarReferencias($dados);
        if ($erros === []) {
            return true;
        }

        ToastHelper::warning(implode(' ', $erros));
        return false;
    }

    private function obterListasFormulario(): array
    {
        $db = (new Database())->getConnection();

        $empresas = $db->query("
            SELECT id, razao_social, nome_fantasia
            FROM empresas
            ORDER BY COALESCE(NULLIF(nome_fantasia, ''), razao_social)
        ")->fetchAll(PDO::FETCH_ASSOC);

        $unidades = $db->query("
            SELECT id, empresa_id, nome
            FROM unidades
            ORDER BY nome
        ")->fetchAll(PDO::FETCH_ASSOC);

        $usuarios = $db->query("
            SELECT id, nome
            FROM usuarios
            WHERE ativo = 1 AND tipo IN ('ADMIN', 'TECNICO')
            ORDER BY nome
        ")->fetchAll(PDO::FETCH_ASSOC);

        $veiculos = $db->query("
            SELECT id, modelo, placa
            FROM veiculos
            ORDER BY modelo, placa
        ")->fetchAll(PDO::FETCH_ASSOC);

        return compact('empresas', 'unidades', 'usuarios', 'veiculos');
    }

    private function obterAgendamentoOuRedirecionar(int $id): array
    {
        $agendamento = $this->agendaModel->buscarPorId($id);
        if (!$agendamento) {
            ToastHelper::error('Agendamento não encontrado.');
            $this->redirecionar('/agenda');
        }

        return $agendamento;
    }

    private function exigirAgendaEditavel(array $agendamento): void
    {
        $status = strtoupper((string)($agendamento['status'] ?? ''));
        $visitaStatus = strtoupper((string)($agendamento['visita_status'] ?? ''));

        if (
            !in_array($status, self::STATUS_EDITAVEIS, true) ||
            in_array($visitaStatus, ['EM_ANDAMENTO', 'CHECKLIST_INICIADO', 'FINALIZADA', 'CANCELADA', 'EXCLUIDA'], true)
        ) {
            ToastHelper::warning(
                'Esse agendamento não pode ser alterado porque foi encerrado ou a visita técnica já foi iniciada.'
            );
            $this->redirecionar('/agenda');
        }
    }

    private function exigirPermissaoNoAgendamento(array $agendamento): void
    {
        if ($this->usuarioAdministrador()) {
            return;
        }

        if (
            $this->tipoUsuario() === 'TECNICO' &&
            (int)($agendamento['tecnico_id'] ?? 0) === $this->usuarioLogadoId()
        ) {
            return;
        }

        ToastHelper::error('Você não possui permissão para alterar esse agendamento.');
        $this->redirecionar('/agenda');
    }

    private function exigirPerfilEscrita(): void
    {
        if (in_array($this->tipoUsuario(), ['ADMIN', 'TECNICO'], true)) {
            return;
        }

        ToastHelper::error('Seu perfil possui acesso apenas para consulta da agenda.');
        $this->redirecionar('/agenda');
    }

    private function normalizarTecnicoResponsavel(?int $tecnicoId): int
    {
        if ($this->usuarioAdministrador()) {
            return (int)$tecnicoId;
        }

        return $this->usuarioLogadoId();
    }

    private function horarioFoiAlterado(array $atual, array $dados): bool
    {
        return (string)$atual['data_agendada'] !== (string)$dados['data_agendada']
            || substr((string)$atual['hora_inicio'], 0, 5) !== substr((string)$dados['hora_inicio'], 0, 5)
            || substr((string)$atual['hora_fim'], 0, 5) !== substr((string)$dados['hora_fim'], 0, 5);
    }

    private function mensagemConflito(array $conflito): string
    {
        $recursos = [];
        if (!empty($conflito['conflito_tecnico'])) {
            $recursos[] = 'o técnico';
        }
        if (!empty($conflito['conflito_veiculo'])) {
            $recursos[] = 'o veículo';
        }

        $recurso = $recursos !== [] ? implode(' e ', $recursos) : 'o recurso selecionado';
        return 'Conflito de agenda: ' . $recurso . ' já possui compromisso nesse intervalo.';
    }

    private function normalizarDataFiltro(string $data): string
    {
        $data = trim($data);
        return $data !== '' && $this->dataValida($data) ? $data : '';
    }

    private function validarId($id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            ToastHelper::error('Identificador do agendamento inválido.');
            $this->redirecionar('/agenda');
        }

        return (int)$id;
    }

    private function exigirPost(string $retorno): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            ToastHelper::error('Método de requisição não permitido.');
            $this->redirecionar($retorno);
        }
    }

    private function dataValida(string $data): bool
    {
        $objeto = DateTime::createFromFormat('Y-m-d', $data);
        return $objeto !== false && $objeto->format('Y-m-d') === $data;
    }

    private function horaValida(string $hora): bool
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $hora) === 1;
    }

    private function tipoUsuario(): string
    {
        $tipo = strtoupper(trim((string)($_SESSION['tipo'] ?? '')));
        return str_replace(['Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U'], $tipo);
    }

    private function usuarioAdministrador(): bool
    {
        return in_array($this->tipoUsuario(), ['ADMIN', 'ADMINISTRADOR'], true);
    }

    private function usuarioLogadoId(): int
    {
        return (int)$_SESSION['usuario_id'];
    }

    private function registrarErroInterno(Throwable $erro): void
    {
        error_log(sprintf(
            '[Agenda] %s em %s:%d',
            $erro->getMessage(),
            $erro->getFile(),
            $erro->getLine()
        ));
    }

    private function redirecionar(string $rota): never
    {
        header('Location: ' . BASE_URL . $rota);
        exit;
    }
}
