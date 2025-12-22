<?php

namespace engine\system;

use engine\system\middlewares\BaseMiddleware;

class Action
{
    public string $frame = 'app';   // default to app (main frame)
    public string $activity = '';
    /**
     * @var \engine\system\middlewares\BaseMiddleware[]
     */
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
        return Application::$app->screen->renderScreen($screen, $data);
    }

    public function registerMiddleware(BaseMiddleware $middlware)
    {
        $this->middlewares[] = $middlware;
    }
}
