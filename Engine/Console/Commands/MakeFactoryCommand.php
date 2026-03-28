<?php

namespace Luxid\Console\Commands;

use Luxid\Console\Command;

class MakeFactoryCommand extends Command
{
  protected string $description = 'Create a new factory class';

  public function handle(array $argv): int
  {
    $this->parseArguments($argv);

    if (empty($this->args)) {
      $this->error("Factory name is required");
      $this->line("Usage: php juice make:factory <name>");
      $this->line("Examples:");
      $this->line("  php juice make:factory UserFactory");
      $this->line("  php juice make:factory PostFactory");
      $this->line("  php juice make:factory TodoFactory");
      return 1;
    }

    $factoryName = $this->args[0];
    $this->createFactory($factoryName);

    return 0;
  }

  private function createFactory(string $factoryName): void
  {
    $this->line("🏭 Creating factory...");

    $directory = $this->getSeedsPath();
    $filePath = $directory . '/' . $factoryName . '.php';

    $this->ensureDirectory($directory);

    // Determine entity name from factory name
    $entityName = str_replace('Factory', '', $factoryName);

    $content = $this->generateFactoryContent($factoryName, $entityName);

    if ($this->createFile($filePath, $content)) {
      $relativePath = str_replace($this->getProjectRoot() . '/', '', $filePath);
      $this->success("Factory created successfully!");
      $this->line("📁 Location: \033[1;34m{$relativePath}\033[0m");

      $this->line("");
      $this->line("\033[1;33m💡 Next steps:\033[0m");
      $this->line("1. Edit the factory to customize the data generation");
      $this->line("2. Use the factory in your seeders:");
      $this->line("   {$factoryName}::new()->count(10)->create();");
    } else {
      $this->error("Failed to create factory");
    }
  }

  private function generateFactoryContent(string $factoryName, string $entityName): string
  {
    return <<<PHP
<?php

namespace Seeds;

use Rocket\Seed\Factory;
use Rocket\Seed\Faker;
use App\Entities\\{$entityName};

class {$factoryName} extends Factory
{
    protected static function getEntityClass(): string
    {
        return {$entityName}::class;
    }
    
    protected function definition(): array
    {
        return [
            'name' => Faker::name(),
            'email' => Faker::unique()->email(),
            // Add more fields as needed
        ];
    }
    
    // Add custom states
    // public function admin(): self
    // {
    //     return \$this->state([
    //         'name' => 'Admin User',
    //         'email' => 'admin@example.com',
    //     ]);
    // }
}
PHP;
  }

  protected function getSeedsPath(): string
  {
    return $this->getProjectRoot() . '/seeds';
  }
}
