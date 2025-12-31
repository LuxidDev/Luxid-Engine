<?php

namespace Luxid\Foundation;

use Luxid\Routing\Router;
use Luxid\Http\Response;
use Luxid\Http\Request;
use Luxid\Http\SessionInterface;
use Luxid\Database\Database;
use Luxid\Database\DbEntity;

class Application
{
    public static string $ROOT_DIR;
    public string $frame = 'app';

    public string $userClass;
    public Router $router;
    public Request $request;
    public Response $response;
    public SessionInterface $session;
    public static Application $app;
    public ?Action $action = null;
    public Database $db;
    public ?DbEntity $user;
    public Screen $screen;

    public function __construct($rootPath, array $config)
    {
        $this->userClass = $config['userClass'];

        self::$ROOT_DIR = $rootPath;
        self::$app = $this;

        $this->request = new Request();
        $this->response = new Response();

        // Only create Session if not in CLI mode
        if (php_sapi_name() !== 'cli') {
            $this->session = new \Luxid\Http\Session();
        } else {
            // Create a null session for CLI
            $this->session = new \Luxid\Http\NullSession();
        }

        $this->router = new Router($this->request, $this->response);
        $this->screen = new Screen();
        $this->db = new Database($config['db']);
        $this->user = null;

        // Only check session for user if session exists and is started
        if ($this->session->isStarted()) {
            $primaryValue = $this->session->get('user');
            if ($primaryValue !== null) {
                $primaryKey = $this->userClass::primaryKey();
                $this->user = $this->userClass::findOne([$primaryKey => $primaryValue]) ?? null;
            }
        }
    }

    public static function isGuest()
    {
        return !self::$app->user;
    }

    public function run()
    {
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode());

            // Check if this is an API request
            $path = $this->request->getPath();
            $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            $isApiRequest = strpos($path, '/api/') === 0 ||
                        strpos($acceptHeader, 'application/json') !== false ||
                        strpos($contentType, 'application/json') !== false;

            if ($isApiRequest) {
                // Return JSON error for API requests
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
            } else {
                // Return HTML error for web requests
                echo $this->screen->renderScreen('_error', [
                    'exception' => $e
                ]);
            }
        }
    }

    // getter | setter ==================================
    public function getAction()
    {
        return $this->action;
    }
    public function setAction(Action $action)
    {
        $this->action = $action;
    }
    // =============================================

    public function login(DbEntity $user)
    {
        $this->user = $user;
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};

        $this->session->set('user', $primaryValue);

        return true;
    }

    public function logout()
    {
        $this->user = null;
        $this->session->remove('user');
    }
}
