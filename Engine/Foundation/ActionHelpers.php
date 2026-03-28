<?php

namespace Luxid\Foundation;

use Luxid\Http\Request;
use Luxid\Http\Response;
use Luxid\Http\Session;
use Luxid\Routing\Router;
use Rocket\Connection\Connection;

trait ActionHelpers
{
  /**
   * Get the Application instance
   */
  protected function app(): Application
  {
    return Application::$app;
  }

  /**
   * Get the Request instance
   */
  protected function request(): Request
  {
    return Application::$app->request;
  }

  /**
   * Get the Response instance
   */
  protected function response(): Response
  {
    return Application::$app->response;
  }

  /**
   * Get the Session instance
   */
  protected function session(): Session
  {
    return Application::$app->session;
  }

  /**
   * Get the Database connection
   * Returns Rocket\Connection\Connection instead of the old Database class
   */
  protected function db(): Connection
  {
    return Application::$app->db;
  }

  /**
   * Get the Router instance
   */
  protected function router(): Router
  {
    return Application::$app->router;
  }

  /**
   * Get the current authenticated user
   */
  protected function user(): ?\Luxid\Database\DbEntity
  {
    return Application::$app->user;
  }

  /**
   * Check if current user is guest
   */
  protected function isGuest(): bool
  {
    return Application::isGuest();
  }
}
