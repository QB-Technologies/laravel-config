# PHP Tool Configs

> 🎯 **Standardized PHP code quality configurations** for PHP-CS-Fixer, PHPStan, Rector, and PHPCPD. Share consistent coding standards across all your projects with a single Composer package.

This package provides battle-tested, production-ready configuration files that can be shared across all your PHP projects, ensuring consistent code quality, formatting standards, and static analysis rules. Stop duplicating configuration files—maintain them in one place and reuse everywhere.

## ✨ Features

- 🔧 **Pre-configured PHP-CS-Fixer** rules for consistent code formatting
- 🔍 **PHPStan** (via Larastan) configuration with sensible defaults for static analysis
- 📋 **PHPCPD** copy-paste detection
- ⚡ **Rector** setup for automated code refactoring and modernization
- 🪝 **Git hooks** included (pre-commit & pre-push) to enforce standards
- 📦 **Easy installation** via Composer
- 🎨 **Fully extensible**—override or extend any configuration
- 🔄 **Semantic versioning** for predictable updates

## 🚀 Quick Start

```bash
composer require --dev qb-technologies/laravel-config
```

That's it! Then extend the base configurations in your project as needed.

The package requires PHP-CS-Fixer, PHPStan (via Larastan), Rector, and PHPCPD, so installing it puts all four binaries in your `vendor/bin`. Don't declare them in your own `composer.json`: this package owns their versions.

Apps can't override those versions locally. Composer intersects a root `composer.json` requirement with the dependency's constraint rather than letting the root win, so declaring a conflicting version in your app gives you an unsolvable set, not a local win. The only route to a different version is a release of this package.

The four tools are pinned to exact versions, not ranges:

| tool | version |
| --- | --- |
| `friendsofphp/php-cs-fixer` | 3.95.27 |
| `larastan/larastan` | 3.12.2 |
| `rector/rector` | 2.6.7 |
| `systemsdk/phpcpd` | 8.0.0 |

A range would leave each app on whatever was newest the last time it ran `composer update`, which is
how one app came to format with PHP-CS-Fixer 3.92.4 while another used 3.95.2, against the same
shared config. An exact pin is the only way every app formats and analyses identically. The cost is
that a tool upgrade needs a release here; that was already true of the major constraint.

PHPCPD is held at 8.0.0 deliberately. From 8.1.1 it requires `phpunit/php-timer ^8.0`, and an app
on Pest 3 gets PHPUnit 11, which requires `php-timer ^7.0.1`. Only one version of a package can be
installed, so a newer PHPCPD makes this package uninstallable for any app testing with Pest 3.
Raising it means waiting for those apps to reach PHPUnit 12.

PHPStan is not pinned directly. It arrives through Larastan and Rector, which both require
`^2.2.14`, so apps can still differ by a patch there.

### Laravel version support

This package requires Larastan, and Larastan requires `illuminate/*`. So this package has a say in which Laravel major a consuming app can resolve to. The current ceiling is whatever the installed Larastan allows, which is the `illuminate/*` constraint in `vendor/larastan/larastan/composer.json`. Larastan widens it over time within 3.x, so reading it there beats a number written down here.

When a new Laravel major lands and Larastan needs a new major to support it, this package needs a release with a widened constraint before consuming apps can upgrade. Moving the pin to a Larastan 4 release once one exists is routine maintenance. It's much less pleasant to work out under upgrade pressure, so it's worth doing early.

### A note for anyone changing these dependencies

The shared `config/.phpstan.neon` includes `vendor/larastan/larastan/extension.neon` itself, so consuming apps must not include it again. PHPStan treats a file included twice as a hard error, not a warning. Apps that carried that line before this release have to drop it when they upgrade. Swapping Larastan for something else is now a change here rather than in every consumer.

## 📦 Installation

### Step 1: Install the Package

```bash
composer require --dev qb-technologies/laravel-config
```

### Step 2: Install Git Hooks (Optional but Recommended)

Install the pre-commit and pre-push hooks:

```bash
php vendor/bin/install-hooks
```

Wire it into both lifecycle events so a normal setup needs no manual step. Give it a name and
reference that from each event, rather than repeating the command twice:

```json
"scripts": {
    "hooks:install": "@php -r \"if (file_exists('vendor/bin/install-hooks')) { include 'vendor/bin/install-hooks'; } else { echo 'Skipping git hook install (dev dependencies not installed).', PHP_EOL; }\"",
    "post-install-cmd": ["@hooks:install"],
    "post-update-cmd": ["@hooks:install"]
}
```

Keep the `file_exists` guard, so a `--no-dev` install in a Dockerfile says what it skipped instead
of failing.

An earlier version of this README told consumers to call `InstallHooks::install()` instead of
including the bin file, on the grounds that PHP strips a shebang only from the script it runs
directly, so an include would print a stray `#!/usr/bin/env php`. That is wrong: PHP strips the
line on include too, measured on 8.3.32 and 8.4.18. The include is shorter and needs no escaped
namespace separators, so it is the one shown here.

The installer asks git where the hooks belong rather than assuming `.git/hooks`, so it works
in a worktree (where `.git` is a file and hooks are shared from the main checkout) and honours a
`core.hooksPath` the repository sets for itself. It prints the directory it used. Copying the
files by hand is not equivalent: in a worktree `.git/hooks` does not exist, and the copy goes
nowhere git will look.

It only writes to the repository you run it in. Run it somewhere that is not a checkout root, or
with `core.hooksPath` set in your global config, and it tells you what it skipped and why instead
of writing hooks into a repository or a shared directory you did not mean.

### Step 3: Set up Laravel Boost in the app (once per machine)

This package requires [Laravel Boost](https://laravel.com/docs/boost), so an app with `qb-technologies/laravel-config` in `require-dev` gets Boost on `composer install` or `composer update`. Do not add `laravel/boost` to the app's own `composer.json`. `composer install --no-dev` skips this package, so Boost is not installed in production.

Boost requires `illuminate/console|contracts|routing|support` at `^11.45.3|^12.41.1|^13.0`. An app on an older Laravel cannot install this release without upgrading the framework first.

Run the installer **interactively**. It cannot be scripted, because the step that pulls in the shared rules is a prompt:

```bash
php artisan boost:install
```

Reach artisan the way your app does. On Sail that is `./vendor/bin/sail artisan boost:install`, and
under Herd `herd php artisan boost:install`. This is a one-off command you type, so it does not read
the `DEV_RUNTIME` the hooks use.

Then, in order:

1. Pick your agent (Cursor, Claude Code, Codex).
2. Include guidelines, and the MCP server if you want it.
3. When Boost lists packages that ship guidelines, **select `qb-technologies/laravel-config`**. Miss this and you get no QB rules.
4. Enable the `laravel-boost` MCP server in your agent.

The shared rules live in `resources/boost/guidelines/core.blade.php`. Boost reads that file from `vendor` and writes it into the agent's always-on guideline file, which is `AGENTS.md` for every agent unless the app overrides the path.

Keeping the rules current after a package bump needs `boost:update` in the app's Composer `post-update-cmd`:

```json
"post-update-cmd": [
    "@php artisan boost:update --ansi --no-interaction"
]
```

That refreshes guidelines for packages you have already selected. It does **not** add newly available ones: `boost:update` skips its discovery prompt whenever it detects a Composer run, so after a bump that introduces new guidelines you have to re-run `boost:install`, or `php artisan boost:update` from a terminal, and select the package there.

`boost:install --no-interaction` is not a shortcut. It writes a `boost.json` with no agents and no packages, which both omits the QB rules and makes the next `boost:update` fail with `Please set up Boost with [php artisan boost:install] first.`

Generated files (`.cursor/`, `.mcp.json`, `boost.json`, `AGENTS.md`, `CLAUDE.md`) can be gitignored. Do not gitignore `.ai/rules`; those are shared project rules and should be committed.

If the generated MCP command is `php artisan boost:mcp` and the app runs in Sail, point the server at `./vendor/bin/sail artisan boost:mcp`. The MCP server reads whatever database the app's `.env` points at, and exposes read-only SQL, schema and log reading, so point it at a local or testing database. The `tinker` tool, which runs arbitrary PHP, is off unless `BOOST_TINKER_TOOL_ENABLED=true`.

### What the hooks run

Each check prefers the app's own composer script and falls back to calling the binary directly.
`composer cs`, `composer phpstan`, `composer rector`, and `composer phpcpd` are the names it looks
for. Define them and the hook runs a check exactly the way CI does, so the two cannot drift apart
on flags. Leave them undefined and the hook keeps its own defaults, which is what every consumer
got before.

The hook disables Composer's process timeout for these calls, so a slow analysis on a large tree
does not die at 300 seconds with a Composer exception in place of the tool's own output.

### Per-developer hook settings (`.hooks-env`)

Copy a `.hooks-env` file to your project root (gitignore it). Both hooks source it for a single
setting, `DEV_RUNTIME`, which matches gitscripts so the same value can drive `devcom` / `devart`
and the git hooks.

| `DEV_RUNTIME` | How hooks run composer, artisan, `php -l`, and `vendor/bin` fallbacks |
| --- | --- |
| unset or `native` | `composer`, `php artisan`, `php -l`, `vendor/bin/...` on the host |
| `herd` / `valet` | `herd composer`, `herd php artisan`, and so on |
| `sail` | `./sail` (or `vendor/bin/sail`) for composer, artisan, and tools |

If `DEV_RUNTIME` is unset, invalid, or the tool is missing (for example `sail` with no sail
script), hooks fall back to native commands and print one warning.

```bash
# .hooks-env — Sail app (TMU, etc.)
DEV_RUNTIME=sail

# Herd site with an isolated PHP version
DEV_RUNTIME=herd
```
The file is optional and belongs in `.gitignore`, since the right answer differs per developer.

Earlier versions used `./vendor/bin/sail` whenever it existed. That is wrong: Sail arrives in
`vendor` as a transitive dependency of apps that never use it, and the check then failed on a
container that was not running.

## Usage

### PHP-CS-Fixer

Create a `.php-cs-fixer.php` file in your project root:

```php
<?php

use PhpCsFixer\Finder;

$baseConfig = require __DIR__ . '/vendor/qb-technologies/laravel-config/config/.php-cs-fixer.php';

$finder = Finder::create()
    ->in(__DIR__ . '/app')
    ->in(__DIR__ . '/domain')
    ->in(__DIR__ . '/routes')
    ->in(__DIR__ . '/config')
    ->in(__DIR__ . '/tests')
    ->exclude('bootstrap')
    ->exclude('storage')
    ->exclude('vendor');

return $baseConfig->setFinder($finder);
```

### PHPStan

Create a `.phpstan.neon` file in your project root. Don't add
`vendor/larastan/larastan/extension.neon` here, the shared config already includes it and PHPStan
fails on a file included twice.

```neon
includes:
    - ./vendor/qb-technologies/laravel-config/config/.phpstan.neon

parameters:
    level: 9  # Adjust level as needed for your project
    paths:
        - app
        - domain
        - routes
        - database
        - config
    ignoreErrors: []
    # Add project-specific ignore errors here if needed

rules:
    - Spatie\Ray\PHPStan\RemainingRayCallRule  # needs spatie/laravel-ray in the app
```

The Ray rule is the app's to declare. This package used to register it in the shared config
without requiring `spatie/laravel-ray`, so any consumer without Ray installed got
`Class 'Spatie\Ray\PHPStan\RemainingRayCallRule' not found` instead of an analysis.

Or, if you want to use the base config directly and only override specific parameters:

```neon
includes:
    - ./vendor/qb-technologies/laravel-config/config/.phpstan.neon

parameters:
    paths:
        - app
        - domain
    # Only override what you need
```

### Rector

Create a `rector.php` file in your project root:

```php
<?php

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;

$baseConfig = require __DIR__ . '/vendor/qb-technologies/laravel-config/config/rector.php';

return $baseConfig
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/domain',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        RenamePropertyToMatchTypeRector::class => [
            // Add project-specific files to skip
            __DIR__ . '/domain/Some/DTOs/SomeDTO.php',
        ],
        RenameParamToMatchTypeRector::class => [
            // Add project-specific files to skip
        ],
    ]);
```

## Updating Configurations

When you need to update the shared configurations:

1. Make changes to the configuration files in this repository
2. Commit and push the changes
3. Create a new tag (following semantic versioning):
   ```bash
   git tag -a v1.0.1 -m "Update PHP-CS-Fixer rules"
   git push origin v1.0.1
   ```
4. Update the version constraint in your project's `composer.json` if needed
5. Run `composer update qb-technologies/laravel-config` in your projects

## Versioning

This package follows [Semantic Versioning](https://semver.org/):
- **MAJOR** version for breaking changes (e.g., removing a rule)
- **MINOR** version for new features (e.g., adding new rules)
- **PATCH** version for bug fixes and minor updates

## Customization

Each project can extend or override the base configurations as needed. The base configurations are designed to be:
- **Comprehensive**: Cover common code quality standards
- **Extensible**: Easy to extend with project-specific rules
- **Flexible**: Allow projects to customize paths, levels, and skip lists

## Contributing

When proposing changes to the shared configurations:

1. Test the changes in at least one project
2. Document the reasoning for the change
3. Ensure backward compatibility or clearly mark as a breaking change
4. Update this README if usage instructions change

## License

MIT License - see [LICENSE](LICENSE) file for details.
