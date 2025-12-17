<?php

namespace Quboticlabs\PhpToolConfigs;

class InstallHooks
{
    /**
     * Install git hooks to the project
     *
     * @param string|null $projectRoot The project root directory (defaults to current working directory)
     * @return void
     */
    public static function install(?string $projectRoot = null): void
    {
        $projectRoot = $projectRoot ?? getcwd();
        
        // Find the package root (when installed via composer, it's in vendor/)
        $packageRoot = self::findPackageRoot();
        $hooksDir = $packageRoot . '/hooks';
        $gitHooksDir = $projectRoot . '/.git/hooks';

        if (!is_dir($gitHooksDir)) {
            throw new \RuntimeException("Not a git repository. Please run 'git init' first.");
        }

        if (!is_dir($hooksDir)) {
            throw new \RuntimeException("Hooks directory not found in package at: {$hooksDir}");
        }

        $hooks = ['pre-commit', 'pre-push'];

        foreach ($hooks as $hook) {
            $sourceHook = $hooksDir . '/' . $hook;
            $targetHook = $gitHooksDir . '/' . $hook;

            if (!file_exists($sourceHook)) {
                echo "⚠️  Warning: Hook file not found: {$hook}\n";
                continue;
            }

            // Copy the hook file
            if (!copy($sourceHook, $targetHook)) {
                throw new \RuntimeException("Failed to copy hook: {$hook}");
            }

            // Make it executable
            chmod($targetHook, 0755);

            echo "✅ Installed hook: {$hook}\n";
        }

        echo "\n🎉 Git hooks installed successfully!\n";
    }

    /**
     * Find the package root directory
     *
     * @return string
     */
    private static function findPackageRoot(): string
    {
        // Try to find via Composer's vendor directory
        $reflection = new \ReflectionClass(self::class);
        $file = $reflection->getFileName();
        
        // Navigate from src/InstallHooks.php to package root
        $packageRoot = dirname($file, 2);
        
        // Verify it's the correct package by checking for hooks directory
        if (is_dir($packageRoot . '/hooks')) {
            return $packageRoot;
        }
        
        // Fallback: try to find via Composer autoload
        $composerFile = $packageRoot . '/composer.json';
        if (file_exists($composerFile)) {
            return $packageRoot;
        }
        
        throw new \RuntimeException("Could not determine package root directory.");
    }
}

