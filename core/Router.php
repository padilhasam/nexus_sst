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
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $route = $requestPath;

        if ($basePath !== '/' && str_starts_with($route, $basePath)) {
            $route = substr($route, strlen($basePath));
        }

        $route = '/' . trim($route, '/');

        if ($route === '//') {
            $route = '/';
        }

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