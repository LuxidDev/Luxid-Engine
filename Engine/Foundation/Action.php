<?php

namespace Luxid\Foundation;

use Luxid\Middleware\BaseMiddleware;
use Luxid\Routing\Routes;

class Action
{
  use ActionHelpers;

  public string $frame = 'app';
  public string $activity = '';

  protected array $middlewares = [];

  /**
   * Define routes for this action
   * Override this method in your action classes
   */
  public static function routes(): Routes
  {
    return Routes::new();
  }

  public function getMiddlewares(): array
  {
    return $this->middlewares;
  }

  public function setFrame($frame)
  {
    $this->frame = $frame;
  }

  public function nova($screen, $data = [])
  {
    return $this->app()->screen->renderScreen($screen, $data);
  }

  public function registerMiddleware(BaseMiddleware $middleware)
  {
    $this->middlewares[] = $middleware;
  }
}