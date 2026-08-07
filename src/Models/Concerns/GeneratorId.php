<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

/**
 * 使用非自增唯一 ID 的模型能力
 */
trait GeneratorId
{
    /**
     * 是否使用自增 ID
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * 初始化生成器 ID
     */
    public function initializeGeneratorId(): void
    {
        $this->usesUniqueIds = true;
    }

    /**
     * 获取唯一 ID 字段列表
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }
}
