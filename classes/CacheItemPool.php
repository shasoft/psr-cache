<?php

namespace Shasoft\PsrCache;

use Shasoft\PsrCache\CacheItem;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Shasoft\PsrCache\Adapter\CacheAdapter;


class CacheItemPool implements CacheItemPoolInterface
{
    // Конструктор
    public function __construct(protected CacheAdapter $adapter)
    {
    }
    // Получить элементы КЕШа
    protected function _getItems(array $keys): array
    {
        $rcData = $this->adapter->get($keys, false);
        $ret = [];
        foreach ($rcData as $key => $data) {
            // Создать элемент КЭШа
            $item = new CacheItem($key, $data);
            // Если КЭШ (внезапно!) оказался просрочен
            if ($data !== false && !$item->isHit()) {
                // Удалить значение
                $this->adapter->delete([$key]);
            }
            $ret[$key] = $item;
        }
        return $ret;
    }
    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return CacheItemInterface
     *   The corresponding Cache Item.
     */
    public function getItem(string $key): CacheItemInterface
    {
        // Получить значение
        $rc = $this->_getItems([$key]);
        // Вернуть элемент
        return $rc[$key];
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param string[] $keys
     *   An indexed array of keys of items to retrieve.
     *
     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return iterable
     *   An iterable collection of Cache Items keyed by the cache keys of
     *   each item. A Cache item will be returned for each key, even if that
     *   key is not found. However, if no keys are specified then an empty
     *   traversable MUST be returned instead.
     */
    public function getItems(array $keys = []): iterable
    {
        // Получить элементы КЕШа
        return $this->_getItems($keys);
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key
     *   The key for which to check existence.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if item exists in the cache, false otherwise.
     */
    public function hasItem(string $key): bool
    {
        // Получить значение
        $rc = $this->adapter->get([$key], true);
        return $rc[$key] !== false;
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     *   True if the pool was successfully cleared. False if there was an error.
     */
    public function clear(): bool
    {
        return $this->adapter->clear();
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key
     *   The key to delete.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the item was successfully removed. False if there was an error.
     */
    public function deleteItem(string $key): bool
    {
        // Удалить значение
        return $this->adapter->delete([$key]);
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param string[] $keys
     *   An array of keys that should be removed from the pool.
     *
     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the items were successfully removed. False if there was an error.
     */
    public function deleteItems(array $keys): bool
    {
        // Удалить значениЯ
        return $this->adapter->delete($keys);
    }

    /**
     * Persists a cache item immediately.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   True if the item was successfully persisted. False if there was an error.
     */
    public function save(CacheItemInterface $item): bool
    {
        // Сохранить элемент
        $keys = $this->adapter->save(array_map(function (CacheItem $item) {
            return $item->getData();
        }, [$item->getKey() => $item]));
        // Все элементы (1 шт.) сохранены успешно?
        return count($keys) == 1;
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    protected array $itemsDeferred = [];
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->itemsDeferred[$item->getKey()] = $item;
        return true;
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit(): bool
    {
        //
        $keys = $this->adapter->save(array_map(function (CacheItem $item) {
            return $item->getData();
        }, $this->itemsDeferred));
        if (count($keys) == count($this->itemsDeferred)) {
            $this->itemsDeferred = [];
            // Всё успешно сохранено
            $ret = true;
        } else {
            // Если хоть что-то было успешно сохранено
            if (count($keys) > 0) {
                foreach ($keys as $key) {
                    unset($this->itemsDeferred[$key]);
                }
            }
            // Сохранено с ошибками (или вообще ничего не было сохранено)
            $ret = false;
        }
        return $ret;
    }
}
