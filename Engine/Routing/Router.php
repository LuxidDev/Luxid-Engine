<?php

namespace Luxid\Routing;

use Luxid\Exceptions\NotFoundException;
use Luxid\Http\Response;
use Luxid\Http\Request;
use Luxid\Foundation\Application;
use Luxid\Middleware\BaseMiddleware;

class Router
{
    public Request $request;
    public Response $response;
    protected array $routes = [
        /* 'get' => [
            '/' => callback,
            '/contact' => callback,
        ],
        'post' => [
            '/user' => callback,
        ], */
    ];

    /**
     * @var array Tracks the last registered route for middleware attachment
     */
    protected ?array $lastRoute = null;

    /**
     * @var array Middleware stack for route groups
     */
    protected array $middlewareStack = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        // Initialize all HTTP method arrays
        $this->routes = [
            'get' => [],
            'post' => [],
            'put' => [],
            'patch' => [],
            'delete' => [],
        ];
    }

    public function get($path, $callback)
    {
        $this->routes['get'][$path] = [
            'callback' => $callback,
            'middleware' => []
        ];

        // Track this as the last registered route
        $this->lastRoute = ['method' => 'get', 'path' => $path];

        return $this;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = [
            'callback' => $callback,
            'middleware' => []
        ];

        // Track this as the last registered route
        $this->lastRoute = ['method' => 'post', 'path' => $path];

        return $this;
    }

    public function put($path, $callback)
    {
        $this->routes['put'][$path] = [
            'callback' => $callback,
            'middleware' => []
        ];

        // Track this as the last registered route
        $this->lastRoute = ['method' => 'put', 'path' => $path];

        return $this;
    }

    public function patch($path, $callback)
    {
        $this->routes['patch'][$path] = [
            'callback' => $callback,
            'middleware' => []
        ];

        // Track this as the last registered route
        $this->lastRoute = ['method' => 'patch', 'path' => $path];

        return $this;
    }

    public function delete($path, $callback)
    {
        $this->routes['delete'][$path] = [
            'callback' => $callback,
            'middleware' => []
        ];

        // Track this as the last registered route
        $this->lastRoute = ['method' => 'delete', 'path' => $path];

        return $this;
    }

    /**
     * Add middleware to the last registered route
     *
     * @param BaseMiddleware $middleware The middleware to add
     * @return static
     */
    public function middleware(BaseMiddleware $middleware)
    {
        // Check if we have a last registered route
        if ($this->lastRoute !== null) {
            $method = $this->lastRoute['method'];
            $path = $this->lastRoute['path'];

            // Add middleware to the route
            if (isset($this->routes[$method][$path])) {
                $this->routes[$method][$path]['middleware'][] = $middleware;
            }

            // Reset for next route
            $this->lastRoute = null;
        }

        return $this;
    }

    /**
     * Register multiple routes with middleware
     *
     * @param array $middleware Array of middleware instances
     * @param callable $callback Function that defines routes
     */
    public function group(array $middleware, callable $callback)
    {
        // Push middleware to stack
        $this->middlewareStack = array_merge($this->middlewareStack, $middleware);

        // Execute route definitions
        call_user_func($callback, $this);

        // Pop middleware from stack
        $this->middlewareStack = [];
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->method();

        // check if route exists
        if (!isset($this->routes[$method][$path])) {
            throw new NotFoundException();
        }

        $route = $this->routes[$method][$path];
        $callback = $route['callback'];
        $middlewares = array_merge($this->middlewareStack, $route['middleware']);

        if (is_string($callback)) {
            return Application::$app->screen->renderScreen($callback);
        }

        // Convert callback to Action instance
        if (is_array($callback)) {
            /**
             * @var \Luxid\Foundation\Action $action $action
             */
            $action = new $callback[0]();

            Application::$app->action = $action;
            $action->activity = $callback[1];
            $callback[0] = $action;

            // Execute route middleware
            foreach ($middlewares as $middleware) {
                $middleware->execute();
            }

            // Execute action middleware
            foreach ($action->getMiddlewares() as $middlewre) {
                $middlewre->execute();
            }
        }

        // Extract route parameters
        $params = $this->extractRouteParams($path);

        // Execute callback with parameters
        if (!empty($params)) {
            return call_user_func_array($callback, array_merge(
                [$this->request, $this->response],
                $params
            ));
        }

        return call_user_func($callback, $this->request, $this->response);
    }

    /**
     * Extract parameters from route path
     * Example: /users/{id} -> extracts 'id' from URL
     */
    private function extractRouteParams(string $routePath): array
    {
        $actualPath = $this->request->getPath();
        $params = [];

        // Simple param extraction for now
        // TODO: would enhance this shi later for more complex routing
        $routeParts = explode('/', $routePath);
        $actualParts = explode('/', $actualPath);

        if (count($routeParts) !== count($actualParts)) {
            return $params;
        }

        foreach ($routeParts as $index => $routePart) {
            if (strpos($routePart, '{') === 0 && strpos($routePart, '}') === strlen($routePart) - 1) {
                $paramName = trim($routePart, '{}');
                $params[] = $actualParts[$index];
            }
        }

        return $params;
    }

    public function getRoutesForInspection(): array
    {
        $formattedRoutes = [];

        foreach ($this->routes as $method => $methodRoutes) {
            foreach ($methodRoutes as $path => $route) {
                $formattedRoutes[] = [
                    'method' => $method,
                    'path' => $path,
                    'callback' => $route['callback'],
                    'middleware' => $route['middleware'] ?? []
                ];
            }
        }

        return $formattedRoutes;
    }
}
