<?php

namespace Luxid\Console\Commands;

use Luxid\Console\Command;
use Luxid\Foundation\Application;

class DbCreateCommand extends Command
{
  protected string $description = 'Create a new database';

  public function handle(array $argv): int
  {
    $this->parseArguments($argv);

    $this->line("🗄️  Creating database...");

    // Load environment
    $rootPath = $this->getProjectRoot();
    $envFile = $rootPath . '/.env';

    if (!file_exists($envFile)) {
      $this->error(".env file not found");
      $this->line("Create .env file from .env.example");
      return 1;
    }

    $dotenv = \Dotenv\Dotenv::createImmutable($rootPath);
    $dotenv->load();

    $dsn = $_ENV['DB_DSN'] ?? '';
    $user = $_ENV['DB_USER'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    if (empty($dsn)) {
      $this->error("DB_DSN not set in .env");
      return 1;
    }

    // Extract database name from DSN
    $dbName = $this->extractDatabaseName($dsn);

    if (!$dbName) {
      $this->error("Could not extract database name from DSN");
      return 1;
    }

    // Create connection to server (without database)
    $serverDsn = $this->removeDatabaseFromDsn($dsn);

    try {
      $pdo = new \PDO($serverDsn, $user, $password);
      $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

      // Create database
      $sql = "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
      $pdo->exec($sql);

      $this->success("Database '{$dbName}' created successfully!");

      // Test connection to the new database
      $testPdo = new \PDO($dsn, $user, $password);
      $this->info("Connection test successful!");
    } catch (\PDOException $e) {
      $message = $e->getMessage();

      // Detect MariaDB socket auth issue (Arch/Linux)
      if (str_contains($message, 'SQLSTATE[HY000] [1698]')) {
        $this->error("Failed to create database: Access denied for root user");

        $this->line("");
        $this->line("💡 Detected MariaDB unix_socket authentication.");
        $this->line("   Your system does not allow password login for 'root'.");
        $this->line("");
        $this->line("👉 Fix options:");
        $this->line("   1. Create a dedicated database user (recommended):");
        $this->line("      sudo mariadb");
        $this->line("      CREATE DATABASE {$dbName};");
        $this->line("      CREATE USER 'luxid_user'@'localhost' IDENTIFIED BY 'password';");
        $this->line("      GRANT ALL PRIVILEGES ON {$dbName}.* TO 'luxid_user'@'localhost';");
        $this->line("");
        $this->line("   2. OR run this command as root (not recommended):");
        $this->line("      sudo php juice db:create");

        return 1;
      }

      // Detect missing driver
      if (str_contains($message, 'could not find driver')) {
        $this->error("Missing PDO MySQL driver.");

        $this->line("");
        $this->line("💡 Install/enable pdo_mysql:");
        $this->line("   Arch: enable extension in /etc/php/php.ini");
        $this->line("   Ubuntu: sudo apt install php-mysql");

        return 1;
      }

      $this->error("Failed to create database: " . $e->getMessage());
      return 1;
    }

    return 0;
  }

  private function extractDatabaseName(string $dsn): ?string
  {
    if (preg_match('/dbname=([^;]+)/', $dsn, $matches)) {
      return $matches[1];
    }

    if (preg_match('/:([^:]+)$/', $dsn, $matches)) {
      $parts = explode(';', $matches[1]);
      foreach ($parts as $part) {
        if (strpos($part, '=') !== false) {
          [$key, $value] = explode('=', $part, 2);
          if (trim($key) === 'dbname') {
            return trim($value);
          }
        }
      }
    }

    return null;
  }

  private function removeDatabaseFromDsn(string $dsn): string
  {
    // Remove dbname parameter
    return preg_replace('/;?dbname=[^;]+/', '', $dsn);
  }
}
