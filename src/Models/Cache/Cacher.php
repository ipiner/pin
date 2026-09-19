<?php

declare(strict_types=1);

namespace Pin\Models\Cache;

use Illuminate\Database\Eloquent\Collection;
use Pin\Models\Model;

/**
 * 缓存接口
 */
interface Cacher
{
    /**
     * 删除缓存
     */
    public function forget(string $key): bool;

    /**
     * 获取单条模型
     */
    public function get(int $id): ?Model;

    /**
     * 获取全量数据。
     *
     * @return Collection<int, Model>
     */
    public function getAll(): Collection;

    /**
     * 设置缓存有效期（秒）
     */
    public function ttl(int $seconds): static;
}
