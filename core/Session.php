<?php

class Session
{
    /**
     * Inicia a sessão
     */
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Armazena um valor
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Obtém um valor
     */
    public static function get(string $key, $default = null)
    {
        self::start();

        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verifica se existe
     */
    public static function has(string $key): bool
    {
        self::start();

        return isset($_SESSION[$key]);
    }

    /**
     * Remove um item
     */
    public static function remove(string $key): void
    {
        self::start();

        unset($_SESSION[$key]);
    }

    /**
     * Limpa toda a sessão
     */
    public static function clear(): void
    {
        self::start();

        $_SESSION = [];
    }

    /**
     * Regenera o ID
     */
    public static function regenerate(): void
    {
        self::start();

        session_regenerate_id(true);
    }

    /**
     * Destroi a sessão
     */
    public static function destroy(): void
    {
        self::start();

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
    }

    /**
     * Login efetuado?
     */
    public static function isLogged(): bool
    {
        return self::has('usuario_id');
    }

    /**
     * Retorna o usuário logado
     */
    public static function user(): array
    {
        return [
            'id'    => self::get('usuario_id'),
            'nome'  => self::get('nome'),
            'email' => self::get('email'),
            'tipo'  => self::get('tipo')
        ];
    }

    /**
     * Flash Message
     */
    public static function flash(string $key, $value = null)
    {
        self::start();

        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return;
        }

        if (!isset($_SESSION['_flash'][$key])) {
            return null;
        }

        $msg = $_SESSION['_flash'][$key];

        unset($_SESSION['_flash'][$key]);

        return $msg;
    }
}