<?php

declare(strict_types=1);

namespace Pin\Cache;

use Illuminate\Cache\ArrayStore as BaseArrayStore;

/**
 * 进程内缓存存储
 */
class ArrayStore extends BaseArrayStore
{
    /**
     * 最大缓存数量
     */
    protected const int MAX_ITEMS = 10000;

    /**
     * 批量回收数量
     */
    protected const int GC_BATCH = 1000;

    /**
     * @param  int  $maxSize  最大缓存数量
     * @param  int  $gcBatch  GC 批量回收数量
     */
    public function __construct(
        protected int $maxSize = self::MAX_ITEMS,
        protected int $gcBatch = self::GC_BATCH,
    ) {
        parent::__construct();
    }

    /**
     * 获取未过期的缓存
     *
     * @return array<array-key, mixed>
     */
    public function getAll(?string $prefix = null): array
    {
        $result = [];

        foreach ($this->storage as $key => $item) {
            if ($prefix !== null && ! str_starts_with((string) $key, $prefix)) {
                continue;
            }

            if (($value = $this->get($key)) !== null) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * 回收最早写入的缓存
     */
    public function gc(?bool $run = null): void
    {
        if (count($this->storage) <= $this->maxSize) {
            return;
        }

        $run ??= random_int(1, 100) <= 5;

        if (! $run) {
            return;
        }

        $remaining = max(0, $this->maxSize - $this->gcBatch);
        $this->storage = $remaining
            ? array_slice($this->storage, -$remaining, null, true)
            : [];
    }
}
