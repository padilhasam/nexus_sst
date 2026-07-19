<?php

class GHEController extends Controller
{
    private GHE $gheModel;
    private Empresa $empresaModel;
    private Unidade $unidadeModel;
    private Risco $riscoModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->gheModel = $this->model('GHE');
        $this->empresaModel = $this->model('Empresa');
        $this->unidadeModel = $this->model('Unidade');
        $this->riscoModel = $this->model('Risco');
    }

    public function index(): void
    {
        $status = strtoupper(trim((string)($_GET['status'] ?? '')));
        if (!in_array($status, ['', 'ATIVO', 'INATIVO'], true)) {
            $status = '';
        }

        $filtros = [
            'busca' => trim((string)($_GET['busca'] ?? '')),
            'empresa_id' => max(0, (int)($_GET['empresa_id'] ?? 0)),
            'unidade_id' => max(0, (int)($_GET['unidade_id'] ?? 0)),
            'status' => $status,
        ];

        $this->view('ghe/index', [
            'ghes' => $this->gheModel->listar(
                $filtros,
                $this->usuarioId(),
                $this->tipoUsuario()
            ),
            'indicadores' => $this->gheModel->obterIndicadores(
                $this->usuarioId(),
                $this->tipoUsuario()
            ),
            'empresas' => $this->empresaModel->listarAtivas(),
            'unidades' => $this->unidadeModel->listarAtivas(),
            'filtros' => $filtros,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function criar(): void
    {
        $checklistId = max(0, (int)($_GET['checklist_id'] ?? 0));
        $checklists = $this->gheModel->listarChecklistsDisponiveis(
            $this->usuarioId(),
            $this->tipoUsuario()
        );

        $checklist = null;
        $hierarquias = [];

        if ($checklistId > 0) {
            $checklist = $this->gheModel->buscarContextoChecklist(
                $checklistId,
                $this->usuarioId(),
                $this->tipoUsuario()
            );

            if (!$checklist || !$this->gheModel->checklistEditavel((string)$checklist['status'])) {
                $_SESSION['erro'] = 'O check-list selecionado não está disponível para criação de GHE.';
                $this->redirecionar('/ghe/criar');
            }

            $hierarquias = $this->gheModel->listarHierarquiasPorChecklist($checklistId);
        }

        $dadosAnteriores = $_SESSION['form_ghe'] ?? [];
        unset($_SESSION['form_ghe']);

        $this->view('ghe/criar', [
            'checklists' => $checklists,
            'checklist' => $checklist,
            'hierarquias' => $hierarquias,
            'dadosAnteriores' => $dadosAnteriores,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function salvar(): never
    {
        $this->prepararPost();
        $checklistId = max(0, (int)($_POST['checklist_id'] ?? 0));

        try {
            if ($checklistId <= 0) {
                throw new RuntimeException('Selecione o check-list de origem do GHE.');
            }

            $contexto = $this->gheModel->buscarContextoChecklist(
                $checklistId,
                $this->usuarioId(),
                $this->tipoUsuario()
            );

            if (!$contexto) {
                throw new RuntimeException('Check-list não encontrado ou sem permissão de acesso.');
            }

            if (!$this->gheModel->checklistEditavel((string)$contexto['status'])) {
                throw new RuntimeException('Não é possível criar GHE em um check-list concluído ou cancelado.');
            }

            $this->validarDadosBasicos($_POST);

            $gheId = $this->gheModel->salvar([
                'checklist_id' => $checklistId,
                'empresa_id' => (int)$contexto['empresa_id'],
                'unidade_id' => !empty($contexto['unidade_id']) ? (int)$contexto['unidade_id'] : null,
                'codigo' => $_POST['codigo'] ?? '',
                'nome' => $_POST['nome'] ?? '',
                'descricao' => $_POST['descricao'] ?? null,
                'observacoes' => $_POST['observacoes'] ?? null,
                'criado_por' => $this->usuarioId(),
            ], $_POST['hierarquias'] ?? []);

            $_SESSION['sucesso'] = 'GHE criado e vinculado aos cargos selecionados.';
            $this->redirecionar('/ghe/visualizar/' . $gheId);
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível cadastrar o GHE.';
            $_SESSION['form_ghe'] = $_POST;
            $destino = '/ghe/criar' . ($checklistId > 0 ? '?checklist_id=' . $checklistId : '');
            $this->redirecionar($destino);
        }
    }

    public function visualizar($id = null): void
    {
        $id = $this->validarId($id);
        $ghe = $this->carregarAutorizado($id);

        $this->view('ghe/visualizar', [
            'ghe' => $ghe,
            'riscosDisponiveis' => $this->riscoModel->listarTodos(),
            'editavel' => (int)$ghe['ativo'] === 1
                && $this->gheModel->checklistEditavel((string)$ghe['checklist_status']),
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function editar($id = null): void
    {
        $id = $this->validarId($id);
        $ghe = $this->carregarAutorizado($id);

        if ((int)$ghe['ativo'] !== 1) {
            $_SESSION['erro'] = 'Reative o GHE antes de editá-lo.';
            $this->redirecionar('/ghe/visualizar/' . $id);
        }

        if (!$this->gheModel->checklistEditavel((string)$ghe['checklist_status'])) {
            $_SESSION['erro'] = 'GHEs de check-lists concluídos ou cancelados são somente leitura.';
            $this->redirecionar('/ghe/visualizar/' . $id);
        }

        $this->view('ghe/editar', [
            'ghe' => $ghe,
            'hierarquias' => $this->gheModel->listarHierarquiasPorChecklist((int)$ghe['checklist_id']),
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function atualizar($id = null): never
    {
        $this->prepararPost();
        $id = $this->validarId($id);

        try {
            $ghe = $this->carregarAutorizado($id);
            if ((int)$ghe['ativo'] !== 1) {
                throw new RuntimeException('Reative o GHE antes de editá-lo.');
            }
            if (!$this->gheModel->checklistEditavel((string)$ghe['checklist_status'])) {
                throw new RuntimeException('GHEs de check-lists concluídos ou cancelados são somente leitura.');
            }

            $this->validarDadosBasicos($_POST);
            $this->gheModel->atualizar($id, $_POST, $_POST['hierarquias'] ?? []);

            $_SESSION['sucesso'] = 'GHE atualizado com sucesso.';
            $this->redirecionar('/ghe/visualizar/' . $id);
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível atualizar o GHE.';
            $this->redirecionar('/ghe/editar/' . $id);
        }
    }

    public function inativar($id = null): never
    {
        $this->prepararPost();
        $id = $this->validarId($id);

        try {
            $ghe = $this->carregarAutorizado($id);
            if (!$this->gheModel->checklistEditavel((string)$ghe['checklist_status'])) {
                throw new RuntimeException('Não é possível inativar um GHE de check-list concluído ou cancelado.');
            }

            $alterado = $this->gheModel->inativar($id);
            $_SESSION[$alterado ? 'sucesso' : 'erro'] = $alterado
                ? 'GHE inativado sem excluir o histórico.'
                : 'O GHE já estava inativo ou não pôde ser alterado.';
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível inativar o GHE.';
        }

        $this->redirecionar('/ghe');
    }

    public function reativar($id = null): never
    {
        $this->prepararPost();
        $id = $this->validarId($id);

        try {
            $ghe = $this->carregarAutorizado($id);
            if (!$this->gheModel->checklistEditavel((string)$ghe['checklist_status'])) {
                throw new RuntimeException('Não é possível reativar um GHE de check-list concluído ou cancelado.');
            }

            $alterado = $this->gheModel->reativar($id);
            $_SESSION[$alterado ? 'sucesso' : 'erro'] = $alterado
                ? 'GHE reativado com sucesso.'
                : 'O GHE já estava ativo ou não foi encontrado.';
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível reativar o GHE.';
        }

        $this->redirecionar('/ghe');
    }

    public function salvarRisco($id = null): never
    {
        $this->prepararPost();
        $id = $this->validarId($id);

        try {
            $ghe = $this->carregarAutorizado($id);
            if ((int)$ghe['ativo'] !== 1) {
                throw new RuntimeException('Reative o GHE antes de adicionar riscos.');
            }
            if (!$this->gheModel->checklistEditavel((string)$ghe['checklist_status'])) {
                throw new RuntimeException('Não é possível alterar riscos de um check-list concluído ou cancelado.');
            }
            if ((int)($_POST['risco_id'] ?? 0) <= 0) {
                throw new RuntimeException('Selecione um risco para aplicar ao GHE.');
            }

            $this->gheModel->adicionarRisco($id, (int)$ghe['checklist_id'], $_POST);
            $_SESSION['sucesso'] = 'Risco aplicado ao GHE.';
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível aplicar o risco ao GHE.';
        }

        $this->redirecionar('/ghe/visualizar/' . $id);
    }

    public function removerRisco($id = null, $riscoId = null): never
    {
        $this->prepararPost();
        $id = $this->validarId($id);
        $riscoId = $this->validarId($riscoId);

        try {
            $ghe = $this->carregarAutorizado($id);
            if (!$this->gheModel->checklistEditavel((string)$ghe['checklist_status'])) {
                throw new RuntimeException('Não é possível alterar riscos de um check-list concluído ou cancelado.');
            }

            $removido = $this->gheModel->removerRisco($id, $riscoId);
            $_SESSION[$removido ? 'sucesso' : 'erro'] = $removido
                ? 'Risco removido do GHE.'
                : 'Risco aplicado não encontrado.';
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível remover o risco do GHE.';
        }

        $this->redirecionar('/ghe/visualizar/' . $id);
    }

    private function carregarAutorizado(int $id): array
    {
        $ghe = $this->gheModel->buscarPorId($id, $this->usuarioId(), $this->tipoUsuario());

        if (!$ghe) {
            $_SESSION['erro'] = 'GHE não encontrado ou sem permissão de acesso.';
            $this->redirecionar('/ghe');
        }

        return $ghe;
    }

    private function validarDadosBasicos(array $dados): void
    {
        if (trim((string)($dados['codigo'] ?? '')) === '') {
            throw new RuntimeException('Informe o código do GHE.');
        }
        if (trim((string)($dados['nome'] ?? '')) === '') {
            throw new RuntimeException('Informe o nome do GHE.');
        }
    }

    private function prepararPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $_SESSION['erro'] = 'Método de requisição não permitido.';
            $this->redirecionar('/ghe');
        }

        $recebido = (string)($_POST['_token'] ?? '');
        $esperado = (string)($_SESSION['csrf_ghe'] ?? '');

        if ($esperado === '' || !hash_equals($esperado, $recebido)) {
            $_SESSION['erro'] = 'A sessão do formulário expirou. Recarregue a página e tente novamente.';
            $this->redirecionar('/ghe');
        }
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_ghe'])) {
            $_SESSION['csrf_ghe'] = bin2hex(random_bytes(32));
        }

        return (string)$_SESSION['csrf_ghe'];
    }

    private function validarId(mixed $id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            $_SESSION['erro'] = 'Identificador inválido.';
            $this->redirecionar('/ghe');
        }

        return (int)$id;
    }

    private function usuarioId(): int
    {
        return (int)$_SESSION['usuario_id'];
    }

    private function tipoUsuario(): string
    {
        return strtoupper(trim((string)($_SESSION['tipo'] ?? '')));
    }

    private function registrarErro(Throwable $erro): void
    {
        error_log(sprintf(
            '[GHE] %s em %s:%d',
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