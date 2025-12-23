<?php

namespace Luxid\Foundation;

use Luxid\Http\Request;
use Luxid\Http\Response;
use Luxid\Http\Session;
use Luxid\Database\Database;
use Luxid\Routing\Router;

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
     * Get the Database instance
     */
    protected function db(): Database
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

    /**
     * Send JSON response
     */
    protected function json($data, int $statusCode = 200): string
    {
        $this->response()->setStatusCode($statusCode);
        header('Content-Type: application/json');
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Send successful JSON response
     */
    protected function success($data = null, string $message = 'Success', int $statusCode = 200): string
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Send error JSON response
     */
    protected function error(string $message = 'Error', $errors = null, int $statusCode = 400): string
    {
        return $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
