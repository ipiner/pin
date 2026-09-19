<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Pin\IdGenerator\IdGenerator;

/**
 * 树节点 ID 生成。
 */
trait TreeIdGenerator
{
    /**
     * 生成节点 ID。
     */
    public function generateNodeId(): int
    {
        return method_exists($this, 'newUniqueId')
            ? $this->newUniqueId()
            : IdGenerator::Redis->generate();
    }
}
