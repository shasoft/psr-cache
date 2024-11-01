<?php

namespace Shasoft\PsrCache\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Shasoft\Ci\SettingEnvironment;
use Shasoft\PsrCache\CacheItemPool;

abstract class Base extends TestCase
{
    protected ?CacheItemPool $pool = null;
    //
    protected array $keys = ['a1', 'b2', 'c3', 'd4'];
    protected array $values = ['#1', '#2', '#3', '#4'];
    // Настройка среды окружения теста
    public static function settingEnvironment(SettingEnvironment $setting): void
    {
        // Установить тестовый домен
        $setting->setWebServer(
            __DIR__ . '/../../test-site',
            [$setting->config()->getOrFail('testDomain')]
        );
    }
    // Создать объект КЭШа
    abstract protected function getPool(): CacheItemPool;
    // 
    public function setUp(): void
    {
        parent::setUp();
        // Создать
        $this->pool = $this->getPool();
    }
    //
    public function tearDown(): void
    {
        // Уничтожить
        $this->pool->clear();
        $this->pool = null;
        parent::tearDown();
    }
    //
    public function testBase(): void
    {
        self::assertFalse($this->pool->hasItem($this->keys[0]));

        $item = $this->pool->getItem($this->keys[0]);
        self::assertFalse($item->isHit());
        $item->set($this->values[0]);
        $this->pool->save($item);

        self::assertTrue($this->pool->hasItem($this->keys[0]));

        self::assertEquals($item->getKey(), $this->keys[0]);

        $item2 = $this->pool->getItem($this->keys[0]);
        self::assertTrue($item2->isHit());
        self::assertEquals($this->values[0], $item2->get());

        $this->pool->deleteItem($this->keys[0]);

        self::assertFalse($this->pool->hasItem($this->keys[0]));

        $this->pool->saveDeferred($item);
        $this->pool->commit();

        self::assertTrue($this->pool->hasItem($this->keys[0]));

        $this->pool->deleteItems($this->keys);

        self::assertFalse($this->pool->hasItem($this->keys[0]));
    }

    //
    public function testExpiration(): void
    {
        self::assertFalse($this->pool->hasItem($this->keys[0]));

        $item = $this->pool->getItem($this->keys[0]);
        self::assertFalse($item->isHit());
        $item->set($this->values[0])->expiresAfter(3);
        $this->pool->save($item);

        self::assertTrue($this->pool->hasItem($this->keys[0]));

        $item2 = $this->pool->getItem($this->keys[0]);
        self::assertTrue($item2->isHit());
        self::assertEquals($this->values[0], $item2->get());

        sleep(4);

        $item3 = $this->pool->getItem($this->keys[0]);
        self::assertFalse($item3->isHit());
    }
}
