# Setup Guide

## Initial Setup

### 1. Update Package Name

Before using this package, update the package name in `composer.json`:

```json
{
    "name": "quboticlabs/php-tool-configs"
}
```

The package name is set to `quboticlabs/php-tool-configs`.

### 2. Update Namespace

Update the namespace in `src/ConfigHelper.php`:

```php
namespace Quboticlabs\PhpToolConfigs;
```

The namespace is set to `Quboticlabs\PhpToolConfigs`.

### 3. Initialize Git Repository

```bash
git init
git add .
git commit -m "Initial commit: Shared PHP tool configurations"
```

### 4. Push to Bitbucket

1. Create a new repository in Bitbucket
2. Add the remote:
   ```bash
   git remote add origin https://bitbucket.org/your-workspace/php-tool-configs.git
   git push -u origin main
   ```

### 5. Create Initial Tag

```bash
git tag -a v1.0.0 -m "Initial release"
git push origin v1.0.0
```

## Using in Projects

### Step 1: Add Repository to Project's composer.json

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://bitbucket.org/your-workspace/php-tool-configs.git"
        }
    ]
}
```

### Step 2: Configure Authentication (if needed)

If your repository is private, add authentication:

```json
{
    "config": {
        "http-basic": {
            "bitbucket.org": {
                "username": "your-username",
                "password": "your-app-password"
            }
        }
    }
}
```

### Step 3: Install the Package

```bash
composer require --dev quboticlabs/php-tool-configs
```

### Step 4: Create Configuration Files

Copy the example files from `examples/` directory to your project root and customize as needed:

```bash
cp vendor/quboticlabs/php-tool-configs/examples/.php-cs-fixer.php.example .php-cs-fixer.php
cp vendor/quboticlabs/php-tool-configs/examples/.phpstan.neon.example .phpstan.neon
cp vendor/quboticlabs/php-tool-configs/examples/rector.php.example rector.php
```

Then edit these files to match your project structure.

## Updating Configurations

When you update the shared configurations:

1. Make changes in this repository
2. Commit and push:
   ```bash
   git add .
   git commit -m "Update: Description of changes"
   git push
   ```
3. Create a new version tag:
   ```bash
   git tag -a v1.0.1 -m "Update: Description of changes"
   git push origin v1.0.1
   ```
4. Update projects:
   ```bash
   composer update quboticlabs/php-tool-configs
   ```

## Versioning Strategy

- **PATCH** (1.0.x): Bug fixes, minor rule adjustments
- **MINOR** (1.x.0): New rules, new features, non-breaking changes
- **MAJOR** (x.0.0): Breaking changes, removed rules, major restructuring

