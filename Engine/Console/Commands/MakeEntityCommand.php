<?php

namespace Luxid\Console\Commands;

use Luxid\Console\Command;

class MakeEntityCommand extends Command
{
  protected string $description = 'Create a new Rocket entity';

  public function handle(array $argv): int
  {
    $this->parseArguments($argv);

    if (empty($this->args)) {
      $this->error("Entity name is required");
      $this->line("Usage: php juice make:entity <name>");
      return 1;
    }

    $entityName = $this->args[0];
    $this->createEntity($entityName);

    return 0;
  }

  private function createEntity(string $entityName): void
  {
    $this->line("📦 Creating Rocket entity...");

    $directory = $this->getAppPath() . '/Entities';
    $filePath = $directory . '/' . $entityName . '.php';

    $content = $this->generateEntityContent($entityName);

    if ($this->createFile($filePath, $content)) {
      $relativePath = str_replace($this->getProjectRoot() . '/', '', $filePath);
      $this->success("Entity created successfully!");
      $this->line("📁 Location: \033[1;34m{$relativePath}\033[0m");

      $this->line("");
      $this->line("\033[1;33m💡 Next steps:\033[0m");
      $this->line("1. Add your columns using Rocket attributes");
      $this->line("2. Create a migration: php juice make:migration create_{$entityName}_table");
      $this->line("3. Run migration: php juice migrate");
    } else {
      $this->error("Failed to create entity");
    }
  }

  private function generateEntityContent(string $entityName): string
  {
    $tableName = strtolower($entityName) . 's';
    $className = ucfirst($entityName);

    return <<<PHP
<?php

namespace App\Entities;

use Luxid\ORM\UserEntity;
use Rocket\Attributes\Entity as EntityAttr;
use Rocket\Attributes\Column;
use Rocket\Attributes\Rules\Required;

#[EntityAttr(table: '{$tableName}')]
class {$className} extends UserEntity
{
    #[Column(primary: true, autoIncrement: true)]
    public int \$id = 0;
    
    #[Column]
    #[Required]
    public string \$name = '';
    
    #[Column(autoCreate: true)]
    public string \$created_at = '';
    
    #[Column(autoCreate: true, autoUpdate: true)]
    public string \$updated_at = '';
    
    public function getDisplayName(): string
    {
        return \$this->name;
    }
    
    // Add your own lifecycle hooks if needed
    // protected function beforeSave(): void
    // {
    //     parent::beforeSave();
    //     \$this->hashPassword();
    // }
}
PHP;
  }
}
