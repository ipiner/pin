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
     *
     * @return Model|null
     */
    public function get(int $id);

    /**
     * 获取全量数据
     *
     * @return Collection
     *
     * 返回约定：
     * - 永远返回 Collection（即使为空）
     * - keyBy('id') 结构
     */
    public function getAll(): Collection;

    /**
     * 设置缓存 TTL
     */
    public function ttl(int $seconds): static;
}
