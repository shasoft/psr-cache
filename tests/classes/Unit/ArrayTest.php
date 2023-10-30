<?php

namespace Shasoft\PsrCache\Tests\Unit;

use Shasoft\PsrCache\CacheItemPool;
use Shasoft\PsrCache\Tests\Unit\Base;
use Shasoft\PsrCache\Adapter\CacheAdapterArray;

class ArrayTest extends Base
{
    protected function getPool(): CacheItemPool
    {
        return new CacheItemPool(new CacheAdapterArray());
    }
}
