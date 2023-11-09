<?php

namespace Shasoft\PsrCache\Adapter;

use Shasoft\Filesystem\File;
use Shasoft\Filesystem\Filesystem;

class CacheAdapterFilesystem extends CacheAdapter
{
    // Конструктор
    public function __construct(protected string $basepath)
    {
    }
    // Получить имя файла
    protected function _filepath(string $key): string
    {
        // Вычислить ХЕШ ключа
        $key_hash = md5($key);
        // Получить имя файла в хранилище
        return $this->basepath . '/' . substr($key_hash, 0, 2) . '/' . $key_hash . '.ser';
    }
    // Получить значения (Если $has=true, то только проверить наличие значения. 
    // Т.е. вернуть либо false, либо true)
    public function get(array $keys, bool $has): array
    {
        $ret = [];
        foreach ($keys as $key) {
            $filepath = $this->_filepath($key);
            $hasItem = file_exists($filepath);
            if ($has) {
                $ret[$key] = $hasItem;
            } else {
                $ret[$key] = ($hasItem ? File::load($filepath) : false);
            }
        }
        return $ret;
    }
    // Удалить указанные значения
    public function delete(array $keys): bool
    {
        foreach ($keys as $key) {
            $filepath = $this->_filepath($key);
            if (file_exists($filepath)) {
                @unlink($filepath);
            }
        }
        return true;
    }
    // Удалить все значения
    public function clear(): bool
    {
        return Filesystem::rmdir($this->basepath);
    }
    // Сохранить элементы ['key1'=>'value11, 'key2'=>'value12, ...]
    // Возвращает список ключей успешно сохраненных элементов
    public function save(array $items): array
    {
        $ret = [];
        foreach ($items as $key => $data) {
            $filepath = $this->_filepath($key);
            // Сохранить в хранилище
            File::save($filepath, $data);
            // Добавить ключ в список успешно сохраненных
            $ret[] = $key;
        }
        return $ret;
    }
}
