<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

/**
 * TreePath（物化路径层）
 *
 * 对树结构中 path 的生成与解析
 */
trait TreePath
{
    /**
     * @var string[]
     */
    protected $appends = ['paths'];

    /**
     * 根据父节点生成当前节点的 Materialized Path（物化路径）
     *
     * @param  int  $id  当前节点 ID
     * @param  int  $pid  父节点 ID（0 表示根节点）
     * @return string 物化路径字符串
     */
    public static function buildPath(int $id, int $pid): string
    {
        return $pid
            ? static::find($pid)->path.'/'.$id
            : (string) $id;
    }

    /**
     * 追加祖先路径 ID 数组，方便前端树控件回显。
     */
    public function getPathsAttribute(): array
    {
        return $this->paths();
    }

    /**
     * 解析当前节点的路径为 ID 数组
     *
     * @return int[] 节点路径 ID 列表（从根到当前节点）
     */
    public function paths(): array
    {
        return $this->path
            ? array_map('intval', explode('/', $this->path))
            : [];
    }

    /**
     * 更新子树路径
     */
    protected static function relocateSubtree(string $oldPath, string $newPath): int
    {
        /**
         * 移动前：
         * id   path
         * 1    1
         * 2    1/2
         * 10   1/10
         * 11   1/10/11
         *
         * 执行：
         * relocateSubtree('1/10', '1/2')
         *
         * 移动后：
         * id   path
         * 1    1
         * 2    1/2
         * 10   1/2/10
         * 11   1/2/10/11
         */
        $items = static::descendantsOf($oldPath);
        $len = strlen($oldPath);
        foreach ($items as $item) {
            /** @var static $item */
            $item->path = $newPath.substr($item->path, $len);
            $item->level = $item->pathLevel();
            $item->save();
        }

        return $items->count();
    }
}
