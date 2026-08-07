<?php

declare(strict_types=1);

namespace Pin\Cache;

/**
 * 进程内缓存（增强 ArrayStore）
 */
class ArrayStore extends \Illuminate\Cache\ArrayStore
{
    /**
     * 最大缓存数量
     */
    protected const MAX_ITEMS = 10000;

    /**
     * GC 批量回收数量
     *
     * 当缓存数量超过 MAX_ITEMS 时，不是只删除一个元素，而是一次性删除一批数据。
     */
    protected const GC_BATCH = 1000;

    /**
     * @param  int  $maxSize  最大缓存数量
     * @param  int  $gcBatch  GC 批量回收数量
     */
    public function __construct(
        protected int $maxSize = self::MAX_ITEMS,
        protected int $gcBatch = self::GC_BATCH
    ) {
        parent::__construct();
    }

    /**
     * 获取当前缓存中的所有数据
     *
     * @param  string|null  $prefix  key 前缀（如 "menus:"）
     * @return array<string, mixed> key => value 结构
     */
    public function getAll(?string $prefix = null): array
    {
        $result = [];
        foreach ($this->storage as $key => $item) {
            if ($prefix && ! str_starts_with($key, $prefix)) {
                continue;
            }

            if (! is_null($value = $this->get($key))) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * gc回收
     *
     * 删除最早写入的数据（近似 FIFO）
     */
    public function gc(?bool $run = null): void
    {
        $run ??= random_int(0, 100) < 5;

        if ($run && count($this->storage) > $this->maxSize) {
            $this->storage = array_slice(
                $this->storage,
                -($this->maxSize - $this->gcBatch),
                null,
                true
            );
        }
    }
}
