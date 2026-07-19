<?php

class FuncionariosController extends Controller
{
    private Funcionario $funcionarioModel;
    private Empresa $empresaModel;
    private Unidade $unidadeModel;
    private Hierarquia $hierarquiaModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->funcionarioModel = $this->model('Funcionario');
        $this->empresaModel = $this->model('Empresa');
        $this->unidadeModel = $this->model('Unidade');
        $this->hierarquiaModel = $this->model('Hierarquia');
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

        $this->view('funcionarios/index', [
            'funcionarios' => $this->funcionarioModel->listar($filtros),
            'indicadores' => $this->funcionarioModel->obterIndicadores(),
            'empresas' => $this->empresaModel->listarAtivas(),
            'unidades' => $this->unidadeModel->listarAtivas(),
            'filtros' => $filtros,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function criar(): void
    {
        $this->view('funcionarios/criar', $this->dadosFormulario());
    }

    public function salvar(): never
    {
        $this->prepararPost();

        try {
            $this->funcionarioModel->salvar($_POST);
            $_SESSION['sucesso'] = 'Funcionário cadastrado com sucesso.';
            $this->redirecionar('/funcionarios');
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível cadastrar o funcionário.';
            $_SESSION['form_funcionario'] = $_POST;
            $this->redirecionar('/funcionarios/criar');
        }
    }

    public function editar($id = null): void
    {
        $id = $this->validarId($id);
        $funcionario = $this->funcionarioModel->buscarPorId($id);

        if (!$funcionario) {
            $_SESSION['erro'] = 'Funcionário não encontrado.';
            $this->redirecionar('/funcionarios');
        }

        $this->view('funcionarios/editar', array_merge(
            $this->dadosFormulario(),
            ['funcionario' => $funcionario]
        ));
    }

    public function atualizar($id = null): never
    {
        $this->prepararPost();
        $id = $this->validarId($id);

        try {
            $this->funcionarioModel->atualizar($id, $_POST);
            $_SESSION['sucesso'] = 'Funcionário atualizado com sucesso.';
            $this->redirecionar('/funcionarios');
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível atualizar o funcionário.';
            $this->redirecionar('/funcionarios/editar/' . $id);
        }
    }

    public function inativar($id = null): never
    {
        $this->prepararPost();
        $id = $this->validarId($id);
        $funcionario = $this->funcionarioModel->buscarPorId($id);

        if (!$funcionario) {
            $_SESSION['erro'] = 'Funcionário não encontrado.';
            $this->redirecionar('/funcionarios');
        }

        try {
            $alterado = $this->funcionarioModel->inativar(
                $id,
                (int)$funcionario['empresa_id'],
                !empty($funcionario['unidade_id']) ? (int)$funcionario['unidade_id'] : null,
                (int)$_SESSION['usuario_id'],
                trim((string)($_POST['motivo'] ?? '')),
                $_POST['data_desligamento'] ?? null
            );

            $_SESSION[$alterado ? 'sucesso' : 'erro'] = $alterado
                ? 'Funcionário inativado sem excluir o histórico.'
                : 'O funcionário já estava inativo ou não pôde ser alterado.';
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException
                ? $erro->getMessage()
                : 'Não foi possível inativar o funcionário.';
        }

        $this->redirecionar('/funcionarios');
    }

    public function reativar($id = null): never
    {
        $this->prepararPost();
        $id = $this->validarId($id);

        try {
            $alterado = $this->funcionarioModel->reativar($id);
            $_SESSION[$alterado ? 'sucesso' : 'erro'] = $alterado
                ? 'Funcionário reativado com sucesso.'
                : 'O funcionário já estava ativo ou não foi encontrado.';
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = 'Não foi possível reativar o funcionário.';
        }

        $this->redirecionar('/funcionarios');
    }

    public function excluir($id = null): never
    {
        $this->inativar($id);
    }

    private function dadosFormulario(): array
    {
        $dadosAnteriores = $_SESSION['form_funcionario'] ?? [];
        unset($_SESSION['form_funcionario']);

        return [
            'empresas' => $this->empresaModel->listarAtivas(),
            'unidades' => $this->unidadeModel->listarAtivas(),
            'hierarquias' => $this->hierarquiaModel->listarTudo(),
            'dadosAnteriores' => $dadosAnteriores,
            'csrfToken' => $this->csrfToken(),
        ];
    }

    private function prepararPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $_SESSION['erro'] = 'Método de requisição não permitido.';
            $this->redirecionar('/funcionarios');
        }

        $recebido = (string)($_POST['_token'] ?? '');
        $esperado = (string)($_SESSION['csrf_funcionarios'] ?? '');

        if ($esperado === '' || !hash_equals($esperado, $recebido)) {
            $_SESSION['erro'] = 'A sessão do formulário expirou. Recarregue a página e tente novamente.';
            $this->redirecionar('/funcionarios');
        }
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_funcionarios'])) {
            $_SESSION['csrf_funcionarios'] = bin2hex(random_bytes(32));
        }

        return (string)$_SESSION['csrf_funcionarios'];
    }

    private function validarId(mixed $id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            $_SESSION['erro'] = 'Identificador inválido.';
            $this->redirecionar('/funcionarios');
        }

        return (int)$id;
    }

    private function registrarErro(Throwable $erro): void
    {
        error_log(sprintf(
            '[Funcionarios] %s em %s:%d',
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