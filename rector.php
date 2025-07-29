<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/rector.php'
    ])
    // uncomment to reach your current PHP version
    ->withSets([
        SetList::PHP_82,
        LevelSetList::UP_TO_PHP_82
    ])
    ->withTypeCoverageLevel(0);
