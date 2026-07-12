<?php

require_once dirname(__DIR__) . '/config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

spl_autoload_register(function ($class) {
    $paths = [
        dirname(__DIR__) . '/core/',
        dirname(__DIR__) . '/app/controllers/',
        dirname(__DIR__) . '/app/models/',
        dirname(__DIR__) . '/app/services/',
        dirname(__DIR__) . '/app/helpers/',
        dirname(__DIR__) . '/app/traits/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$router = new Router();

require_once dirname(__DIR__) . '/routes/web.php';

$router->dispatch();