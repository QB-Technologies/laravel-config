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

### Laravel version support

This package requires Larastan, and Larastan requires `illuminate/*`. So this package has a say in which Laravel major a consuming app can resolve to. The current ceiling is whatever the installed Larastan allows, which is the `illuminate/*` constraint in `vendor/larastan/larastan/composer.json`. Larastan widens it over time within 3.x, so reading it there beats a number written down here.

When a new Laravel major lands and Larastan needs a new major to support it, this package needs a release with a widened constraint before consuming apps can upgrade. Widening to `^3.4 || ^4.0` once Larastan 4 exists is routine maintenance. It's much less pleasant to work out under upgrade pressure, so it's worth doing early.

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

This also runs automatically from `post-install-cmd` and `post-update-cmd` if you wire it
up there, so a normal setup needs no manual step. Call the class rather than `include`-ing the
bin file: PHP only strips a shebang from the script it runs directly, so an include prints a
stray `#!/usr/bin/env php` on every install. The `file_exists` guard keeps a `--no-dev` install
quiet.

```json
"post-install-cmd": [
    "@php -r \"if (file_exists('vendor/autoload.php') && file_exists('vendor/bin/install-hooks')) { require 'vendor/autoload.php'; QBTechnologies\\\\LaravelConfig\\\\InstallHooks::install(); }\""
]
```

The installer asks git where the hooks belong rather than assuming `.git/hooks`, so it works
in a worktree (where `.git` is a file and hooks are shared from the main checkout) and honours a
`core.hooksPath` the repository sets for itself. It prints the directory it used. Copying the
files by hand is not equivalent: in a worktree `.git/hooks` does not exist, and the copy goes
nowhere git will look.

It only writes to the repository you run it in. Run it somewhere that is not a checkout root, or
with `core.hooksPath` set in your global config, and it tells you what it skipped and why instead
of writing hooks into a repository or a shared directory you did not mean.

### What the hooks run

Each check prefers the app's own composer script and falls back to calling the binary directly.
`composer cs`, `composer phpstan`, `composer rector`, and `composer phpcpd` are the names it looks
for. Define them and the hook runs a check exactly the way CI does, so the two cannot drift apart
on flags. Leave them undefined and the hook keeps its own defaults, which is what every consumer
got before.

The hook disables Composer's process timeout for these calls, so a slow analysis on a large tree
does not die at 300 seconds with a Composer exception in place of the tool's own output.

### Telling the hooks how to reach artisan

The pre-push hook runs `artisan migrate:status`. By default it calls `php artisan`. If your app
runs artisan inside a container, put an `ARTISAN` array in a `.hooks-env.sh` at the project root
and the hook sources it:

```bash
# .hooks-env.sh
ARTISAN=(./vendor/bin/sail artisan)
```

It has to be an array, not a string, so the words survive the call. The file is optional and
usually belongs in `.gitignore`, since the right answer differs per developer.

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
