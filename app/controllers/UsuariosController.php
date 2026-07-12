<?php

class UsuariosController extends Controller
{
    private $usuarioModel;

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

        $this->usuarioModel = $this->model('Usuario');
    }

    /**
     * LISTAGEM
     */
    public function index()
    {
        $userName = $this->usuarioModel->listarTodos();

        $dados = [
            'titulo'   => 'Usuários',
            'usuarios' => $userName,
            'css'      => 'usuarios.css'
        ];

        $this->view('usuarios/index', $dados);
    }

    /**
     * FORMULÁRIO DE CRIAÇÃO
     */
    public function criar()
    {
        $dados = [
            'titulo' => 'Novo Usuário',
            'css'    => 'usuarios.css'
        ];

        $this->view('usuarios/criar', $dados);
    }

    /**
     * SALVAR NOVO USUÁRIO
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }

        $nome     = trim($_POST['nome'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $senha    = trim($_POST['senha'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $tipo     = trim($_POST['tipo'] ?? 'TECNICO');
        $ativo    = (int) ($_POST['ativo'] ?? 0);

        // Validação
        if (empty($nome) || empty($email) || empty($senha)) {

            $_SESSION['erro'] = 'Preencha todos os campos obrigatórios.';

            header('Location: ' . BASE_URL . '/usuarios/criar');
            exit;
        }

        // Verifica e-mail existente
        if ($this->usuarioModel->buscarPorEmail($email)) {

            $_SESSION['erro'] = 'Já existe um usuário com este e-mail.';

            header('Location: ' . BASE_URL . '/usuarios/criar');
            exit;
        }

        $dados = [
            'nome'      => $nome,
            'email'     => $email,
            'senha'     => $senha,
            'telefone'  => $telefone,
            'tipo'      => $tipo,
            'ativo'     => $ativo
        ];

        $salvou = $this->usuarioModel->cadastrar($dados);

        if ($salvou) {

            $_SESSION['sucesso'] = 'Usuário cadastrado com sucesso.';

        } else {

            $_SESSION['erro'] = 'Erro ao cadastrar usuário.';
        }

        header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }

    /**
     * FORMULÁRIO DE EDIÇÃO
     */
    public function editar($id)
    {
        $usuario = $this->usuarioModel->buscarPorId((int)$id);

        if (!$usuario) {

            $_SESSION['erro'] = 'Usuário não encontrado.';

            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }

        $dados = [
            'titulo' => 'Editar Usuário',
            'usuario' => $usuario,
            'css' => 'usuarios.css'
        ];

        $this->view('usuarios/editar', $dados);
    }

    /**
     * ATUALIZAR USUÁRIO
     */
    public function atualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }

        $usuarioAtual = $this->usuarioModel->buscarPorId((int)$id);

        if (!$usuarioAtual) {

            $_SESSION['erro'] = 'Usuário não encontrado.';

            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }

        $nome     = trim($_POST['nome'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $tipo     = trim($_POST['tipo'] ?? 'TECNICO');
        $ativo    = (int) ($_POST['ativo'] ?? 0);
        $senha    = trim($_POST['senha'] ?? '');

        // Verifica se email pertence a outro usuário
        $usuarioEmail = $this->usuarioModel->buscarPorEmail($email);

        if ($usuarioEmail && $usuarioEmail['id'] != $id) {

            $_SESSION['erro'] = 'Este e-mail já está sendo utilizado.';

            header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
            exit;
        }

        $dados = [
            'nome'      => $nome,
            'email'     => $email,
            'telefone'  => $telefone,
            'tipo'      => $tipo,
            'ativo'     => $ativo
        ];

        // Atualiza senha apenas se preenchida
        if (!empty($senha)) {
            $dados['senha'] = $senha;
        }

        $atualizou = $this->usuarioModel->atualizar((int)$id, $dados);

        if ($atualizou) {

            $_SESSION['sucesso'] = 'Usuário atualizado com sucesso.';

        } else {

            $_SESSION['erro'] = 'Erro ao atualizar usuário.';
        }

        header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }

    /**
     * EXCLUIR USUÁRIO
     */
    public function excluir($id)
    {
        $usuario = $this->usuarioModel->buscarPorId((int)$id);

        if (!$usuario) {

            $_SESSION['erro'] = 'Usuário não encontrado.';

            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }

        // Impede excluir o próprio usuário logado
        if ($usuario['id'] == $_SESSION['usuario_id']) {

            $_SESSION['erro'] = 'Você não pode excluir seu próprio usuário.';

            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }

        $excluiu = $this->usuarioModel->excluir((int)$id);

        if ($excluiu) {

            $_SESSION['sucesso'] = 'Usuário excluído com sucesso.';

        } else {

            $_SESSION['erro'] = 'Erro ao excluir usuário.';
        }

        header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }
}