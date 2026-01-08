<?php

namespace QBTechnologies\LaravelConfig;

class ConfigHelper
{
    /**
     * Get the path to the PHP-CS-Fixer configuration file
     */
    public static function getPhpCsFixerConfigPath(): string
    {
        return __DIR__ . '/../config/.php-cs-fixer.php';
    }

    /**
     * Get the path to the PHPStan configuration file
     */
    public static function getPhpStanConfigPath(): string
    {
        return __DIR__ . '/../config/.phpstan.neon';
    }

    /**
     * Get the path to the Rector configuration file
     */
    public static function getRectorConfigPath(): string
    {
        return __DIR__ . '/../config/rector.php';
    }
}

