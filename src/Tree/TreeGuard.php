<?php

declare(strict_types=1);

namespace Pin\Tree;

/**
 * 父节点校验。
 */
class TreeGuard
{
    public function __construct(protected ModelService $service)
    {
    }

    /**
     * 校验目标父节点。
     *
     * @param  int  $id  当前节点 ID
     * @param  int  $pid  目标父节点 ID
     */
    public function validatePid(int $id, int $pid): bool|string
    {
        if ($pid === 0) {
            return true;
        }

        $name = $this->service->resourceName;

        if ($id === $pid) {
            return "{$name}不能互为子{$name}";
        }

        $parent = $this->service->modelClass::find($pid);

        if (! $parent) {
            return "所属{$name}不存在";
        }

        return in_array($id, $parent->paths(), true)
            ? "{$name}不能作为自己的子{$name}"
            : true;
    }
}
