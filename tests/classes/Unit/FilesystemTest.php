<?php

namespace Shasoft\PsrCache\Tests\Unit;

use Shasoft\Filesystem\Path;
use Shasoft\PsrCache\CacheItemPool;
use Shasoft\PsrCache\Tests\Unit\Base;
use Shasoft\PsrCache\Adapter\CacheAdapterFilesystem;

class FilesystemTest extends Base
{
    protected function getPool(): CacheItemPool
    {
        return new CacheItemPool(new CacheAdapterFilesystem(Path::normalize(__DIR__ . '/../../~/CacheAdapterFilesystem')));
    }
}
