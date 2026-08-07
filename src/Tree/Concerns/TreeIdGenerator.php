<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Pin\IdGenerator\IdGenerator;

/**
 * TreeIdGenerator
 *
 * 树结构节点 ID 生成策略层
 */
trait TreeIdGenerator
{
    /**
     * 生成 Tree 节点唯一 ID
     *
     * @return int 唯一节点 ID
     */
    public function generateNodeId(): int
    {
        return method_exists($this, 'newUniqueId')
            ? $this->newUniqueId()
            : IdGenerator::Redis->generate();
    }
}
