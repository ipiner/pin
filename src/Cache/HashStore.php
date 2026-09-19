<?php

declare(strict_types=1);

namespace Pin\Cache;

use Illuminate\Contracts\Cache\Store;

/**
 * Hash 缓存存储。
 */
interface HashStore extends Store
{
    /**
     * 删除整个 Hash。
     */
    public function del(array|string $key): bool;

    /**
     * 获取全部字段值。
     *
     * @return array<string, mixed>
     */
    public function getAll(string $key): array;
}
