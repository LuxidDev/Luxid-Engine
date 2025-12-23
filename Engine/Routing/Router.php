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
     * @var array Middleware stack for route groups
     */
    protected array $middlewareStack = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        // Initialie all HTTP method arrays
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

        return $this;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = [
            'callback' => $callback,
            'middleware' => []
        ];

        return $this;
    }

    public function put($path, $callback)
    {
        $this->routes['put'][$path] = [
            'callback' => $callback,
            'middleware' => []
        ];
        return $this;
    }

    public function patch($path, $callback)
    {
        $this->routes['patch'][$path] = [
            'callback' => $callback,
            'middleware' => []
        ];
        return $this;
    }

    public function delete($path, $callback)
    {
        $this->routes['delete'][$path] = [
            'callback' => $callback,
            'middleware' => []
        ];
        return $this;
    }

    /**
     * Add middleware to the last registered route
     * @return static
     */
    public function middleware(BaseMiddleware $middleware)
    {
        $method = $this->request->method();
        $path = $this->request->getPath();

        // Find the last registered route for this path and method
        if (isset($this->routes[$method][$path])) {
            $this->routes[$method][$path]['middleware'][] = $middleware;
        }

        return $this;
    }

    /**
     * Register multiple routes with middleware
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

        // check if route exisits
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

        // Execture callback with parameters
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

        // sigh .. simple param extraction for now
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
}

