<?php

class ChecklistsController extends Controller
{
    private ChecklistVisita $checklistModel;
    private Funcionario $funcionarioModel;
    private GHE $gheModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->checklistModel = $this->model('ChecklistVisita');
        $this->funcionarioModel = $this->model('Funcionario');
        $this->gheModel = $this->model('GHE');
    }

    public function index(): void
    {
        $abasPermitidas = ['andamento', 'concluidos', 'cancelados', 'todos'];
        $abaAtual = strtolower(trim((string)($_GET['aba'] ?? 'andamento')));
        if (!in_array($abaAtual, $abasPermitidas, true)) {
            $abaAtual = 'andamento';
        }

        $prioridade = strtoupper(trim((string)($_GET['prioridade'] ?? '')));
        if (!in_array($prioridade, ['', 'PADRAO', 'URGENTE', 'CRITICA'], true)) {
            $prioridade = '';
        }

        $filtros = [
            'prioridade' => $prioridade,
            'data_inicio' => $this->dataFiltro($_GET['data_inicio'] ?? null),
            'data_fim' => $this->dataFiltro($_GET['data_fim'] ?? null),
        ];

        $usuarioId = $this->usuarioLogadoId();
        $tipoUsuario = $this->tipoUsuario();

        $this->view('checklists/index', [
            'checklists' => $this->checklistModel->listarTodos(
                $usuarioId,
                $tipoUsuario,
                $abaAtual,
                $filtros
            ),
            'indicadores' => $this->checklistModel->obterIndicadores(
                $usuarioId,
                $tipoUsuario
            ),
            'abaAtual' => $abaAtual,
            'filtros' => $filtros,
        ]);
    }

    public function iniciar($visitaId = null): never
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $_SESSION['erro'] = 'Método de requisição não permitido.';
            $this->redirecionar('/visitas');
        }

        $visitaId = $this->validarId($visitaId);

        try {
            $checklistId = $this->checklistModel->iniciarPorVisita(
                $visitaId,
                $this->usuarioLogadoId(),
                $this->usuarioAdministrador()
            );

            $_SESSION['sucesso'] = 'Check-list iniciado. A visita técnica está em andamento.';
            $this->redirecionar('/checklists/visualizar/' . $checklistId);
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível iniciar o check-list.';
            $this->redirecionar('/visitas');
        }
    }

    public function visualizar($checklistId = null): void
    {
        $checklistId = $this->validarId($checklistId);
        $checklist = $this->carregarChecklistAutorizado($checklistId);
        $estruturaPronta = $this->checklistModel->estruturaOperacionalDisponivel();

        $abasPermitidas = ['dados', 'hierarquia', 'funcionarios', 'ghe-riscos'];
        $abaSolicitada = trim((string)($_GET['aba'] ?? ''));
        $abaPadrao = $estruturaPronta
            ? (string)($checklist['ultima_aba'] ?? 'dados')
            : 'dados';
        $abaAtiva = in_array($abaSolicitada, $abasPermitidas, true)
            ? $abaSolicitada
            : (in_array($abaPadrao, $abasPermitidas, true) ? $abaPadrao : 'dados');

        $hierarquia = [];
        $setores = [];
        $cargos = [];
        $funcionarios = [];
        $ghes = [];
        $riscos = [];
        $progresso = [
            'hierarquias' => 0,
            'funcionarios' => 0,
            'ghes' => 0,
            'riscos' => 0,
            'percentual' => strtoupper((string)$checklist['status']) === 'CONCLUIDO' ? 100 : 10,
        ];
        $finalizacao = [
            'pode_finalizar' => false,
            'concluido' => strtoupper((string)$checklist['status']) === 'CONCLUIDO',
            'pendencias' => [],
        ];

        if ($estruturaPronta) {
            if (!in_array(strtoupper((string)$checklist['status']), ['CONCLUIDO', 'CANCELADO'], true)) {
                $this->checklistModel->marcarUltimaAba($checklistId, $abaAtiva);
            }
            $hierarquia = $this->checklistModel->listarHierarquiaContexto($checklist);
            $setores = $this->checklistModel->listarSetoresAtivos();
            $cargos = $this->checklistModel->listarCargosAtivos();
            $funcionarios = $this->funcionarioModel->listarPorContexto(
                (int)$checklist['empresa_id'],
                !empty($checklist['unidade_id']) ? (int)$checklist['unidade_id'] : null
            );
            $ghes = $this->gheModel->listarPorChecklist($checklistId);
            $riscos = $this->checklistModel->listarRiscosAtivos();
            $progresso = $this->checklistModel->calcularProgresso($checklistId, $checklist);
            $finalizacao = $this->checklistModel->obterSituacaoFinalizacao($checklistId, $checklist);
        } elseif ($abaAtiva !== 'dados') {
            $abaAtiva = 'dados';
        }

        $this->view('checklists/visualizar', [
            'checklist' => $checklist,
            'abaAtiva' => $abaAtiva,
            'estruturaPronta' => $estruturaPronta,
            'hierarquia' => $hierarquia,
            'setores' => $setores,
            'cargos' => $cargos,
            'funcionarios' => $funcionarios,
            'ghes' => $ghes,
            'riscos' => $riscos,
            'progresso' => $progresso,
            'finalizacao' => $finalizacao,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function salvarHierarquia($checklistId = null): never
    {
        $checklistId = $this->prepararPost($checklistId);
        $this->carregarChecklistAutorizado($checklistId);

        try {
            $this->garantirEstruturaPronta();
            $this->checklistModel->salvarLinhaHierarquia($checklistId, $_POST);
            $_SESSION['sucesso'] = 'Setor e cargo vinculados à hierarquia da visita.';
        } catch (Throwable $erro) {
            $this->tratarErroOperacional($erro, 'Não foi possível salvar a hierarquia.');
        }

        $this->redirecionarChecklist($checklistId, 'hierarquia');
    }

    public function salvarFuncionario($checklistId = null): never
    {
        $checklistId = $this->prepararPost($checklistId);
        $checklist = $this->carregarChecklistAutorizado($checklistId);

        try {
            $this->garantirEstruturaPronta();
            $nome = trim((string)($_POST['nome'] ?? ''));
            $hierarquiaId = (int)($_POST['hierarquia_id'] ?? 0);
            if ($nome === '' || $hierarquiaId <= 0) {
                throw new RuntimeException('Informe o nome e o cargo do funcionário.');
            }

            $this->funcionarioModel->salvar([
                'empresa_id' => (int)$checklist['empresa_id'],
                'unidade_id' => !empty($checklist['unidade_id']) ? (int)$checklist['unidade_id'] : null,
                'hierarquia_id' => $hierarquiaId,
                'codigo' => $_POST['codigo'] ?? null,
                'matricula' => $_POST['matricula'] ?? null,
                'nome' => $nome,
                'cpf' => $_POST['cpf'] ?? null,
                'data_admissao' => $_POST['data_admissao'] ?? null,
                'observacoes' => $_POST['observacoes'] ?? null,
            ]);
            $this->checklistModel->marcarUltimaAba($checklistId, 'funcionarios');
            $_SESSION['sucesso'] = 'Funcionário incluído na hierarquia da empresa.';
        } catch (Throwable $erro) {
            $this->tratarErroOperacional($erro, 'Não foi possível incluir o funcionário.');
        }

        $this->redirecionarChecklist($checklistId, 'funcionarios');
    }

    public function inativarFuncionario($checklistId = null, $funcionarioId = null): never
    {
        $checklistId = $this->prepararPost($checklistId);
        $funcionarioId = $this->validarId($funcionarioId);
        $checklist = $this->carregarChecklistAutorizado($checklistId);

        try {
            $this->garantirEstruturaPronta();
            $motivo = trim((string)($_POST['motivo'] ?? ''));
            if ($motivo === '') {
                throw new RuntimeException('Informe o motivo da inativação.');
            }

            $alterado = $this->funcionarioModel->inativar(
                $funcionarioId,
                (int)$checklist['empresa_id'],
                !empty($checklist['unidade_id']) ? (int)$checklist['unidade_id'] : null,
                $this->usuarioLogadoId(),
                $motivo,
                $_POST['data_desligamento'] ?? null
            );
            if (!$alterado) {
                throw new RuntimeException('Funcionário não encontrado na unidade desta visita.');
            }
            $_SESSION['sucesso'] = 'Funcionário inativado sem excluir o histórico.';
        } catch (Throwable $erro) {
            $this->tratarErroOperacional($erro, 'Não foi possível inativar o funcionário.');
        }

        $this->redirecionarChecklist($checklistId, 'funcionarios');
    }

    public function salvarGhe($checklistId = null): never
    {
        $checklistId = $this->prepararPost($checklistId);
        $checklist = $this->carregarChecklistAutorizado($checklistId);

        try {
            $this->garantirEstruturaPronta();
            $codigo = trim((string)($_POST['codigo'] ?? ''));
            $nome = trim((string)($_POST['nome'] ?? ''));
            if ($codigo === '' || $nome === '') {
                throw new RuntimeException('Informe o código e o nome do GHE.');
            }

            $this->gheModel->salvar([
                'checklist_id' => $checklistId,
                'empresa_id' => (int)$checklist['empresa_id'],
                'unidade_id' => !empty($checklist['unidade_id']) ? (int)$checklist['unidade_id'] : null,
                'codigo' => $codigo,
                'nome' => $nome,
                'descricao' => $_POST['descricao'] ?? null,
                'observacoes' => $_POST['observacoes'] ?? null,
                'criado_por' => $this->usuarioLogadoId(),
            ], $_POST['hierarquias'] ?? []);
            $this->checklistModel->marcarUltimaAba($checklistId, 'ghe-riscos');
            $_SESSION['sucesso'] = 'GHE criado e vinculado aos cargos selecionados.';
        } catch (Throwable $erro) {
            $this->tratarErroOperacional($erro, 'Não foi possível criar o GHE.');
        }

        $this->redirecionarChecklist($checklistId, 'ghe-riscos');
    }

    public function salvarRiscoGhe($checklistId = null, $gheId = null): never
    {
        $checklistId = $this->prepararPost($checklistId);
        $gheId = $this->validarId($gheId);
        $this->carregarChecklistAutorizado($checklistId);

        try {
            $this->garantirEstruturaPronta();
            if ((int)($_POST['risco_id'] ?? 0) <= 0) {
                throw new RuntimeException('Selecione um risco para o GHE.');
            }
            $this->gheModel->adicionarRisco($gheId, $checklistId, $_POST);
            $this->checklistModel->marcarUltimaAba($checklistId, 'ghe-riscos');
            $_SESSION['sucesso'] = 'Risco aplicado ao GHE.';
        } catch (Throwable $erro) {
            $this->tratarErroOperacional($erro, 'Não foi possível aplicar o risco ao GHE.');
        }

        $this->redirecionarChecklist($checklistId, 'ghe-riscos');
    }

    public function finalizar($checklistId = null): never
    {
        $checklistId = $this->prepararPost($checklistId);
        $this->carregarChecklistAutorizado($checklistId);

        try {
            $this->garantirEstruturaPronta();
            $this->checklistModel->finalizar(
                $checklistId,
                $this->usuarioLogadoId()
            );

            $_SESSION['sucesso'] = 'Check-list finalizado. A visita e a agenda foram marcadas como concluídas.';
        } catch (Throwable $erro) {
            $this->tratarErroOperacional($erro, 'Não foi possível finalizar o check-list.');
        }

        $this->redirecionarChecklist($checklistId, 'dados');
    }

    private function carregarChecklistAutorizado(int $checklistId): array
    {
        $checklist = $this->checklistModel->buscarDadosTela($checklistId);
        if (!$checklist) {
            $_SESSION['erro'] = 'Check-list não encontrado.';
            $this->redirecionar('/checklists');
        }

        if (!$this->checklistModel->usuarioPodeAcessar(
            $checklist,
            $this->usuarioLogadoId(),
            $this->tipoUsuario()
        )) {
            $_SESSION['erro'] = 'Você não possui permissão para acessar este check-list.';
            $this->redirecionar('/checklists');
        }

        return $checklist;
    }

    private function prepararPost(mixed $id): int
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $_SESSION['erro'] = 'Método de requisição não permitido.';
            $this->redirecionar('/checklists');
        }
        $this->validarCsrf();
        return $this->validarId($id);
    }

    private function garantirEstruturaPronta(): void
    {
        if (!$this->checklistModel->estruturaOperacionalDisponivel()) {
            throw new RuntimeException('Execute a migration da Etapa 9 antes de utilizar estas abas.');
        }
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_checklist'])) {
            $_SESSION['csrf_checklist'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_checklist'];
    }

    private function validarCsrf(): void
    {
        $recebido = (string)($_POST['_token'] ?? '');
        $esperado = (string)($_SESSION['csrf_checklist'] ?? '');
        if ($esperado === '' || !hash_equals($esperado, $recebido)) {
            $_SESSION['erro'] = 'A sessão do formulário expirou. Recarregue a página e tente novamente.';
            $this->redirecionar('/checklists');
        }
    }

    private function validarId($id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            $_SESSION['erro'] = 'Identificador inválido.';
            $this->redirecionar('/checklists');
        }
        return (int)$id;
    }

    private function dataFiltro(mixed $valor): string
    {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return '';
        }

        $data = DateTime::createFromFormat('Y-m-d', $valor);
        return $data && $data->format('Y-m-d') === $valor ? $valor : '';
    }

    private function usuarioLogadoId(): int
    {
        return (int)$_SESSION['usuario_id'];
    }

    private function tipoUsuario(): string
    {
        return strtoupper(trim((string)($_SESSION['tipo'] ?? '')));
    }

    private function usuarioAdministrador(): bool
    {
        return in_array($this->tipoUsuario(), ['ADMIN', 'ADMINISTRADOR'], true);
    }

    private function tratarErroOperacional(Throwable $erro, string $mensagemPadrao): void
    {
        $this->registrarErro($erro);
        $_SESSION['erro'] = $erro instanceof RuntimeException
            ? $erro->getMessage()
            : $mensagemPadrao;
    }

    private function registrarErro(Throwable $erro): void
    {
        error_log(sprintf(
            '[Checklist] %s em %s:%d',
            $erro->getMessage(),
            $erro->getFile(),
            $erro->getLine()
        ));
    }

    private function redirecionarChecklist(int $checklistId, string $aba): never
    {
        $this->redirecionar('/checklists/visualizar/' . $checklistId . '?aba=' . rawurlencode($aba));
    }

    private function redirecionar(string $rota): never
    {
        header('Location: ' . BASE_URL . $rota);
        exit;
    }
}
