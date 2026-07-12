<?php

class LoginController extends Controller
{
    private $loginModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->loginModel = $this->model('Login');
    }

    public function index()
    {
        if (!empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        $this->view('auth/login');
    }

    public function autenticar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        if (empty($email) || empty($senha)) {
            $this->view('auth/login', [
                'erro' => 'Preencha todos os campos.'
            ]);
            return;
        }

        $usuario = $this->loginModel->autenticar($email, $senha);

        if (!$usuario) {
            $this->view('auth/login', [
                'erro' => 'E-mail ou senha inválidos.'
            ]);
            return;
        }

        if ((int)($usuario['ativo'] ?? 0) !== 1) {
            $this->view('auth/login', [
                'erro' => 'Usuário bloqueado ou inativo.'
            ]);
            return;
        }

        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nome']       = $usuario['nome'];
        $_SESSION['email']      = $usuario['email'];
        $_SESSION['tipo']       = $usuario['tipo'] ?? null;

        $this->loginModel->registrarUltimoAcesso($usuario['id']);

        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }

    public function logout()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}