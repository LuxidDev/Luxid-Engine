<?php

use Luxid\Foundation\Application;
use Luxid\Routing\RouteBuilder;
use Luxid\Nodes\Route;

if (!function_exists('route')) {
    /**
     * Global helper function to create a new fluent route
     */
    function route(string $name): RouteBuilder
    {
        // Check if Application::$app is initialized
        if (!isset(Application::$app) || Application::$app === null) {
            throw new \RuntimeException(
                'Application not initialized. Make sure to create an Application instance before defining routes.'
            );
        }

        $router = Application::$app->router;
        return new RouteBuilder($router, $name);
    }
}

if (!function_exists('route_group')) {
    /**
     * Global helper function for route grouping
     * Alias for Route::group()
     */
    function route_group(array $options, callable $callback): void
    {
        Route::group($options, $callback);
    }
}

if (!function_exists('get')) {
    function get(string $handler): \Luxid\Routing\RouteMethod
    {
        return new \Luxid\Routing\RouteMethod('get', $handler);
    }
}

if (!function_exists('post')) {
    function post(string $handler): \Luxid\Routing\RouteMethod
    {
        return new \Luxid\Routing\RouteMethod('post', $handler);
    }
}

if (!function_exists('put')) {
    function put(string $handler): \Luxid\Routing\RouteMethod
    {
        return new \Luxid\Routing\RouteMethod('put', $handler);
    }
}

if (!function_exists('patch')) {
    function patch(string $handler): \Luxid\Routing\RouteMethod
    {
        return new \Luxid\Routing\RouteMethod('patch', $handler);
    }
}

if (!function_exists('delete')) {
    function delete(string $handler): \Luxid\Routing\RouteMethod
    {
        return new \Luxid\Routing\RouteMethod('delete', $handler);
    }
}