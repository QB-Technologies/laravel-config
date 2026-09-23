# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-09-23

### Added
- Laravel Boost guideline at `resources/boost/guidelines/core.blade.php` so `php artisan boost:install` and `boost:update` publish QB coding rules into consuming apps

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

