<?php

declare(strict_types=1);

namespace Luxid\Console\Commands;

use Luxid\Console\Command;

/**
 * Bridge command for Sentinel installation.
 */
class SentinelInstallBridge extends Command
{
    protected string $description = 'Install Sentinel authentication package';

    public function handle(array $argv): int
    {
        // Use autoloading to get the InstallCommand
        if (!class_exists('\\Luxid\\Sentinel\\Console\\InstallCommand')) {
            $this->error('Sentinel InstallCommand not found.');
            $this->line('');
            $this->line('Make sure luxid/sentinel is installed:');
            $this->line('  composer require luxid/sentinel');
            $this->line('');
            return 1;
        }

        try {
            $command = new \Luxid\Sentinel\Console\InstallCommand();
            return $command->handle($argv);
        } catch (\Throwable $e) {
            $this->error('Failed to run install command: ' . $e->getMessage());
            return 1;
        }
    }
}
