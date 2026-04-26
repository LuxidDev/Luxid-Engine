<?php

namespace Luxid\Console\Commands;

use Luxid\Console\Command;

class MakeActionCommand extends Command
{
    protected string $description = 'Create a new Action class';

    public function handle(array $argv): int
    {
        $this->parseArguments($argv);

        if (empty($this->args)) {
            $this->error('Please provide an action name');
            $this->line('Usage: php juice make:action <ActionName>');
            return 1;
        }

        $actionName = $this->args[0];
        $actionPath = $this->getAppPath() . '/Actions/' . $actionName . '.php';
        $actionNamespace = $this->getNamespaceFromPath($actionPath);
        $actionClass = basename($actionName, '.php');

        // Determine if this is an API or Web action based on command or naming
        $isApi = str_contains($actionName, 'Api') || $actionName === 'ApiAction' || $this->options['api'] ?? false;

        // Create the action file
        $content = $this->generateActionContent($actionNamespace, $actionClass, $isApi);

        if ($this->createFile($actionPath, $content)) {
            $this->info("Action created: app/Actions/{$actionName}.php");

            // Automatically register in the appropriate routes file
            $this->registerInRoutes($actionClass, $isApi);

            return 0;
        }

        return 1;
    }

    private function generateActionContent(string $namespace, string $className, bool $isApi): string
    {
        $methodTemplate = $isApi ?
            'return Response::success([\'message\' => \'' . strtolower($className) . ' executed successfully\']);' :
            'return Nova::render(\'Welcome\', [\'title\' => \'' . $className . '\']);';

        $useStatements = $isApi ?
            "use Luxid\Nodes\Response;\nuse App\Entities\User;" :
            "use Luxid\Nodes\Nova;";

        $routesMethod = $this->generateRoutesMethod($className, $isApi);

        return <<<PHP
<?php

namespace {$namespace};

use Luxid\Foundation\Action;
{$useStatements}

class {$className} extends LuxidAction
{
    {$routesMethod}

    public function index()
    {
        {$methodTemplate}
    }
}

PHP;
    }

    private function generateRoutesMethod(string $className, bool $isApi): string
    {
        $methodName = strtolower($className);
        $path = $isApi ? "/api/{$methodName}" : "/{$methodName}";

        if ($isApi) {
            return <<<'PHP'
    public static function routes(): \Luxid\Routing\Routes
    {
        return \Luxid\Routing\Routes::new()
            ->prefix('api')
            ->add('/RESOURCE_NAME_HERE', get('index'))
            ->add('/RESOURCE_NAME_HERE/{id}', get('show'))
            ->add('/RESOURCE_NAME_HERE', post('store'))
            ->add('/RESOURCE_NAME_HERE/{id}', put('update'))
            ->add('/RESOURCE_NAME_HERE/{id}', delete('destroy'));
    }
PHP;
        }

        return <<<PHP
    public static function routes(): \Luxid\Routing\Routes
    {
        return \Luxid\Routing\Routes::new()
            ->add('/{$methodName}', get('index'));
    }
PHP;
    }

    private function registerInRoutes(string $className, bool $isApi): void
    {
        $routesFile = $isApi ?
            $this->getProjectRoot() . '/routes/api.php' :
            $this->getProjectRoot() . '/routes/web.php';

        $registrationLine = "{$className}::routes()->register();";

        if (!file_exists($routesFile)) {
            // Create routes file if it doesn't exist
            $content = $isApi ?
                "<?php\n\n// API Routes\n\nuse App\\Actions\\{$className};\n\n{$registrationLine}\n" :
                "<?php\n\n// Web Routes\n\nuse App\\Actions\\{$className};\n\n{$registrationLine}\n";

            file_put_contents($routesFile, $content);
            $this->info("Created routes file and registered {$className}");
            return;
        }

        // Read existing routes file
        $content = file_get_contents($routesFile);

        // Check if already registered
        if (strpos($content, $registrationLine) !== false) {
            $this->warning("{$className} already registered in routes file");
            return;
        }

        // Check if the use statement exists
        $useStatement = "use App\\Actions\\{$className};";

        if (strpos($content, $useStatement) === false) {
            // Add use statement after opening PHP tag
            $content = preg_replace(
                '/^<\?php/',
                "<?php\n\n{$useStatement}",
                $content,
                1
            );
        }

        // Add registration line before the last line or at the end
        if (strpos($content, "// Auto-registered actions") !== false) {
            // There's a section for auto-registered actions
            $content = preg_replace(
                '/\/\/ Auto-registered actions/',
                "// Auto-registered actions\n{$registrationLine}",
                $content
            );
        } else {
            // Add at the end of the file
            $content = rtrim($content) . "\n\n{$registrationLine}\n";
        }

        file_put_contents($routesFile, $content);
        $this->info("Registered {$className} in " . ($isApi ? 'routes/api.php' : 'routes/web.php'));
    }

    private function getNamespaceFromPath(string $path): string
    {
        $relativePath = str_replace($this->getAppPath(), '', $path);
        $relativePath = dirname($relativePath);
        $relativePath = str_replace('/', '\\', $relativePath);
        $relativePath = trim($relativePath, '\\');

        return $relativePath ? 'App\\Actions\\' . $relativePath : 'App\\Actions';
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}