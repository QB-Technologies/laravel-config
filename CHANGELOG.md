# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-09-23

### Breaking
- Consuming apps can no longer pick their own versions of the four code-quality tools. A root `composer.json` requirement is intersected with this package's constraint rather than overriding it, so a conflicting local declaration gives an unsolvable set and the only route to a different version is a release here.
- Requiring Larastan puts an `illuminate/*` floor into every consumer's dependency graph. This package had no Laravel constraint at all before, and now has a say in which Laravel major a consuming app can resolve to.
- `phpstan/phpstan ^1.10` is gone. PHPStan 2.x arrives transitively through Larastan instead, so a consumer still declaring PHPStan 1.x gets a resolver conflict rather than a message it can read.
- Consuming apps must remove `vendor/larastan/larastan/extension.neon` from their own `.phpstan.neon`. The shared config includes it now, and PHPStan treats a file included twice as a hard error, so an app that keeps the line fails on the first run after upgrading.
- The shared config no longer registers `Spatie\Ray\PHPStan\RemainingRayCallRule`. An app that wants the rule declares it itself and needs `spatie/laravel-ray` of its own.
- The hooks run the app's composer scripts where it defines them, so a push runs the app's flags rather than this package's. An app with no `cs`, `phpstan`, `rector` or `phpcpd` script is unaffected.
- The pre-push hook calls `php artisan` for its migration check unless the app sets `DEV_RUNTIME` in a `.hooks-env` at its root (for example `DEV_RUNTIME=sail`). An app that relied on the hook finding `./vendor/bin/sail` by itself has to add that file.

### Changed
- All four code-quality tools now ship from `require` instead of `require-dev`, so consuming apps get them transitively and no longer declare their own copies. `friendsofphp/php-cs-fixer ^3.75` and `rector/rector ^2.0` moved across, and `phpstan/phpstan ^1.10` was replaced by `larastan/larastan ^3.4`.
- The pre-commit and pre-push hooks now run each check through the app's composer script when it defines one, falling back to the direct `vendor/bin` call when it does not. `cs`, `phpstan`, `rector`, and `phpcpd` are the names they look for. The hooks previously hardcoded their own flags while the apps kept the same flags in composer scripts, so the two could drift, and an app README telling developers to raise PHPStan's memory in the `phpstan` script was describing something the hook never read. Composer's process timeout is disabled for these calls so a slow analysis does not stop at 300 seconds with a Composer exception in place of the tool's output.
- Git hooks install a shared `hooks/_lib` beside `pre-commit` and `pre-push`. Both hooks source it to load `.hooks-env` and honour `DEV_RUNTIME` (`native`, `herd`, `valet`, `sail`) the same way gitscripts does, falling back to host `php` / `composer` when it is unset or unavailable. Composer script discovery and direct `vendor/bin` fallbacks use that runtime too.
- The shared `config/.phpstan.neon` no longer registers `Spatie\Ray\PHPStan\RemainingRayCallRule`, and now includes Larastan's `extension.neon` itself. The Ray rule was declared without `spatie/laravel-ray` being a dependency, so a consumer without Ray got `Service 'rules.0': Class 'Spatie\Ray\PHPStan\RemainingRayCallRule' not found` instead of an analysis, and a consumer that had Ray and declared the rule itself saw every stray `ray()` call reported twice. Declaring that rule is the app's job. Larastan is a hard dependency here as of this release, so including its extension is this package's job, and consuming apps must drop that line from their own `.phpstan.neon`: PHPStan treats a file included twice as a hard error.

### Fixed
- The pre-push hook no longer assumes `./vendor/bin/sail`. It preferred that binary whenever it existed in `vendor`, and Sail arrives there as a transitive dependency of apps that never use it, so the migration check ran against a container that was not running and failed the push. Apps now say how they reach artisan in a `.hooks-env`, which is also the one place a consumer can override hook behaviour without editing an installed hook that the next install overwrites.
- The pre-push hook's project-structure check now runs on macOS. It built its list of required directories with `declare -A`, which needs bash 4, and macOS ships bash 3.2. There bash reads the literal as an indexed array and arithmetic-evaluates each subscript, so `app/Providers` is parsed as a division and fails with `division by 0`. An arithmetic error in a non-interactive shell abandons the command being run, which unwinds the whole function, so nothing after the declaration executed and `set -e` never fired. The check reported success without validating anything. It now uses a plain indexed array, which works on both.
- `install-hooks` now works in a git worktree. It assumed hooks live in `<project>/.git/hooks`, but a worktree's `.git` is a file pointing at `.git/worktrees/<name>` and hooks are shared from the main checkout, so the installer found no `.git/hooks` directory and silently skipped. It now asks git where the hooks directory is, which also covers a repository-local `core.hooksPath`, and falls back to reading `.git` itself when git is not on `PATH`.
- `install-hooks` now refuses to write outside the repository it runs in. Asking git for the hooks directory answers for whichever repository git finds walking up, so running the installer from a directory nested inside another checkout wrote this project's hooks into that one, and a `core.hooksPath` in global config sent them to the directory every repository on the machine shares. It now installs only when the project directory is the repository root, and only into that repository's own hooks directory or a `core.hooksPath` the repository sets for itself. Every other case says what it skipped and why.
- `install-hooks` no longer fails the composer lifecycle when it cannot write a hook. It already warned and skipped when there was no repository or when the hooks directory could not be created, but a failed `copy()` threw. `bin/install-hooks` turns that into `exit(1)`, consuming apps include it from `post-install-cmd`, and a bare `composer install` in a Dockerfile then fails the image build. A failed copy and a failed `chmod` now warn and carry on, and the closing line reports how many hooks landed.
- The pre-push hook now checks the files a push actually sends. It read `git diff --cached`, which in a pre-push hook compares the index to HEAD, and everything is committed by then, so the list was always empty and the PHP syntax check, ESLint, Prettier and the `.env` guard all passed without looking at anything. It now diffs the commits that are not on any remote.
- `check_unnecessary_files` matches that same list instead of walking the working tree with `find`. It used to flag files git ignores, and its `.idea/` and `.vscode/` patterns matched nothing at all, since `find -name` does not take a trailing slash. An ignored editor directory is now left alone and a committed one is caught.
- The pre-push hook no longer runs `git add` on its way out. Staging files during a push is either a no-op or a surprise.
- `install-hooks` prints git's own complaint when it cannot find a hooks directory, so a `safe.directory` refusal no longer looks the same as there being no repository. It also gives git `/dev/null` on stdin rather than inheriting the caller's, and normalises the paths it reports, so messages no longer carry a `../..` through them.
- The package's own dependencies can be installed again. The old `phpstan/phpstan ^1.10` pin conflicted with `rector ^2.0` and `larastan ^3.x`, both of which need `phpstan ^2.x`, so `composer install` in a checkout of this package died on a resolver conflict. Requiring Larastan pulls a compatible PHPStan 2.x transitively and matches what the consuming apps actually run.

## [1.1.1] - 2026-07-31

### Fixed
- `bin/install-hooks` now locates the Composer autoloader correctly when the package is installed as a dependency. It previously hardcoded `../vendor/autoload.php`, which only exists in a standalone checkout, so `vendor/bin/install-hooks` fatally errored inside a consuming app. It now uses Composer's `$_composer_autoload_path`, falling back to the app's `vendor/autoload.php`.

## [1.1.0] - 2026-07-30

### Added
- PHPCPD (copy-paste detection) as a `require` dependency so it is provided to consuming apps
- PHPCPD check to the pre-push hook (`--min-lines 10 --min-tokens 70 --suffix .php domain/`)

### Changed
- `InstallHooks` now fails soft (warns and skips) when no `.git/hooks` directory is present, making it safe to run from a consumer's `post-update-cmd`

## [1.0.3] - 2026-05-27
- Added memory limit to PHPStan analyse
- Commented out testing in pre push hook as we don't want this yet
- Removed erroneous line from validate_directory_structure method in pre push
- Extended PHP CS Fixer config to remove unneeded curly braces

## [1.0.2] - 2026-03-04

### Changed
- Add group_import to PHPCSFIXER

## [1.0.1] - 2026-02-20

### Changed
- Updated configs for correct configurations
- Updated README documentation

## [1.0.0] - 2026-02-20

### Added
- Initial package setup with PHP-CS-Fixer, PHPStan, and Rector configurations
- Base configuration files for all three tools
- ConfigHelper class for easy path access
- Comprehensive README with setup and usage instructions
- Git hooks for automated code quality checks

