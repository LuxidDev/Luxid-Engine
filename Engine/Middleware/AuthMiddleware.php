<?php

namespace Luxid\Middleware;

use Luxid\Foundation\Application;
use Luxid\Exceptions\ForbiddenException;
use Luxid\Contracts\Auth\AuthManager;

class AuthMiddleware extends BaseMiddleware
{
  protected ?AuthManager $auth = null;  // Make nullable
  public array $publicActivities = [];

  /**
   * Constructor with backward compatibility
   * 
   * @param AuthManager|array|null $auth AuthManager instance or array of public activities (legacy)
   * @param array $publicActivities Public activities (when first param is AuthManager)
   */
  public function __construct($auth = null, array $publicActivities = [])
  {
    if ($auth instanceof AuthManager) {
      $this->auth = $auth;
      $this->publicActivities = $publicActivities;
    } elseif (is_array($auth)) {
      // Legacy mode: first param was public activities
      $this->auth = null;
      $this->publicActivities = $auth;
    } else {
      $this->auth = null;
      $this->publicActivities = [];
    }
  }

  public function execute()
  {
    // If we have an auth manager, use it
    if ($this->auth && $this->auth->check()) {
      return;
    }

    // Legacy check: If no auth manager, fall back to old behavior
    if (!$this->auth && Application::$app && Application::$app->user) {
      return;
    }

    // Skip if no action is set (can happen with closure routes)
    if (Application::$app->action === null) {
      return;
    }

    $currentActivity = Application::$app->action->activity ?? '';

    // Check if this activity is in the public list
    if (!in_array($currentActivity, $this->publicActivities)) {
      throw new ForbiddenException();
    }
  }
}
