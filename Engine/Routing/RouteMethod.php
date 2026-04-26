<?php

namespace Luxid\Routing;

class RouteMethod
{
    private string $method;
    private string $handler;

    public function __construct(string $method, string $handler)
    {
        $this->method = $method;
        $this->handler = $handler;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHandler(): string
    {
        return $this->handler;
    }
}