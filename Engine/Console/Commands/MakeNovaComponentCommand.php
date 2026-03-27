<?php

namespace Luxid\Console\Commands;

use Luxid\Console\Command;

class MakeNovaComponentCommand extends Command
{
  protected string $description = 'Create a new Nova component';

  public function handle(array $argv): int
  {
    $this->parseArguments($argv);

    if (empty($this->args)) {
      $this->error("Component name is required");
      $this->line("Usage: php juice make:nova:component <name>");
      $this->line("");
      $this->line("Examples:");
      $this->line("  php juice make:nova:component Button");
      $this->line("  php juice make:nova:component Form/Input");
      return 1;
    }

    $componentName = $this->args[0];
    $this->createComponent($componentName);

    return 0;
  }

  private function createComponent(string $componentName): void
  {
    $this->line("🎨 Creating Nova component...");

    // Handle nested paths (Form/Input)
    if (strpos($componentName, '/') !== false) {
      $parts = explode('/', $componentName);
      $className = array_pop($parts);
      $path = implode('/', $parts);
      $componentPath = $path . '/' . $className;
      $fullPath = 'components/' . $path;
    } else {
      $className = $componentName;
      $componentPath = $className;
      $fullPath = 'components';
    }

    $directory = $this->getNovaPath() . '/' . $fullPath;
    $filePath = $directory . '/' . $className . '.nova.php';

    // Generate component content
    $content = $this->generateComponentContent($className, $componentPath);

    if ($this->createFile($filePath, $content)) {
      $relativePath = str_replace($this->getProjectRoot() . '/', '', $filePath);
      $this->success("Nova component created successfully!");
      $this->line("📁 Location: \033[1;34m{$relativePath}\033[0m");

      $this->line("");
      $this->line("\033[1;33m💡 Usage example:\033[0m");
      $this->line("In your Nova component or page:");
      $this->line("  // Render the component");
      $this->line("  echo nova('components/{$componentPath}', ['prop' => 'value']);");
      $this->line("");
      $this->line("Or in your Action:");
      $this->line("  return Nova::render('{$componentPath}', [], 'components');");
    } else {
      $this->error("Failed to create Nova component");
    }
  }

  private function generateComponentContent(string $className, string $componentPath): string
  {
    $componentId = 'components/' . $componentPath;

    return <<<PHP
<?php
// {$componentPath}.nova.php

component('{$componentId}', function(\$c) {
    \$c->state(function() {
        return [
            'message' => 'Hello from {$className}!',
            'count' => 0
        ];
    });
    
    \$c->actions([
        'increment' => function(&\$state) {
            \$state['count']++;
            \$state['message'] = "Count is now {\$state['count']}";
        },
        'decrement' => function(&\$state) {
            \$state['count']--;
            \$state['message'] = "Count is now {\$state['count']}";
        }
    ]);
    
    \$c->view(function(\$state) {
        ?>
        <div class="p-4 border rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold mb-2">{$className} Component</h3>
            <p class="text-gray-600 mb-3">@echo(\$state->message)</p>
            <div class="flex gap-2">
                <button @click="decrement" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition">
                    -
                </button>
                <span class="px-3 py-1 bg-gray-100 rounded">@echo(\$state->count)</span>
                <button @click="increment" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition">
                    +
                </button>
            </div>
        </div>
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
