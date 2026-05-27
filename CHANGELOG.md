# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

