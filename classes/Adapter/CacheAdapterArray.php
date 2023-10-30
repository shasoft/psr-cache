<?php

namespace Shasoft\PsrCache\Adapter;

class CacheAdapterArray extends CacheAdapter
{
    // Данные
    protected array $data = [];
    // Получить значения (Если $has=true, то только проверить наличие значения. 
    // Т.е. вернуть либо false, либо true)
    public function get(array $keys, bool $has): array
    {
        $ret = [];
        foreach ($keys as $key) {
            $hasItem = array_key_exists($key, $this->data);
            if ($has) {
                $ret[$key] = $hasItem;
            } else {
                $ret[$key] = $this->data[$key] ?? false;
            }
        }
        return $ret;
    }
    // Удалить указанные значения
    public function delete(array $keys): bool
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->data)) {
                unset($this->data[$key]);
            }
        }
        return true;
    }
    // Удалить все значения
    public function clear(): bool
    {
        $this->data = [];
        return true;
    }
    // Сохранить элементы ['key1'=>'value11, 'key2'=>'value12, ...]
    // Возвращает список ключей успешно сохраненных элементов
    public function save(array $items): array
    {
        $ret = [];
        foreach ($items as $key => $data) {
            // Сохранить в хранилище
            $this->data[$key] = $data;
            // Добавить ключ в список успешно сохраненных
            $ret[] = $key;
        }
        return $ret;
    }
    // Получить все значения
    public function all(): array
    {
        return $this->data;
    }
}
