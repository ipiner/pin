<?php

declare(strict_types=1);

namespace Pin\Cache;

use Illuminate\Contracts\Cache\Store;

/**
 * 支持 Hash 字段级读取和整表删除的缓存 Store。
 */
interface HashStore extends Store
{
    /**
     * 物理删除缓存 key（整表删除）
     *
     * @param  array|string  $key  缓存键
     */
    public function del(array|string $key): bool;

    /**
     * 获取整个 hash 的所有字段与值
     *
     * @return array<string, mixed>
     */
    public function getAll(string $key): array;
}
