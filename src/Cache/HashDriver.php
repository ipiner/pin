<?php

declare(strict_types=1);

namespace Pin\Cache;

/**
 * Hash 存储驱动
 */
interface HashDriver
{
    /**
     * 删除整个 Hash
     */
    public function del(array|string $key): bool;

    /**
     * 设置过期时间（秒）
     */
    public function expire(string $key, int $seconds): bool;

    /**
     * 删除字段
     */
    public function hDel(string $key, string ...$fields): int;

    /**
     * 获取字段值
     */
    public function hGet(string $key, string $field): mixed;

    /**
     * 获取全部字段
     *
     * @return array<string, string>
     */
    public function hGetAll(string $key): array;

    /**
     * 按输入顺序批量获取字段值
     *
     * @param  array<int, string>  $fields
     * @return array<int, string|false>
     */
    public function hMGet(string $key, array $fields): array;

    /**
     * 批量写入字段
     *
     * @param  array<string, string>  $data
     */
    public function hMSet(string $key, array $data): bool;

    /**
     * 移除过期时间
     */
    public function persist(string $key): bool;

    /**
     * 获取剩余秒数，-1 表示永久，-2 表示不存在
     */
    public function ttl(string $key): int;
}
