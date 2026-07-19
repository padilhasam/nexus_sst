<?php

class Router
{
    private array $routes = [];

    public function get(string $uri, string $controller, string $method): void
    {
        $this->add('GET', $uri, $controller, $method);
    }

    public function post(string $uri, string $controller, string $method): void
    {
        $this->add('POST', $uri, $controller, $method);
    }

    public function any(string $uri, string $controller, string $method): void
    {
        $this->add('GET', $uri, $controller, $method);
        $this->add('POST', $uri, $controller, $method);
    }

    private function add(string $httpMethod, string $uri, string $controller, string $method): void
    {
        $uri = '/' . trim($uri, '/');

        if ($uri === '//') {
            $uri = '/';
        }

        $this->routes[$httpMethod][$uri] = [
            'controller' => $controller,
            'method' => $method
        ];
    }

    public function dispatch(): void
    {
        $httpMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $requestPath = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        $route = $this->normalizeRequestPath($requestPath);

        $routes = $this->routes[$httpMethod] ?? [];
        $match = $this->matchRoute($routes, $route);

        if (!$match) {
            http_response_code(404);
            echo "Rota {$route} não encontrada.";
            exit;
        }

        $controllerName = $match['controller'];
        $methodName = $match['method'];
        $params = $match['params'];

        $controllerFile = dirname(__DIR__) . "/app/controllers/{$controllerName}.php";

        if (!file_exists($controllerFile)) {
            http_response_code(404);
            die("Controller {$controllerName} não encontrado.");
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            http_response_code(500);
            die("Classe {$controllerName} não definida.");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            http_response_code(404);
            die("Método {$methodName} não encontrado em {$controllerName}.");
        }

        $controller->$methodName(...$params);
    }

    /**
     * Remove da URL o caminho público da aplicação antes de procurar a rota.
     *
     * Exemplo:
     * /orcamento/public/dashboard -> /dashboard
     *
     * O BASE_URL é usado primeiro porque alguns servidores/proxies informam
     * SCRIPT_NAME apenas como /index.php, mesmo quando a aplicação está em
     * uma subpasta.
     */
    private function normalizeRequestPath(string $requestPath): string
    {
        $route = '/' . ltrim($requestPath, '/');

        $basePaths = [];

        if (defined('BASE_URL')) {
            $configuredBasePath = parse_url((string) BASE_URL, PHP_URL_PATH);

            if (is_string($configuredBasePath) && $configuredBasePath !== '') {
                $basePaths[] = '/' . trim($configuredBasePath, '/');
            }
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptBasePath = rtrim(dirname($scriptName), '/');

        if ($scriptBasePath !== '' && $scriptBasePath !== '.' && $scriptBasePath !== '/') {
            $basePaths[] = '/' . trim($scriptBasePath, '/');
        }

        $basePaths = array_values(array_unique(array_filter($basePaths)));
        usort($basePaths, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($basePaths as $basePath) {
            if ($route === $basePath) {
                $route = '/';
                break;
            }

            if (str_starts_with($route, $basePath . '/')) {
                $route = substr($route, strlen($basePath));
                break;
            }
        }

        $route = '/' . trim($route, '/');

        return $route === '//' ? '/' : $route;
    }

    private function matchRoute(array $routes, string $route): ?array
    {
        if (isset($routes[$route])) {
            return [
                'controller' => $routes[$route]['controller'],
                'method' => $routes[$route]['method'],
                'params' => []
            ];
        }

        foreach ($routes as $pattern => $action) {
            $regex = preg_replace('#\{[a-zA-Z0-9_]+\}#', '([a-zA-Z0-9_-]+)', $pattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $route, $matches)) {
                return [
                    'controller' => $action['controller'],
                    'method' => $action['method'],
                    'params' => array_slice($matches, 1)
                ];
            }
        }

        return null;
    }
}
