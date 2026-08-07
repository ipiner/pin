<?php

declare(strict_types=1);

namespace Pin\Tree;

/**
 * 树型数据的规则校验器
 */
class TreeGuard
{
    public function __construct(protected ModelService $service)
    {
    }

    /**
     * 校验目标父节点是否合法
     *
     * @param  int  $id  当前节点 ID（新增时可能为临时值）
     * @param  int  $pid  目标父节点 ID
     */
    public function validatePid(int $id, int $pid): bool|string
    {
        $name = $this->service->resourceName;

        return match (true) {
            // 根节点合法
            $pid === 0 => true,

            // 自己不能作为自己的父节点
            $id === $pid => "{$name}不能互为子{$name}",

            // 父节点不存在
            empty($item = $this->service->modelClass::find($pid)) => "所属{$name}不存在",

            // 防止循环引用：
            // 当前节点不能出现在目标父节点的路径中
            in_array($id, $item->paths()) => "{$name}不能作为自己的子{$name}",

            default => true,
        };
    }
}
