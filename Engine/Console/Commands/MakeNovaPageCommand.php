<?php

namespace Luxid\Console\Commands;

use Luxid\Console\Command;

class MakeNovaPageCommand extends Command
{
  protected string $description = 'Create a new Nova page';

  public function handle(array $argv): int
  {
    $this->parseArguments($argv);

    if (empty($this->args)) {
      $this->error("Page name is required");
      $this->line("Usage: php juice make:nova:page <name>");
      $this->line("");
      $this->line("Examples:");
      $this->line("  php juice make:nova:page Dashboard");
      $this->line("  php juice make:nova:page Admin/Dashboard");
      return 1;
    }

    $pageName = $this->args[0];
    $this->createPage($pageName);

    return 0;
  }

  private function createPage(string $pageName): void
  {
    $this->line("📄 Creating Nova page...");

    // Handle nested paths (Admin/Dashboard)
    if (strpos($pageName, '/') !== false) {
      $parts = explode('/', $pageName);
      $className = array_pop($parts);
      $path = implode('/', $parts);
      $pagePath = $path . '/' . $className;
      $fullPath = 'pages/' . $path;
    } else {
      $className = $pageName;
      $pagePath = $className;
      $fullPath = 'pages';
    }

    $directory = $this->getNovaPath() . '/' . $fullPath;
    $filePath = $directory . '/' . $className . '.nova.php';

    // Generate page content
    $content = $this->generatePageContent($className, $pagePath);

    if ($this->createFile($filePath, $content)) {
      $relativePath = str_replace($this->getProjectRoot() . '/', '', $filePath);
      $this->success("Nova page created successfully!");
      $this->line("📁 Location: \033[1;34m{$relativePath}\033[0m");

      $this->line("");
      $this->line("\033[1;33m💡 Usage example:\033[0m");
      $this->line("In your Action:");
      $this->line("  return Nova::render('{$pagePath}', [");
      $this->line("      'title' => '{$className}',");
      $this->line("  ]);");
      $this->line("");
      $this->line("Or add to routes/web.php:");
      $this->line("  route('{$className}')");
      $this->line("      ->get('/{$this->getRoutePath($pagePath)}')");
      $this->line("      ->uses(function() { return Nova::render('{$pagePath}'); })");
      $this->line("      ->open();");
    } else {
      $this->error("Failed to create Nova page");
    }
  }

  private function generatePageContent(string $className, string $pagePath): string
  {
    $pageId = 'pages/' . $pagePath;
    $pageTitle = str_replace('/', ' ', $pagePath);
    $pageTitle = ucwords($pageTitle);

    return <<<PHP
<?php
// {$pagePath}.nova.php

component('{$pageId}', function(\$c) {
    \$c->state(function() {
        return [
            'title' => '{$pageTitle}',
            'content' => 'Welcome to the {$pageTitle} page!',
            'showMessage' => true
        ];
    });
    
    \$c->actions([
        'toggleMessage' => function(&\$state) {
            \$state['showMessage'] = !\$state['showMessage'];
        }
    ]);
    
    \$c->view(function(\$state) {
        ?>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-4xl font-bold mb-6">@echo(\$state->title)</h1>
            
            @if(\$state->showMessage)
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6">
                    <p>@echo(\$state->content)</p>
                </div>
            @endif
            
            <button @click="toggleMessage" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                @if(\$state->showMessage) Hide @else Show @endif Message
            </button>
            
            <div class="mt-8 p-4 bg-gray-100 rounded">
                <p class="text-sm text-gray-600">Edit this page at nova/pages/{$pagePath}.nova.php</p>
            </div>
        </div>
        <?php
    });
});
PHP;
  }

  private function getRoutePath(string $pagePath): string
  {
    // Convert "Admin/Dashboard" to "admin/dashboard"
    return strtolower(str_replace('/', '/', $pagePath));
  }

  protected function getNovaPath(): string
  {
    return $this->getProjectRoot() . '/nova';
  }
}
