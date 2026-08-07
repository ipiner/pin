<?php

declare(strict_types=1);

namespace Pin\Cache;

use BadMethodCallException;
use Pin\Support\Json;

/**
 * HashStoreHelper
 *
 * 提供 Redis Hash 缓存的通用操作：
 * - key 解析（HashKey）
 * - JSON 序列化/反序列化
 * - 批量读写
 * - TTL 控制（惰性 + 概率触发）
 * - 逻辑删除与物理删除区分
 */
trait HashStoreHelper
{
    protected HashDriver $driver;

    /**
     * 缓存过期时间（秒）
     *
     * 默认：7 天
     */
    protected int $defaultTTL = 604800;

    /**
     * 将未知方法代理到底层 Hash Driver
     */
    public function __call(string $method, mixed $parameters): mixed
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
     * 删除整个 key
     */
    public function del(array|string $key): bool
    {
        return $this->driver->del($key);
    }

    /**
     * {@inheritDoc}
     */
    public function flush(): bool
    {
        return throw new BadMethodCallException(__METHOD__.' not implemented.');
    }

    /**
     * 永久缓存（TTL=0）
     */
    public function forever($key, $value): bool
    {
        return $this->putMany([$key => $value], 0);
    }

    /**
     * 删除缓存
     *
     * 如果 `$key` 包括“:"：则删除 Redis Hash 结构中对应 hDel（删除字段）
     * 否则会直接删除整个缓存键（包括所有 field）
     *
     * - users-all -> 删除整个缓存
     * - users: -> 删除 users 中的字段 1
     *
     * @param  string|array  $key  缓存键（完整 key）
     */
    public function forget($key): bool
    {
        if (! str_contains($key, ':') || is_array($key)) {
            return $this->del($key);
        }

        $item = HashKey::parse($key);

        return $this->driver->hDel($item->key, $item->field) > 0;
    }

    /**
     * 获取单条缓存
     *
     * key 形式：users:1
     */
    public function get($key)
    {
        $item = HashKey::parse($key);
        $value = $this->driver->hGet($item->key, $item->field);

        return $value === false ? null : $this->unserialize($value);
    }

    /**
     * 获取所有缓存数据
     */
    public function getAll(string $key): array
    {
        return array_map(
            fn ($v) => $this->unserialize($v),
            $this->driver->hGetAll($key)
        );
    }

    /**
     * 获取 Driver
     */
    public function getDriver(): HashDriver
    {
        return $this->driver;
    }

    /**
     * 设置 Driver
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
     * 批量获取缓存
     *
     * 注意：
     * - 所有 keys 必须属于同一个 hash key
     * - 返回值顺序与输入一致
     */
    public function many(array $keys): array
    {
        [$key, $fields] = HashKey::parseMany($keys);
        $values = $this->driver->hMGet($key, $fields);

        return array_map(
            fn ($v) => $v === false ? null : $this->unserialize($v),
            $values
        );
    }

    /**
     * 写入单条缓存
     */
    public function put($key, $value, $seconds = null): bool
    {
        return $this->putMany([$key => $value], $seconds);
    }

    /**
     * 批量写入缓存
     */
    public function putMany(array $values, $seconds = null): bool
    {
        $key = '';
        $data = [];

        foreach ($values as $k => $v) {
            $item = HashKey::parse($k);
            $key = $item->key;
            $data[$item->field] = $this->serialize($v);
        }

        return tap(
            $this->driver->hMSet($key, $data),
            fn () => $this->expire($key, $seconds),
        );
    }

    /**
     * 刷新 TTL（延长生命周期）
     */
    public function touch($key, $seconds): bool
    {
        return $this->expire($key, $seconds, true);
    }

    /**
     * 设置过期时间
     *
     * 惰性 + 概率执行
     */
    protected function expire(string $rawKey, ?int $seconds, ?bool $run = null): bool
    {
        // 默认 5% 概率执行 expire
        $run ??= random_int(1, 100) <= 5;

        if (! $run) {
            return false;
        }

        $key = HashKey::parse($rawKey)->key;
        // -1 = 已有 TTL，避免重复设置
        if ($this->driver->ttl($key) !== -1) {
            return true;
        }

        return $this->driver->expire($key, $seconds ?: $this->getTTL($seconds));
    }

    /**
     * 获取TTL
     */
    protected function getTTL(?int $seconds = null): int
    {
        return $seconds ?: $this->defaultTTL;
    }

    /**
     * 序列化存储值
     */
    protected function serialize(mixed $value): string
    {
        return Json::encode($value);
    }

    /**
     * 设置默认TTL
     */
    protected function setDefaultTTL(?int $seconds): void
    {
        $this->defaultTTL = $seconds ?: 604800;
    }

    /**
     * 反序列化读取值
     */
    protected function unserialize(string $value): mixed
    {
        return Json::decode($value);
    }
}
