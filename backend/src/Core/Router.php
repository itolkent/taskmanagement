<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, string $handler): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        if (ob_get_length()) {
            ob_clean();
        }

        $path = parse_url($uri, PHP_URL_PATH);

        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        $path = str_replace('/index.php', '', $path);

        header('Content-Type: application/json');

        foreach ($this->routes as $route) {

            if (strtoupper($method) !== strtoupper($route['method'])) {
                continue;
            }

            $pattern = "@^" . preg_replace('@\{([\w]+)\}@', '(?P<$1>[^/]+)', $route['pattern']) . "$@";

            if (preg_match($pattern, $path, $matches)) {

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$class, $action] = explode('@', $route['handler']);

                $controller = new $class();

                $result = call_user_func([$controller, $action], $params);

                echo json_encode($result);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
}