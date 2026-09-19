<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

/**
 * 树路径读写。
 */
trait TreePath
{
    /**
     * @var list<string>
     */
    protected $appends = ['paths'];

    /**
     * 根据父节点生成路径。
     *
     * @param  int  $pid  父节点 ID（0 表示根节点）
     */
    public static function buildPath(int $id, int $pid): string
    {
        if (! $pid) {
            return (string) $id;
        }

        return static::findOrFail($pid)->path.'/'.$id;
    }

    /**
     * 校验移动目标。
     */
    protected function ensureParentValid(): void
    {
        if (
            $this->pid
            && in_array($this->id, static::findOrFail($this->pid)->paths(), true)
        ) {
            throw new Exception('不能以自身或子节点作为父节点', 422)->withStatusCode(422);
        }
    }

    /**
     * 获取路径 ID 数组。
     */
    public function getPathsAttribute(): array
    {
        return $this->paths();
    }

    /**
     * 解析路径 ID。
     *
     * @return list<int>
     */
    public function paths(): array
    {
        return $this->path
            ? array_map('intval', explode('/', $this->path))
            : [];
    }

    /**
     * 更新后代节点的路径和层级。
     */
    protected static function relocateSubtree(string $oldPath, string $newPath): int
    {
        $items = static::descendantsOf($oldPath);
        $prefixLength = strlen($oldPath);

        foreach ($items as $item) {
            /** @var static $item */
            $item->path = $newPath.substr($item->path, $prefixLength);
            $item->level = $item->pathLevel();

            if (! $item->save()) {
                Errors::UpdateFailed->throw();
            }
        }

        return $items->count();
    }
}
