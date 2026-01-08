<?php

use Rector\Config\RectorConfig;

/**
 * Base Rector configuration
 * 
 * To use in your project, create a rector.php file in your project root:
 * 
 * <?php
 * 
 * use Rector\Config\RectorConfig;
 * use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
 * use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
 * 
 * $baseConfig = require __DIR__ . '/vendor/qb-technologies/laravel-config/config/rector.php';
 * 
 * return $baseConfig
 *     ->withPaths([
 *         __DIR__ . '/app',
 *         __DIR__ . '/domain',
 *         __DIR__ . '/routes',
 *         __DIR__ . '/tests',
 *     ])
 *     ->withSkip([
 *         RenamePropertyToMatchTypeRector::class => [
 *             // Add your project-specific skips here
 *         ],
 *     ]);
 */

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        naming: true,
        privatization: true,
        typeDeclarations: true,
    );

