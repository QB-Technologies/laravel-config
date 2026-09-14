<?php

namespace QBTechnologies\LaravelConfig;

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
        $gitHooksDir = self::resolveGitHooksDir($projectRoot);

        if ($gitHooksDir === null) {
            // Fail soft: CI/Docker builds often have no repository at all. Skip
            // installation rather than break the composer lifecycle when this is
            // wired into post-update-cmd.
            echo "⚠️  Skipping git hook installation: no git repository found.\n";

            return;
        }

        if (!is_dir($gitHooksDir) && !mkdir($gitHooksDir, 0755, true) && !is_dir($gitHooksDir)) {
            echo "⚠️  Skipping git hook installation: could not create {$gitHooksDir}\n";

            return;
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

        echo "\n🎉 Git hooks installed successfully in {$gitHooksDir}\n";
    }

    /**
     * Work out where git will actually look for this repository's hooks.
     *
     * A plain checkout keeps them in .git/hooks, but a worktree's .git is a file
     * pointing at .git/worktrees/<name>, and hooks are shared from the common
     * directory rather than living there. core.hooksPath can move them somewhere
     * else again. Git knows about all three, so ask it first and only read .git
     * by hand when git isn't available.
     *
     * @return string|null Absolute path, or null when this isn't a repository
     */
    private static function resolveGitHooksDir(string $projectRoot): ?string
    {
        return self::askGitForHooksDir($projectRoot) ?? self::readHooksDirFromDotGit($projectRoot);
    }

    private static function askGitForHooksDir(string $projectRoot): ?string
    {
        if (!function_exists('proc_open')) {
            return null;
        }

        $process = @proc_open(
            ['git', 'rev-parse', '--git-path', 'hooks'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $projectRoot
        );

        if (!is_resource($process)) {
            return null;
        }

        $output = stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        if (proc_close($process) !== 0 || !is_string($output)) {
            return null;
        }

        $path = trim($output);

        return $path === '' ? null : self::toAbsolutePath($path, $projectRoot);
    }

    private static function readHooksDirFromDotGit(string $projectRoot): ?string
    {
        $dotGit = rtrim($projectRoot, '/\\') . '/.git';

        if (is_dir($dotGit)) {
            return $dotGit . '/hooks';
        }

        if (!is_file($dotGit)) {
            return null;
        }

        $contents = @file_get_contents($dotGit);

        if ($contents === false || preg_match('/^gitdir:\s*(.+)$/m', $contents, $matches) !== 1) {
            return null;
        }

        $gitDir = self::toAbsolutePath(trim($matches[1]), $projectRoot);

        // A worktree's git dir holds a commondir file pointing back at the shared
        // directory, which is where the hooks actually live.
        $commonDirFile = $gitDir . '/commondir';

        if (is_file($commonDirFile)) {
            $commonDir = @file_get_contents($commonDirFile);

            if (is_string($commonDir) && trim($commonDir) !== '') {
                $gitDir = self::toAbsolutePath(trim($commonDir), $gitDir);
            }
        }

        // commondir is relative, so the joined path carries a ../.. through it.
        $resolved = realpath($gitDir);

        return ($resolved === false ? $gitDir : $resolved) . '/hooks';
    }

    private static function toAbsolutePath(string $path, string $base): string
    {
        $isAbsolute = str_starts_with($path, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $path) === 1;

        return $isAbsolute ? $path : rtrim($base, '/\\') . '/' . $path;
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
