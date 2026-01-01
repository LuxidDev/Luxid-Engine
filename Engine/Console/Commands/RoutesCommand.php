<?php

namespace Luxid\Console\Commands;

use Luxid\Console\Command;

class RoutesCommand extends Command
{
    protected string $description = 'List all registered routes';

    public function handle(array $argv): int
    {
        $this->parseArguments($argv);

        $this->line("🛣️  Registered Routes");
        $this->line(str_repeat("─", 80));

        $routes = $this->loadRoutes();

        if (empty($routes)) {
            $this->warning("No routes found");
            $this->line("Create routes in: \033[1;34m" . $this->getRoutesPath() . "/api.php\033[0m");
            return 0;
        }

        $tableRows = [];
        foreach ($routes as $route) {
            $tableRows[] = [
                $route['method'],
                $route['path'],
                $route['handler'],
                $route['middleware']
            ];
        }

        $this->table(['Method', 'Path', 'Handler', 'Middleware'], $tableRows);

        $this->line("");
        $this->info("Total: " . count($routes) . " route(s)");

        return 0;
    }

    private function loadRoutes(): array
    {
        $routes = [];
        $routesFile = $this->getRoutesPath() . '/api.php';

        if (!file_exists($routesFile)) {
            return $routes;
        }

        $content = file_get_contents($routesFile);

        // Clean up content for easier parsing
        $content = str_replace(["\r", "\n", "\t"], ' ', $content);
        $content = preg_replace('/\\s+/', ' ', $content);

        // Split by $router-> to find route definitions
        $parts = explode('$router->', $content);

        foreach ($parts as $part) {
            // Look for route definitions
            if (preg_match('/^(get|post|put|patch|delete)\(\\s*[\'"]([^\'"]+)[\'"]\\s*,\\s*([^,)]+)/', $part, $match)) {
                // Check if the full route line has ->middleware(
                $routeLine = '$router->' . substr($part, 0, 100); // Check first 100 chars
                $hasMiddleware = strpos($routeLine, '->middleware(') !== false;

                $routes[] = [
                    'method' => strtoupper($match[1]),
                    'path' => $match[2],
                    'handler' => trim($match[3]),
                    'middleware' => $hasMiddleware ? 'Yes' : 'No'
                ];
            }
        }

        return $routes;
    }
}
