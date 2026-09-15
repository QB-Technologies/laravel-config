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

        // Fail soft: CI/Docker builds often have no repository at all, and the
        // resolver refuses to write outside this project. Skip installation rather
        // than break the composer lifecycle when this is wired into post-update-cmd.
        if ($gitHooksDir === null) {
            return;
        }

        // @-suppressed: the friendly message below says the same thing as the raw
        // PHP warning, and a read-only or wrong-uid target is a normal situation
        // here rather than a programming error.
        if (!is_dir($gitHooksDir) && !@mkdir($gitHooksDir, 0755, true) && !is_dir($gitHooksDir)) {
            echo "⚠️  Skipping git hook installation: could not create {$gitHooksDir}\n";

            return;
        }

        if (!is_dir($hooksDir)) {
            throw new \RuntimeException("Hooks directory not found in package at: {$hooksDir}");
        }

        $hooks = ['pre-commit', 'pre-push'];
        $installed = 0;

        foreach ($hooks as $hook) {
            $sourceHook = $hooksDir . '/' . $hook;
            $targetHook = $gitHooksDir . '/' . $hook;

            if (!file_exists($sourceHook)) {
                echo "⚠️  Warning: Hook file not found: {$hook}\n";
                continue;
            }

            // Every failure from here on warns and carries on. This runs from
            // post-update-cmd, and the Dockerfiles run a bare composer install, so
            // throwing would turn an unwritable hooks directory into a failed image
            // build.
            if (!@copy($sourceHook, $targetHook)) {
                echo "⚠️  Could not install {$hook} into {$gitHooksDir}, skipping.\n";
                continue;
            }

            if (!@chmod($targetHook, 0755)) {
                echo "⚠️  Installed {$hook} but could not make it executable; git will ignore it.\n";
            }

            $installed++;

            echo "✅ Installed hook: {$hook}\n";
        }

        if ($installed !== count($hooks)) {
            echo "\n⚠️  Installed {$installed} of " . count($hooks) . " git hooks in {$gitHooksDir}\n";

            return;
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
        $gitError = null;
        $hooksDir = self::askGitForHooksDir($projectRoot, $gitError) ?? self::readHooksDirFromDotGit($projectRoot);

        if ($hooksDir === null) {
            echo "⚠️  Skipping git hook installation: no git repository found.\n";

            // Without this a refusal git can explain, safe.directory being the
            // common one, looks identical to there being no repository.
            if ($gitError !== null) {
                echo "   git said: {$gitError}\n";
            }

            return null;
        }

        // git rev-parse answers for whichever repository it finds walking up, so a
        // project that isn't itself a checkout resolves an enclosing one's hooks.
        $topLevel = self::askGit(['rev-parse', '--show-toplevel'], $projectRoot);

        if ($topLevel !== null && self::normalisePath($topLevel) !== self::normalisePath($projectRoot)) {
            echo "⚠️  Skipping git hook installation: {$projectRoot} is not a repository root.\n";

            return null;
        }

        if (self::isInside($hooksDir, self::gitCommonDir($projectRoot))) {
            return $hooksDir;
        }

        // core.hooksPath puts the hooks outside the repository. Honour it when this
        // repository asked for it, but not when it comes from global config: that
        // would hand every repository on the machine this project's hooks.
        if (self::askGit(['config', '--local', '--get', 'core.hooksPath'], $projectRoot) === null) {
            echo "⚠️  Skipping git hook installation: core.hooksPath points at {$hooksDir}, outside this repository.\n";

            return null;
        }

        echo "ℹ️  core.hooksPath is set for this repository, installing into {$hooksDir}\n";

        return $hooksDir;
    }

    private static function askGitForHooksDir(string $projectRoot, ?string &$error = null): ?string
    {
        $path = self::askGit(['rev-parse', '--git-path', 'hooks'], $projectRoot, $error);

        return $path === null ? null : self::toAbsolutePath($path, $projectRoot);
    }

    /**
     * @param array<int, string> $arguments
     * @param string|null $error Git's own complaint, when it has one
     */
    private static function askGit(array $arguments, string $cwd, ?string &$error = null): ?string
    {
        $error = null;

        if (!function_exists('proc_open')) {
            return null;
        }

        $process = @proc_open(
            array_merge(['git'], $arguments),
            [
                // Without a stdin of its own git inherits ours, and a credential
                // helper could sit there waiting on input nobody is going to type.
                0 => ['file', PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $cwd
        );

        if (!is_resource($process)) {
            return null;
        }

        $output = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        if (proc_close($process) !== 0 || !is_string($output)) {
            $error = is_string($stderr) && trim($stderr) !== '' ? trim($stderr) : null;

            return null;
        }

        $output = trim($output);

        return $output === '' ? null : $output;
    }

    private static function gitCommonDir(string $projectRoot): string
    {
        $commonDir = self::askGit(['rev-parse', '--git-common-dir'], $projectRoot);

        return self::toAbsolutePath($commonDir ?? '.git', $projectRoot);
    }

    private static function isInside(string $path, string $directory): bool
    {
        $path = self::normalisePath($path);
        $directory = self::normalisePath($directory);

        return $path === $directory || str_starts_with($path, $directory . '/');
    }

    /**
     * Resolve a path for comparison. The hooks directory may not exist yet, so
     * fall back to resolving its parent and appending the name.
     */
    private static function normalisePath(string $path): string
    {
        $path = rtrim(str_replace('\\', '/', $path), '/');
        $resolved = realpath($path);

        if ($resolved !== false) {
            return rtrim(str_replace('\\', '/', $resolved), '/');
        }

        $parent = realpath(dirname($path));

        if ($parent !== false) {
            return rtrim(str_replace('\\', '/', $parent), '/') . '/' . basename($path);
        }

        return $path;
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

        return $gitDir . '/hooks';
    }

    private static function toAbsolutePath(string $path, string $base): string
    {
        $isAbsolute = str_starts_with($path, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $path) === 1;
        $joined = $isAbsolute ? $path : rtrim($base, '/\\') . '/' . $path;

        // The gitdir: line and commondir are both relative, so the joined path
        // carries a ../.. through it and that ends up in the messages below.
        return realpath($joined) ?: $joined;
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
