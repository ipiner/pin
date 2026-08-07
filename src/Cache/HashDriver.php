<?php

declare(strict_types=1);

namespace Pin\Cache;

/**
 * Hash 存储底层驱动契约。
 */
interface HashDriver
{
    /**
     * 删除整个 key（物理删除）
     */
    public function del(array|string $key): bool;

    /**
     * 设置 key 过期时间（秒）
     */
    public function expire(string $key, int $seconds): bool;

    /**
     * 删除一个或多个 field
     */
    public function hDel(string $key, string ...$fields): int;

    /**
     * 获取单个 field
     */
    public function hGet(string $key, string $field): mixed;

    /**
     * 获取整个 hash
     *
     * @return array<string, string>
     */
    public function hGetAll(string $key): array;

    /**
     * 批量获取 field
     *
     * @param  array<int, string>  $fields
     * @return array<int, string|false>
     */
    public function hMGet(string $key, array $fields): array;

    /**
     * 批量设置 field
     *
     * @param  array<string, string>  $data
     */
    public function hMSet(string $key, array $data): bool;

    /**
     * 获取 TTL
     *
     * - -2 => key不存在
     * - -1 => 无过期时间
     * - 大于0 => 剩余秒数
     */
    public function ttl(string $key): int;
}
