<?php

namespace Luxid\Middleware;

use Luxid\Foundation\Application;
use Luxid\Exceptions\ForbiddenException;

class AuthMiddleware extends BaseMiddleware
{
    public array $publicActivities = [];

    public function __construct(array $publicActivities = [])
    {
        $this->publicActivities = $publicActivities;
    }

    public function execute()
    {
        // Skip if no action
        if (Application::$app->action === null) {
            return;
        }

        if (Application::isGuest()) {
            $currentActivity = Application::$app->action->activity;

            // Check if this activity is in the public list
            if (!in_array($currentActivity, $this->publicActivities)) {
                throw new ForbiddenException();
            }
        }
    }
}
