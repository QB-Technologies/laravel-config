# PHP Tool Configs

Shared PHP tool configurations (PHP-CS-Fixer, PHPStan, Rector) for company projects.

This package provides standardized configuration files that can be shared across all company projects, ensuring consistent code quality and formatting standards.

## Installation

### 1. Set up Bitbucket Package Repository

First, you need to configure Composer to use Bitbucket as a private package repository.

#### Option A: Using Bitbucket Packages (Recommended)

1. Go to your Bitbucket repository settings
2. Navigate to **Packages** section
3. Copy the repository URL (format: `https://api.bitbucket.org/2.0/repositories/{workspace}/{repo_slug}/packages`)

#### Option B: Using Satis (Self-hosted)

If you prefer to use Satis for hosting packages:

1. Set up a Satis instance
2. Configure it to pull from your Bitbucket repositories
3. Use the Satis URL as your repository URL

### 2. Configure Composer in Your Projects

Add the repository configuration to your project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://bitbucket.org/your-workspace/php-cs-fixer.git"
        }
    ],
    "require-dev": {
        "quboticlabs/php-tool-configs": "^1.0"
    }
}
```

**Note:** For Bitbucket, you may need to set up authentication. Add this to your `composer.json`:

```json
{
    "config": {
        "http-basic": {
            "bitbucket.org": {
                "username": "your-bitbucket-username",
                "password": "your-app-password"
            }
        }
    }
}
```

To create an app password in Bitbucket:
1. Go to Personal Settings → App Passwords
2. Create a new app password with read permissions
3. Use this password in the composer.json config

### 3. Install the Package

```bash
composer require --dev quboticlabs/php-tool-configs
```

## Usage

### PHP-CS-Fixer

Create a `.php-cs-fixer.php` file in your project root:

```php
<?php

use PhpCsFixer\Finder;

$baseConfig = require __DIR__ . '/vendor/quboticlabs/php-tool-configs/config/.php-cs-fixer.php';

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
    - ./vendor/quboticlabs/php-tool-configs/config/.phpstan.neon

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
    - ./vendor/quboticlabs/php-tool-configs/config/.phpstan.neon

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

$baseConfig = require __DIR__ . '/vendor/quboticlabs/php-tool-configs/config/rector.php';

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
5. Run `composer update quboticlabs/php-tool-configs` in your projects

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

Proprietary - Internal use only
