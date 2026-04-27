<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
  ->withPaths([
    __DIR__ . '/src',
    __DIR__ . '/tests',
  ])
  ->withSets([
    LevelSetList::UP_TO_PHP_83,
    SetList::CODE_QUALITY,
    SetList::TYPE_DECLARATION,
    PHPUnitSetList::PHPUNIT_100,
  ])
  ->withImportNames(removeUnusedImports: true);
