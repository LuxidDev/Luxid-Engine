<?php

namespace engine\system;

use engine\system\exception\NotFoundException;

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

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            throw new NotFoundException();
        }

        if (is_string($callback)) {
            return Application::$app->screen->renderScreen($callback);
        }

        // to make the action an object, not an instance
        if (is_array($callback)) {
            /**
             * @var \engine\system\Action $action
             */
            $action = new $callback[0]();

            Application::$app->action = $action;
            $action->activity = $callback[1];
            $callback[0] = $action;

            foreach ($action->getMiddlewares() as $middlewre) {
                $middlewre->execute();
            }

        }

        // execute callback
        return call_user_func($callback, $this->request, $this->response);
    }
}

