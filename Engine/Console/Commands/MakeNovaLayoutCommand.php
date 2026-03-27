<?php

namespace Luxid\Console\Commands;

use Luxid\Console\Command;

class MakeNovaLayoutCommand extends Command
{
  protected string $description = 'Create a new Nova layout';

  public function handle(array $argv): int
  {
    $this->parseArguments($argv);

    if (empty($this->args)) {
      $this->error("Layout name is required");
      $this->line("Usage: php juice make:nova:layout <name>");
      $this->line("");
      $this->line("Examples:");
      $this->line("  php juice make:nova:layout AdminLayout");
      $this->line("  php juice make:nova:layout Auth/AuthLayout");
      return 1;
    }

    $layoutName = $this->args[0];
    $this->createLayout($layoutName);

    return 0;
  }

  private function createLayout(string $layoutName): void
  {
    $this->line("🏗️ Creating Nova layout...");

    // Handle nested paths (Auth/AuthLayout)
    if (strpos($layoutName, '/') !== false) {
      $parts = explode('/', $layoutName);
      $className = array_pop($parts);
      $path = implode('/', $parts);
      $layoutPath = $path . '/' . $className;
      $fullPath = 'layouts/' . $path;
    } else {
      $className = $layoutName;
      $layoutPath = $className;
      $fullPath = 'layouts';
    }

    $directory = $this->getNovaPath() . '/' . $fullPath;
    $filePath = $directory . '/' . $className . '.nova.php';

    // Generate layout content
    $content = $this->generateLayoutContent($className, $layoutPath);

    if ($this->createFile($filePath, $content)) {
      $relativePath = str_replace($this->getProjectRoot() . '/', '', $filePath);
      $this->success("Nova layout created successfully!");
      $this->line("📁 Location: \033[1;34m{$relativePath}\033[0m");

      $this->line("");
      $this->line("\033[1;33m💡 Usage example:\033[0m");
      $this->line("To use this layout in your Action:");
      $this->line("  return Nova::render('Welcome', [], 'pages', '{$layoutPath}');");
      $this->line("");
      $this->line("Or set as default in nova.json:");
      $this->line("  \"default_layout\": \"{$layoutPath}\"");
    } else {
      $this->error("Failed to create Nova layout");
    }
  }

  private function generateLayoutContent(string $className, string $layoutPath): string
  {
    $layoutId = 'layouts/' . $layoutPath;
    $layoutTitle = str_replace('/', ' ', $layoutPath);
    $layoutTitle = ucwords($layoutTitle);

    return <<<PHP
<?php
// {$layoutPath}.nova.php

component('{$layoutId}', function(\$c) {
    \$c->state(function() {
        return [
            'title' => '{$layoutTitle}',
            'description' => '{$layoutTitle} layout'
        ];
    });
    
    \$c->view(function(\$state) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>@echo(\$state->title)</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <style>
                * { font-family: 'Inter', sans-serif; }
            </style>
        </head>
        <body class="bg-gray-50">
            <nav class="bg-white shadow-sm">
                <div class="container mx-auto px-4 py-3">
                    <div class="flex justify-between items-center">
                        <h1 class="text-xl font-semibold text-gray-800">@echo(\$state->title)</h1>
                        <div class="space-x-4">
                            <a href="/" class="text-gray-600 hover:text-gray-900">Home</a>
                            <a href="/dashboard" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        </div>
                    </div>
                </div>
            </nav>
            
            <main class="container mx-auto px-4 py-8">
                @slot('content')
                    <div class="text-center py-12">
                        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Welcome to your new layout</h2>
                        <p class="text-gray-500">Edit this layout at nova/layouts/{$layoutPath}.nova.php</p>
                        <p class="text-gray-500 mt-2">The content from your page will appear here</p>
                    </div>
                @endslot
            </main>
            
            <footer class="bg-white border-t mt-12">
                <div class="container mx-auto px-4 py-6 text-center text-gray-500 text-sm">
                    Luxid Framework &copy; 2026
                </div>
            </footer>
            
            <script src="/nova.js"></script>
        </body>
        </html>
        <?php
    });
});
PHP;
  }

  protected function getNovaPath(): string
  {
    return $this->getProjectRoot() . '/nova';
  }
}
