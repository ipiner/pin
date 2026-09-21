<?php

declare(strict_types=1);

namespace Pin\Cache;

use BadMethodCallException;
use Pin\Support\Json;

/**
 * Hash 缓存操作
 */
trait HashStoreHelper
{
    protected HashDriver $driver;

    /**
     * 默认过期时间（秒）
     */
    protected const int DEFAULT_TTL = 604800;

    protected int $defaultTtl = self::DEFAULT_TTL;

    /**
     * 转发驱动调用
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->driver->{$method}(...$parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement($key, $value = 1): bool|int
    {
        throw new BadMethodCallException(__METHOD__.' not implemented.');
    }

    /**
     * 删除整个 Hash
     */
    public function del(array|string $key): bool
    {
        return $key !== [] && $this->driver->del($key);
    }

    /**
     * {@inheritDoc}
     */
    public function flush(): bool
    {
        throw new BadMethodCallException(__METHOD__.' not implemented.');
    }

    /**
     * 写入永久缓存
     */
    public function forever($key, $value): bool
    {
        return $this->putMany([$key => $value], 0);
    }

    /**
     * 删除字段或整个 Hash
     *
     * @param  string|array  $key
     */
    public function forget($key): bool
    {
        if (is_array($key) || ! str_contains($key, ':')) {
            return $this->del($key);
        }

        $item = HashKey::parse($key);

        return $this->driver->hDel($item->key, $item->field) > 0;
    }

    /**
     * 获取单条缓存
     */
    public function get($key): mixed
    {
        $item = HashKey::parse($key);
        $value = $this->driver->hGet($item->key, $item->field);

        return $value === false ? null : $this->unserialize($value);
    }

    /**
     * 获取整个 Hash 的缓存
     */
    public function getAll(string $key): array
    {
        return array_map(
            $this->unserialize(...),
            $this->driver->hGetAll($key),
        );
    }

    /**
     * 获取驱动
     */
    public function getDriver(): HashDriver
    {
        return $this->driver;
    }

    /**
     * 设置驱动
     */
    protected function setDriver(HashDriver $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * {@inheritDoc}
     */
    public function getPrefix(): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function increment($key, $value = 1): bool|int
    {
        throw new BadMethodCallException(__METHOD__.' not implemented.');
    }

    /**
     * 批量获取同一 Hash 下的缓存
     */
    public function many(array $keys): array
    {
        if (! $keys) {
            return [];
        }

        [$key, $fields] = HashKey::parseMany($keys);
        $values = $this->driver->hMGet($key, $fields);

        return array_combine($keys, array_map(
            fn ($value) => $value === false ? null : $this->unserialize($value),
            $values,
        ));
    }

    /**
     * 写入单条缓存
     */
    public function put($key, $value, $seconds = null): bool
    {
        return $this->putMany([$key => $value], $seconds);
    }

    /**
     * 批量写入同一 Hash 下的缓存
     */
    public function putMany(array $values, $seconds = null): bool
    {
        if (! $values) {
            return true;
        }

        [$key, $fields] = HashKey::parseMany(array_keys($values));
        $data = array_combine($fields, array_map($this->serialize(...), $values));

        return $this->driver->hMSet($key, $data) && $this->expire($key, $seconds);
    }

    /**
     * 更新整个 Hash 的过期时间
     */
    public function touch($key, $seconds): bool
    {
        return $this->driver->expire(HashKey::parse($key)->key, $seconds);
    }

    /**
     * 设置 Hash 过期时间
     */
    protected function expire(string $key, ?int $seconds): bool
    {
        $seconds = $this->getTTL($seconds);
        $ttl = $this->driver->ttl($key);

        if ($ttl === -2) {
            return false;
        }

        if ($seconds === 0) {
            return $ttl === -1 || $this->driver->persist($key);
        }

        return $ttl !== -1 || $this->driver->expire($key, $seconds);
    }

    /**
     * 获取过期时间
     */
    protected function getTTL(?int $seconds = null): int
    {
        return $seconds ?? $this->defaultTtl;
    }

    /**
     * 序列化缓存值
     */
    protected function serialize(mixed $value): string
    {
        return Json::encode($value);
    }

    /**
     * 设置默认过期时间
     */
    protected function setDefaultTTL(?int $seconds): void
    {
        $this->defaultTtl = $seconds ?? static::DEFAULT_TTL;
    }

    /**
     * 反序列化缓存值
     */
    protected function unserialize(string $value): mixed
    {
        return Json::decode($value);
    }
}
