<?php

namespace engine\system\middlewares;

use engine\system\Application;
use engine\system\exception\ForbiddenException;

class AuthMiddleware extends BaseMiddleware
{
    public array $activity = [];

    public function __construct(array $activity= [])
    {
        $this->activity = $activity;
    }

    public function execute()
    {
        if (Application::isGuest()) {
            if (empty($this->activity) || in_array(Application::$app->action->activity, $this->activity)){
                throw new ForbiddenException();
            }
        }
    }
}
