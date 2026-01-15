<?php
class Router {
    private $routes = [];
    
    public function add(string $method, string $path, callable $handler): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function dispatch(string $method, string $uri): void {
        // Usuń query string
        $uri = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{[a-zA-Z]+\}/', '([^/]+)', $route['path']);
            $pattern = "#^{$pattern}$#";
            
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }
        
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
    }
}
