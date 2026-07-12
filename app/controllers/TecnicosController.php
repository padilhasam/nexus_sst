<?php

class TecnicosController extends Controller
{
    private $tecnicoModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica login
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->tecnicoModel = $this->model('Tecnico');
    }

    /**
     * LISTAGEM
     */
    public function index()
    {
        $dados = [
            'tecnicos' => $this->tecnicoModel->listarTodos(),
            'css' => 'tecnicos.css'
        ];

        $this->view('tecnicos/index', $dados);
    }

    /**
     * FORM CRIAÇÃO
     */
    public function criar()
    {
        $dados = [
            'css' => 'tecnicos.css'
        ];

        $this->view('tecnicos/criar', $dados);
    }

    /**
     * SALVAR
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/tecnicos');
            exit;
        }

        $dados = [
            'nome' => trim($_POST['nome'] ?? ''),
            'registro_profissional' => trim($_POST['registro_profissional'] ?? null),
            'conselho' => trim($_POST['conselho'] ?? null),
            'uf' => strtoupper(trim($_POST['uf'] ?? null)),
            'cpf' => trim($_POST['cpf'] ?? null),
            'telefone' => trim($_POST['telefone'] ?? null),
            'email' => trim($_POST['email'] ?? null),
            'assinatura' => $_POST['assinatura'] ?? null,
            'ativo' => isset($_POST['ativo']) ? (int)$_POST['ativo'] : 1
        ];

        // validações básicas
        if (empty($dados['nome'])) {
            $_SESSION['erro'] = 'Nome é obrigatório.';
            header('Location: ' . BASE_URL . '/tecnicos/criar');
            exit;
        }

        if (!empty($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['erro'] = 'E-mail inválido.';
            header('Location: ' . BASE_URL . '/tecnicos/criar');
            exit;
        }

        if (!empty($dados['cpf']) && strlen($dados['cpf']) < 11) {
            $_SESSION['erro'] = 'CPF inválido.';
            header('Location: ' . BASE_URL . '/tecnicos/criar');
            exit;
        }

        $ok = $this->tecnicoModel->cadastrar($dados);

        if ($ok) {
            $_SESSION['sucesso'] = 'Técnico cadastrado com sucesso.';
        } else {
            $_SESSION['erro'] = 'Erro ao cadastrar técnico.';
        }

        header('Location: ' . BASE_URL . '/tecnicos');
        exit;
    }

    /**
     * EDITAR
     */
    public function editar($id)
    {
        $tecnico = $this->tecnicoModel->buscarPorId((int)$id);

        if (!$tecnico) {
            $_SESSION['erro'] = 'Técnico não encontrado.';
            header('Location: ' . BASE_URL . '/tecnicos');
            exit;
        }

        $dados = [
            'tecnico' => $tecnico,
            'css' => 'tecnicos.css'
        ];

        $this->view('tecnicos/editar', $dados);
    }

    /**
     * ATUALIZAR
     */
    public function atualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/tecnicos');
            exit;
        }

        $dados = [
            'nome' => trim($_POST['nome'] ?? ''),
            'registro_profissional' => trim($_POST['registro_profissional'] ?? null),
            'conselho' => trim($_POST['conselho'] ?? null),
            'uf' => strtoupper(trim($_POST['uf'] ?? null)),
            'cpf' => trim($_POST['cpf'] ?? null),
            'telefone' => trim($_POST['telefone'] ?? null),
            'email' => trim($_POST['email'] ?? null),
            'assinatura' => $_POST['assinatura'] ?? null,
            'ativo' => isset($_POST['ativo']) ? (int)$_POST['ativo'] : 1
        ];

        if (empty($dados['nome'])) {
            $_SESSION['erro'] = 'Nome é obrigatório.';
            header('Location: ' . BASE_URL . '/tecnicos/editar/' . $id);
            exit;
        }

        $ok = $this->tecnicoModel->atualizar((int)$id, $dados);

        if ($ok) {
            $_SESSION['sucesso'] = 'Técnico atualizado com sucesso.';
        } else {
            $_SESSION['erro'] = 'Erro ao atualizar técnico.';
        }

        header('Location: ' . BASE_URL . '/tecnicos');
        exit;
    }

    /**
     * EXCLUIR
     */
    public function excluir($id)
    {
        $tecnico = $this->tecnicoModel->buscarPorId((int)$id);

        if (!$tecnico) {
            $_SESSION['erro'] = 'Técnico não encontrado.';
            header('Location: ' . BASE_URL . '/tecnicos');
            exit;
        }

        $ok = $this->tecnicoModel->excluir((int)$id);

        if ($ok) {
            $_SESSION['sucesso'] = 'Técnico excluído com sucesso.';
        } else {
            $_SESSION['erro'] = 'Erro ao excluir técnico.';
        }

        header('Location: ' . BASE_URL . '/tecnicos');
        exit;
    }
}