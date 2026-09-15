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

This package requires Larastan, and Larastan requires `illuminate/*`. So this package has a say in which Laravel major a consuming app can resolve to. Larastan 3.x allows `^11.44.2 || ^12.4.1`.

When a new Laravel major lands, this package needs a release with a widened Larastan constraint before consuming apps can upgrade. Widening to `^3.4 || ^4.0` once Larastan 4 exists is routine maintenance. It's much less pleasant to work out under upgrade pressure, so it's worth doing early.

### A note for anyone changing these dependencies

Consuming apps include `vendor/larastan/larastan/extension.neon` directly from their own `.phpstan.neon`. That path is effectively part of this package's contract even though the app never declares Larastan itself. Swapping Larastan for something else means updating every consumer's `.phpstan.neon` in the same release.

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
up there, so a normal setup needs no manual step.

The installer asks git where the hooks belong rather than assuming `.git/hooks`, so it works
in a worktree (where `.git` is a file and hooks are shared from the main checkout) and honours a
`core.hooksPath` the repository sets for itself. It prints the directory it used. Copying the
files by hand is not equivalent: in a worktree `.git/hooks` does not exist, and the copy goes
nowhere git will look.

It only writes to the repository you run it in. Run it somewhere that is not a checkout root, or
with `core.hooksPath` set in your global config, and it tells you what it skipped and why instead
of writing hooks into a repository or a shared directory you did not mean.

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

Create a `.phpstan.neon` file in your project root:

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
    - Spatie\Ray\PHPStan\RemainingRayCallRule
```

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
