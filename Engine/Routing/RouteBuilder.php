<?php

namespace Luxid\Routing;

use Luxid\Middleware\AuthMiddleware;
use Luxid\Middleware\BaseMiddleware;
use Luxid\Middleware\PublicMiddleware;

/**
 * RouteBuilder provides a fluent, action-first DSL for routing
 */
class RouteBuilder
{
    private string $name;
    private string $method;
    private string $path;
    private $callback;
    private Router $router;
    private array $middleware = [];
    private bool $securityConfigured = false;
    private bool $routeRegistered = false;
    private bool $inheritGroupSecurity = true;

    public function __construct(Router $router, string $name)
    {
        $this->router = $router;
        $this->name = $name;
    }

    /**
     * Define a GET route
     */
    public function get(string $path): self
    {
        $this->method = 'get';
        $this->path = $path;
        return $this;
    }

    /**
     * Define a POST route
     */
    public function post(string $path): self
    {
        $this->method = 'post';
        $this->path = $path;
        return $this;
    }

    /**
     * Define a PUT route
     */
    public function put(string $path): self
    {
        $this->method = 'put';
        $this->path = $path;
        return $this;
    }

    /**
     * Define a PATCH route
     */
    public function patch(string $path): self
    {
        $this->method = 'patch';
        $this->path = $path;
        return $this;
    }

    /**
     * Define a DELETE route
     */
    public function delete(string $path): self
    {
        $this->method = 'delete';
        $this->path = $path;
        return $this;
    }

    /**
     * Bind the route to an Action class
     */
    public function uses(string $actionClass, string $method = 'index'): self
    {
        $this->callback = [$actionClass, $method];
        return $this;
    }

    /**
     * Disable group security inheritance for this route
     */
    public function withoutInheritance(): self
    {
        $this->inheritGroupSecurity = false;
        return $this;
    }

    /**
     * Mark route as secure (requires authentication)
     */
    public function secure(array $publicActivities = []): self
    {
        return $this->auth($publicActivities);
    }

    /**
     * Mark route as requiring authentication
     */
    public function auth(array $publicActivities = []): self
    {
        $this->addAuthMiddleware($publicActivities);
        $this->securityConfigured = true;
        $this->registerRouteIfNeeded();
        return $this;
    }

    /**
     * Mark route as explicitly public
     *
     * @param array|bool $activities
     *   - true: use PublicMiddleware (completely public)
     *   - false|null: use default activity
     *   - array: specific activities that are public
     */
    public function open($activities = null): self
    {
        if ($activities === true) {
            // Use PublicMiddleware for completely public route
            $this->addPublicMiddleware();
            $this->securityConfigured = true;
        } elseif ($activities === false || $activities === null) {
            // Default: use current activity as public
            $activities = [$this->extractActivityFromCallback()];
            $this->addAuthMiddleware($activities);
            $this->securityConfigured = true;
        } elseif (is_array($activities)) {
            // Specific activities are public
            $this->addAuthMiddleware($activities);
            $this->securityConfigured = true;
        } else {
            throw new \InvalidArgumentException(
                'open() accepts true, false, null, or an array of activity names'
            );
        }

        $this->registerRouteIfNeeded();
        return $this;
    }

    /**
     * Mark route as completely public (no auth checks at all)
     * Alias for open(true)
     */
    public function public(): self
    {
        return $this->open(true);
    }

    /**
     * Add generic middleware to the route
     */
    public function with($middleware): self
    {
        if (is_string($middleware)) {
            if (!class_exists($middleware)) {
                throw new \InvalidArgumentException(
                    sprintf('Middleware class "%s" does not exist', $middleware)
                );
            }

            if (!is_subclass_of($middleware, BaseMiddleware::class)) {
                throw new \InvalidArgumentException(
                    sprintf('Middleware "%s" must extend BaseMiddleware', $middleware)
                );
            }

            $middleware = new $middleware();
        }

        if (!$middleware instanceof BaseMiddleware) {
            throw new \InvalidArgumentException(
                'Middleware must be an instance of BaseMiddleware or a class name string'
            );
        }

        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Register the route with the router
     */
    private function registerRouteIfNeeded(): void
    {
        if ($this->routeRegistered) {
            return;
        }

        // Validate route is complete before registering
        if (!isset($this->method) || !isset($this->path) || !isset($this->callback)) {
            throw new \RuntimeException(
                sprintf('Route "%s" definition incomplete. Must specify method, path, and uses()', $this->name)
            );
        }

        // Apply group security inheritance if enabled
        if (!$this->securityConfigured && $this->inheritGroupSecurity) {
            $groupInfo = $this->getCurrentGroupInfo();

            if ($groupInfo && $groupInfo['auth'] === true) {
                // Auto-apply auth from group
                $this->addAuthMiddleware([]);
                $this->securityConfigured = true;
            } elseif ($groupInfo && $groupInfo['open'] !== null) {
                // Auto-apply open from group
                $this->addAuthMiddleware($groupInfo['open']);
                $this->securityConfigured = true;
            }
        }

        // Enforce security configuration
        if (!$this->securityConfigured) {
            throw new \RuntimeException(
                sprintf(
                    'Route "%s" must explicitly declare security with secure() or open()',
                    $this->name
                )
            );
        }

        // Register the route with the router
        call_user_func([$this->router, $this->method], $this->path, $this->callback);

        // Attach route-specific middleware
        foreach ($this->middleware as $middleware) {
            $this->router->middleware($middleware);
        }

        $this->routeRegistered = true;
    }

    /**
     * Get current group information from router
     */
    private function getCurrentGroupInfo(): ?array
    {
        $groupStack = $this->router->getGroupStack();

        if (empty($groupStack)) {
            return null;
        }

        $currentGroup = end($groupStack);

        return [
            'auth' => $currentGroup['auth'] ?? false,
            'open' => $currentGroup['open'] ?? null
        ];
    }

    /**
     * Extract activity name from callback
     */
    private function extractActivityFromCallback(): string
    {
        if (is_array($this->callback) && isset($this->callback[1])) {
            return $this->callback[1];
        }

        return 'index';
    }

    /**
     * Add AuthMiddleware with appropriate configuration
     */
    private function addAuthMiddleware(array $publicActivities = []): void
    {
        $this->middleware[] = new AuthMiddleware($publicActivities);
    }

    /**
     * Add PublicMiddleware for truly public routes
     */
    private function addPublicMiddleware(): void
    {
        $this->middleware[] = new PublicMiddleware();
    }

    /**
     * Get the route name
     */
    public function getName(): string
    {
        return $this->name;
    }
}
