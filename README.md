# PHP Tool Configs

Shared PHP tool configurations (PHP-CS-Fixer, PHPStan, Rector) for company projects.

This package provides standardized configuration files that can be shared across all company projects, ensuring consistent code quality and formatting standards.

## Installation

### Step 1: Add Repository to composer.json

Add the Bitbucket repository to your project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@bitbucket.org:quboticlabs/php-cs-fixer.git"
        }
    ],
    "require-dev": {
        "quboticlabs/php-cs-fixer": "^1.0"
    }
}
```

**Note:** This uses SSH authentication. Ensure you have SSH keys configured with Bitbucket. Alternatively, you can use HTTPS with API token authentication:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://bitbucket.org/quboticlabs/php-cs-fixer.git"
        }
    ],
    "config": {
        "http-basic": {
            "bitbucket.org": {
                "username": "x-bitbucket-api-token-auth",
                "password": "YOUR_API_TOKEN"
            }
        }
    },
    "require-dev": {
        "quboticlabs/php-cs-fixer": "^1.0"
    }
}
```

To create a Bitbucket API token:
1. Go to Bitbucket → Personal Settings → Security → API tokens
2. Click **Create API token with scopes**
3. Name it (e.g., "Composer Package Access")
4. Select **Repositories → Read** permission
5. Click **Create** and copy the token

### Step 2: Install the Package

```bash
composer require --dev quboticlabs/php-cs-fixer
```

### Step 3: Install Git Hooks (Optional but Recommended)

Install the pre-commit and pre-push hooks:

```bash
cp vendor/quboticlabs/php-cs-fixer/hooks/pre-commit .git/hooks/pre-commit
cp vendor/quboticlabs/php-cs-fixer/hooks/pre-push .git/hooks/pre-push
chmod +x .git/hooks/pre-commit .git/hooks/pre-push
```

## Usage

### PHP-CS-Fixer

Create a `.php-cs-fixer.php` file in your project root:

```php
<?php

use PhpCsFixer\Finder;

$baseConfig = require __DIR__ . '/vendor/quboticlabs/php-cs-fixer/config/.php-cs-fixer.php';

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
    - ./vendor/quboticlabs/php-cs-fixer/config/.phpstan.neon

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
    - ./vendor/quboticlabs/php-cs-fixer/config/.phpstan.neon

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

$baseConfig = require __DIR__ . '/vendor/quboticlabs/php-cs-fixer/config/rector.php';

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
5. Run `composer update quboticlabs/php-cs-fixer` in your projects

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
