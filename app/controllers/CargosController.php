<?php

class CargosController extends Controller
{
    private Cargo $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $this->model = $this->model('Cargo');
    }

    public function index(): void
    {
        $this->view('cargos/index', ['cargos' => $this->model->listarTudo()]);
    }

    public function criar(): void
    {
        $dadosAnteriores = $_SESSION['form_cargos'] ?? [];
        unset($_SESSION['form_cargos']);
        $this->view('cargos/criar', [
            'csrfToken' => $this->csrfToken(),
            'dadosAnteriores' => $dadosAnteriores,
        ]);
    }

    public function salvar(): never
    {
        $this->prepararPost();
        $dados = $this->dadosPost();
        try {
            $this->validarDuplicidade($dados);
            $this->model->salvar($dados);
            $_SESSION['sucesso'] = 'Cargo cadastrado com sucesso.';
            $this->redirecionar('/cargos');
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException ? $erro->getMessage() : 'Não foi possível cadastrar o cargo.';
            $_SESSION['form_cargos'] = $_POST;
            $this->redirecionar('/cargos/criar');
        }
    }

    public function editar($id = null): void
    {
        $id = $this->validarId($id);
        $registro = $this->model->buscarPorId($id);
        if (!$registro) {
            $_SESSION['erro'] = 'Cargo não encontrado.';
            $this->redirecionar('/cargos');
        }
        $this->view('cargos/editar', [
            'cargo' => $registro,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function atualizar($id = null): never
    {
        $this->prepararPost();
        $id = $this->validarId($id);
        $dados = $this->dadosPost();
        try {
            $this->validarDuplicidade($dados, $id);
            $this->model->atualizar($id, $dados);
            $_SESSION['sucesso'] = 'Cargo atualizado com sucesso.';
            $this->redirecionar('/cargos');
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = $erro instanceof RuntimeException ? $erro->getMessage() : 'Não foi possível atualizar o cargo.';
            $this->redirecionar('/cargos/editar/' . $id);
        }
    }

    public function excluir($id = null): never
    {
        $id = $this->validarId($id);
        try {
            $alterado = $this->model->desativar($id);
            $_SESSION[$alterado ? 'sucesso' : 'erro'] = $alterado
                ? 'Cargo desativado sem excluir as hierarquias existentes.'
                : 'O cargo já estava inativo ou não foi encontrado.';
        } catch (Throwable $erro) {
            $this->registrarErro($erro);
            $_SESSION['erro'] = 'Não foi possível desativar o cargo.';
        }
        $this->redirecionar('/cargos');
    }

    private function dadosPost(): array
    {
        return [
            'codigo' => strtoupper(trim((string)($_POST['codigo'] ?? ''))),
            'codigo_externo' => strtoupper(trim((string)($_POST['codigo_externo'] ?? ''))),
            'nome' => trim((string)($_POST['nome'] ?? '')),
            'cbo' => trim((string)($_POST['cbo'] ?? '')),
            
            'descricao' => trim((string)($_POST['descricao'] ?? '')),
            'ativo' => !empty($_POST['ativo']) ? 1 : 0,
        ];
    }

    private function validarDuplicidade(array $dados, int $ignorarId = 0): void
    {
        if ($dados['nome'] !== '') {
            $existente = $this->model->buscarPorNome($dados['nome']);
            if ($existente && (int)$existente['id'] !== $ignorarId) {
                throw new RuntimeException('Já existe outro cargo com este nome.');
            }
        }
        if ($dados['codigo'] !== '') {
            $existente = $this->model->buscarPorCodigo($dados['codigo']);
            if ($existente && (int)$existente['id'] !== $ignorarId) {
                throw new RuntimeException('Já existe outro cargo com este código.');
            }
        }
    }

    private function prepararPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $_SESSION['erro'] = 'Método de requisição não permitido.';
            $this->redirecionar('/cargos');
        }
        $recebido = (string)($_POST['_token'] ?? '');
        $esperado = (string)($_SESSION['csrf_cargos'] ?? '');
        if ($esperado === '' || !hash_equals($esperado, $recebido)) {
            $_SESSION['erro'] = 'A sessão do formulário expirou. Recarregue a página.';
            $this->redirecionar('/cargos');
        }
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf_cargos'])) {
            $_SESSION['csrf_cargos'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_cargos'];
    }

    private function validarId(mixed $id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            $_SESSION['erro'] = 'Identificador inválido.';
            $this->redirecionar('/cargos');
        }
        return (int)$id;
    }

    private function registrarErro(Throwable $erro): void
    {
        error_log('[CargosController] ' . $erro->getMessage());
    }

    private function redirecionar(string $rota): never
    {
        header('Location: ' . BASE_URL . $rota);
        exit;
    }
}
