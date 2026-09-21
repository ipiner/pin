<?php

declare(strict_types=1);

namespace Pin\Cache;

use Illuminate\Redis\Connections\PhpRedisConnection;

/**
 * PhpRedis Hash 命令适配器
 */
class RedisHashDriver implements HashDriver
{
    public function __construct(protected PhpRedisConnection $connection)
    {
    }

    /**
     * 转发 Redis 命令
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->connection->{$method}(...$parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function del(string|array $key): bool
    {
        return (bool) $this->connection->del($key);
    }

    /**
     * {@inheritDoc}
     */
    public function expire(string $key, int $seconds): bool
    {
        return (bool) $this->connection->expire($key, $seconds);
    }

    /**
     * {@inheritDoc}
     */
    public function hDel(string $key, string ...$fields): int
    {
        return $this->connection->hDel($key, ...$fields);
    }

    /**
     * {@inheritDoc}
     */
    public function hGet(string $key, string $field): mixed
    {
        return $this->connection->hGet($key, $field);
    }

    /**
     * {@inheritDoc}
     */
    public function hGetAll(string $key): array
    {
        return $this->connection->hGetAll($key);
    }

    /**
     * {@inheritDoc}
     */
    public function hMGet(string $key, array $fields): array
    {
        $values = $this->connection->command('hmget', [$key, $fields]);

        return array_map(fn ($field) => $values[$field], $fields);
    }

    /**
     * {@inheritDoc}
     */
    public function hMSet(string $key, array $data): bool
    {
        return (bool) $this->connection->hMSet($key, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function persist(string $key): bool
    {
        return (bool) $this->connection->persist($key);
    }

    /**
     * {@inheritDoc}
     */
    public function ttl(string $key): int
    {
        return $this->connection->ttl($key);
    }
}
