<?php

namespace Luxid\Console\Commands;

use Luxid\Console\Command;

class MakeSeederCommand extends Command
{
  protected string $description = 'Create a new seeder class';

  public function handle(array $argv): int
  {
    $this->parseArguments($argv);

    if (empty($this->args)) {
      $this->error("Seeder name is required");
      $this->line("Usage: php juice make:seeder <name>");
      $this->line("Examples:");
      $this->line("  php juice make:seeder UserSeeder");
      $this->line("  php juice make:seeder PostSeeder");
      $this->line("  php juice make:seeder TodoSeeder");
      return 1;
    }

    $seederName = $this->args[0];
    $this->createSeeder($seederName);

    return 0;
  }

  private function createSeeder(string $seederName): void
  {
    $this->line("🌱 Creating seeder...");

    $directory = $this->getSeedsPath();
    $filePath = $directory . '/' . $seederName . '.php';

    // Ensure the seeds directory exists
    $this->ensureDirectory($directory);

    $content = $this->generateSeederContent($seederName);

    if ($this->createFile($filePath, $content)) {
      $relativePath = str_replace($this->getProjectRoot() . '/', '', $filePath);
      $this->success("Seeder created successfully!");
      $this->line("📁 Location: \033[1;34m{$relativePath}\033[0m");

      $this->line("");
      $this->line("\033[1;33m💡 Next steps:\033[0m");
      $this->line("1. Edit the seeder to add your data");
      $this->line("2. Add the seeder to DatabaseSeeder:");
      $this->line("   \$this->call({$seederName}::class);");
      $this->line("3. Run seeder: \033[1;32mphp juice seed\033[0m");
    } else {
      $this->error("Failed to create seeder");
    }
  }

  private function generateSeederContent(string $seederName): string
  {
    // Determine entity name from seeder name
    // e.g., UserSeeder -> User
    $entityName = str_replace('Seeder', '', $seederName);

    // Guess if it's a common entity
    $hasEntity = in_array($entityName, ['User', 'Post', 'Todo', 'Product', 'Category', 'Order', 'Comment']);

    if ($hasEntity) {
      return $this->generateEntitySeeder($seederName, $entityName);
    }

    return $this->generateGenericSeeder($seederName);
  }

  private function generateEntitySeeder(string $seederName, string $entityName): string
  {
    // Escape the $i variable properly
    return <<<PHP
<?php

namespace Seeds;

use Rocket\Seed\Seeder;
use App\Entities\\{$entityName};

class {$seederName} extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This method creates sample data for the {$entityName} entity.
     * You can customize this to fit your needs.
     */
    public function run(): void
    {
        echo "🌱 Seeding {$entityName}s...\n";
        
        // Create sample records
        for (\$i = 1; \$i <= 10; \$i++) {
            \${$entityName} = new {$entityName}();
            \${$entityName}->name = "Sample {$entityName} " . \$i;
            \${$entityName}->save();
        }
        
        echo "  ✓ Created 10 sample {$entityName}s\n";
        
        // You can add more specific records here
        // Example: Create an admin user
        // \$admin = new {$entityName}();
        // \$admin->name = "Admin";
        // \$admin->email = "admin@example.com";
        // \$admin->save();
        
        echo "✅ {$entityName} seeding completed!\n";
    }
}
PHP;
  }

  private function generateGenericSeeder(string $seederName): string
  {
    return <<<PHP
<?php

namespace Seeds;

use Rocket\Seed\Seeder;

class {$seederName} extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Add your data seeding logic here.
     */
    public function run(): void
    {
        echo "🌱 Seeding {$seederName}...\n";
        
        // TODO: Add your seeding logic here
        // Example:
        // \$user = new \App\Entities\User();
        // \$user->name = 'John Doe';
        // \$user->email = 'john@example.com';
        // \$user->save();
        
        echo "✅ {$seederName} completed!\n";
    }
}
PHP;
  }

  protected function getSeedsPath(): string
  {
    return $this->getProjectRoot() . '/seeds';
  }
}
