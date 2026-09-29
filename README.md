# PHP Tool Configs

> 🎯 **Standardized PHP code quality configurations** for PHP-CS-Fixer, PHPStan, and Rector. Share consistent coding standards across all your projects with a single Composer package.

This package provides battle-tested, production-ready configuration files that can be shared across all your PHP projects, ensuring consistent code quality, formatting standards, and static analysis rules. Stop duplicating configuration files—maintain them in one place and reuse everywhere.

## ✨ Features

- 🔧 **Pre-configured PHP-CS-Fixer** rules for consistent code formatting
- 🔍 **PHPStan** configuration with sensible defaults for static analysis
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

## 📦 Installation

### Step 1: Install the Package

```bash
composer require --dev qb-technologies/laravel-config
```

### Step 2: Install Git Hooks (Optional but Recommended)

Install the pre-commit and pre-push hooks:

```bash
cp vendor/qb-technologies/laravel-config/hooks/pre-commit .git/hooks/pre-commit
cp vendor/qb-technologies/laravel-config/hooks/pre-push .git/hooks/pre-push
chmod +x .git/hooks/pre-commit .git/hooks/pre-push
```

Or use the included installer:

```bash
php vendor/bin/install-hooks
```

### Step 3: Set up Laravel Boost in the app (once per machine)

From 1.2.0 this package requires [Laravel Boost](https://laravel.com/docs/boost), so an app with `qb-technologies/laravel-config` in `require-dev` gets Boost on `composer install` or `composer update`. Do not add `laravel/boost` to the app's own `composer.json`. `composer install --no-dev` skips this package, so Boost is not installed in production.

Boost requires `illuminate/console|contracts|routing|support` at `^11.45.3|^12.41.1|^13.0`. An app on an older Laravel cannot install this release without upgrading the framework first.

Run the installer **interactively**. It cannot be scripted, because the step that pulls in the shared rules is a prompt:

```bash
./vendor/bin/sail artisan boost:install
```

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
