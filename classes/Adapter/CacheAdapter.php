<?php

namespace Shasoft\PsrCache\Adapter;

abstract class CacheAdapter
{
    // Получить значения (Если $has=true, то только проверить наличие значения. 
    // Т.е. вернуть либо false, либо true)
    abstract public function get(array $keys, bool $has): array;
    // Удалить указанные значения
    abstract public function delete(array $keys): bool;
    // Удалить все значения
    abstract public function clear(): bool;
    // Сохранить элементы ['key1'=>'value11, 'key2'=>'value12, ...]
    // Возвращает список ключей успешно сохраненных элементов
    abstract public function save(array $items): array;
}
