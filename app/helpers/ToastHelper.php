<?php

class ToastHelper
{
    private const TIPOS_VALIDOS = [
        'success',
        'danger',
        'warning',
        'info',
    ];

    public static function success(
        string $mensagem,
        int $tempo = 4000
    ): void {
        self::adicionar('success', $mensagem, $tempo);
    }

    public static function error(
        string $mensagem,
        int $tempo = 7000
    ): void {
        self::adicionar('danger', $mensagem, $tempo);
    }

    public static function warning(
        string $mensagem,
        int $tempo = 6000
    ): void {
        self::adicionar('warning', $mensagem, $tempo);
    }

    public static function info(
        string $mensagem,
        int $tempo = 5000
    ): void {
        self::adicionar('info', $mensagem, $tempo);
    }

    private static function adicionar(
        string $tipo,
        string $mensagem,
        int $tempo
    ): void {
        self::iniciarSessao();

        if (!in_array($tipo, self::TIPOS_VALIDOS, true)) {
            $tipo = 'info';
        }

        $mensagem = trim($mensagem);

        if ($mensagem === '') {
            return;
        }

        if ($tempo < 1000) {
            $tempo = 1000;
        }

        $_SESSION['toast'] = [
            'tipo' => $tipo,
            'mensagem' => $mensagem,
            'tempo' => $tempo,
        ];
    }

    public static function render(): void
    {
        self::iniciarSessao();

        if (empty($_SESSION['toast'])) {
            return;
        }

        $toast = $_SESSION['toast'];

        unset($_SESSION['toast']);

        $mensagem = json_encode(
            (string)($toast['mensagem'] ?? ''),
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        );

        $tipo = json_encode(
            (string)($toast['tipo'] ?? 'info'),
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

        $tempo = max(
            1000,
            (int)($toast['tempo'] ?? 5000)
        );
        ?>

        <script>
        if (typeof window.showToast === 'function') {
            window.showToast(
                <?= $mensagem ?>,
                <?= $tipo ?>,
                <?= $tempo ?>
            );
        } else {
            console.error(
                'A função global showToast() não foi carregada.'
            );
        }
        </script>

        <?php
    }

    private static function iniciarSessao(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}