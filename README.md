# shasoft/psr-cache
Класс для работы с КЭШем на основе [PSR-16](https://www.php-fig.org/psr/psr-16/)

Пакет реализует два класса
1. **Shasoft\PsrCache\CacheItemPool** implements **Psr\Cache\CacheItemInterface** - класс объекта работы с КЭШем
2. **Shasoft\PsrCache\PsrCache** implements **Psr\Cache\CacheItemInterface** - класс элемента КЭШа

```php
    // Создать объект для работы с КЭШем
    $cache = new Shasoft\PsrCache\CacheItemPool(
        new Shasoft\PsrCache\Adapter\CacheAdapter()
    );
    // Получить элемент КЭШа
    $itemCache = cache->getItem('myKey');
    // Если элемент не найден в КЖШе
    if( !$itemCache->isHit() ) {
        // то установить значение
        $itemCache->set('valueCacheItem');
        // и сохранить в КЭШ
        $cache->save($itemCache);
    }
    // Вывести значение
    echo $itemCache->get();
 ```
 В качестве параметра конструктора класс объекта работы с КЭШем принимает объект адаптера.
 На текущий момент доступны следующие адаптеры
 1. **Shasoft\PsrCache\Adapter\CacheAdapterArray** - КЭШирование в php массиве 

Для создание своего адаптера необходимо создать свой класс наследовав его от **Shasoft\PsrCache\Adapter\CacheAdapter** и определить его абстрактные методы:

```php
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
```