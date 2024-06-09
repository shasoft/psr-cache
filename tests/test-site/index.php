<?php

use Shasoft\Batch\BatchDebug;
use Shasoft\Batch\BatchConfig;
use Shasoft\Batch\BatchManager;
use Shasoft\Batch\Tests\CacheDebug;

require_once __DIR__ . '/../../vendor/autoload.php';

// Включить логирование
BatchDebug::log(true);

class CacheTest
{
    protected int $key = 2;
    // Конструктор
    public function __construct()
    {
    }
    static public function assertEquals($a, $b)
    {
    }
    public function run()
    {
        $cacheDebug = new CacheDebug;
        $cacheDebug->run(
            [
                function (CacheDebug $dbg) {
                    return $dbg->fns->fnPut0($this->key, 13);
                },
                function (CacheDebug $dbg) {
                    return $dbg->fns->fnGet0($this->key);
                },
                function (CacheDebug $dbg): void {
                    self::assertEquals($dbg->ret, 13);
                },
                function (CacheDebug $dbg) {
                    return $dbg->fns->fnPut0($this->key, 17);
                },
            ],
            true,
            true
        );
        s_dd($cacheDebug);
    }
}

try {
    (new CacheTest)->run();
} catch (\Throwable $th) {
    s_dd($th);
}
