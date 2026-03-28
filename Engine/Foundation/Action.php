<?php

namespace Luxid\Foundation;

use Luxid\Middleware\BaseMiddleware;

class Action
{
  use ActionHelpers;

  public string $frame = 'app';
  public string $activity = '';

  protected array $middlewares = [];

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
