<?php

class AuthController extends Controller
{
    protected array $usuarioLogado = [];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->verificarAutenticacao();

        $this->usuarioLogado = [
            'id'    => $_SESSION['usuario_id'] ?? null,
            'nome'  => $_SESSION['nome'] ?? 'Usuário',
            'email' => $_SESSION['email'] ?? null,
            'tipo'  => $_SESSION['tipo'] ?? null,
        ];
    }

    protected function verificarAutenticacao(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    protected function usuarioId(): ?int
    {
        return $_SESSION['usuario_id'] ?? null;
    }

    protected function usuarioNome(): string
    {
        return $_SESSION['nome'] ?? 'Usuário';
    }

    protected function usuarioTipo(): ?string
    {
        return $_SESSION['tipo'] ?? null;
    }

    protected function redirecionar(string $rota): void
    {
        header('Location: ' . BASE_URL . $rota);
        exit;
    }
}