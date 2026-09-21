<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Pin\Exceptions\Exception;

trait TreeLevel
{
    /**
     * 校验节点及子树层级
     */
    protected function ensureLevelValid(): void
    {
        $maxLevel = $this->maxTreeLevel();

        if ($maxLevel === 0) {
            return;
        }

        if ($this->level > $maxLevel) {
            $this->levelExceeded($maxLevel);
        }

        if ($this->exists) {
            $this->ensureSubtreeLevelValid($maxLevel);
        }
    }

    /**
     * 校验移动子树后的最大层级
     */
    protected function ensureSubtreeLevelValid(int $maxLevel): void
    {
        $oldPath = $this->getRawOriginal('path');
        $levelDelta = $this->level - $this->pathLevel($oldPath);

        if ($levelDelta <= 0) {
            return;
        }

        $maxSubtreeLevel = static::where(
            'path',
            'like',
            $oldPath.'/%'
        )
            ->max('level');

        if ($maxSubtreeLevel && $maxSubtreeLevel + $levelDelta > $maxLevel) {
            $this->levelExceeded($maxLevel);
        }
    }

    /**
     * 获取允许的最大层级
     */
    protected function maxTreeLevel(): int
    {
        return (int) config('pin.tree.max_level', 0);
    }

    /**
     * 抛出层级超限异常
     */
    public function levelExceeded(int $maxLevel): never
    {
        throw new Exception("层级不能大于{$maxLevel}", 422)
            ->withStatusCode(422);
    }

    /**
     * 根据路径计算节点层级
     */
    protected function pathLevel(?string $path = null): int
    {
        return substr_count($path ?? $this->path, '/') + 1;
    }
}
