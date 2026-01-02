<?php
// Engine/helpers.php

use Luxid\Foundation\Application;
use Luxid\Routing\RouteBuilder;

if (!function_exists('route')) {
    /**
     * Global helper function to create a new fluent route
     */
    function route(string $name): RouteBuilder
    {
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
        \Luxid\Facades\Route::group($options, $callback);
    }
}
