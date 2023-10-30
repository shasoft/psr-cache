<?php

namespace Shasoft\PsrCache;

use Psr\Cache\CacheItemInterface;


class CacheItem implements CacheItemInterface
{
    // Значение взято из КЭШа
    protected bool $isHit;
    // Значение
    protected mixed $value = null;
    // Время жизни (т.е. время до которого значение валидно)
    protected ?\DateTimeInterface $expiration = null;
    // Конструктор
    public function __construct(protected string $key, array|false $data = false)
    {
        // Данные взяты из КЭШа?
        $this->isHit = ($data !== false);
        // Элемент взят из КЭШа?
        if ($this->isHit) {
            // Время жизни указано?
            if ($data[1] >= 0) {
                $this->expiration = new \DateTime();
                $this->expiration->setTimestamp($data[1]);
            }
            // Проверим: А может КЭШ просрочен?
            if (null !== $this->expiration && new \DateTime() > $this->expiration) {
                // Если просрочен, то промах мимо КЕШа
                $this->isHit = false;
            } else {
                // Установить значение (только если КЭШ не просрочен)
                $this->value = $data[0];
            }
        }
    }
    /**
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     *   The key string for this cache item.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     *
     * The value returned must be identical to the value originally stored by set().
     *
     * If isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return mixed
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function get(): mixed
    {
        return $this->value;
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return bool
     *   True if the request resulted in a cache hit. False otherwise.
     */
    public function isHit(): bool
    {
        return $this->isHit;
    }

    /**
     * Sets the value represented by this cache item.
     *
     * The $value argument may be any item that can be serialized by PHP,
     * although the method of serialization is left up to the Implementing
     * Library.
     *
     * @param mixed $value
     *   The serializable value to be stored.
     *
     * @return static
     *   The invoked object.
     */
    public function set(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Sets the absolute expiration time for this cache item.
     *
     * @param \DateTimeInterface|null $expiration
     *   The point in time after which the item MUST be considered expired.
     *   If null is passed explicitly, a default value MAY be used. If none is set,
     *   the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        $this->expiration = ($expiration instanceof \DateTimeInterface) ? $expiration : null;
        return $this;
    }

    /**
     * Sets the relative expiration time for this cache item.
     *
     * @param int|\DateInterval|null $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAfter(int|\DateInterval|null $time): static
    {
        if ($time instanceof \DateInterval) {
            $expiration = new \DateTime();
            $expiration->add($time);
            $this->expiration = $expiration;
        } elseif (is_numeric($time)) {
            $expires = new \DateTime('now +' . $time . ' seconds');
            $this->expiration = $expires;
        } else {
            $this->expiration = null;
        }
        return $this;
    }
    // Получить данные для сохранения
    public function getData(): array
    {
        return [$this->value, is_null($this->expiration) ? -1 : $this->expiration->getTimestamp()];
    }
}
