<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Concerns;

use Pin\Module\ModuleInspector;

/**
 * 模块领域 支持
 */
trait HasDomain
{
    /**
     * 当前 模块领域 名称
     */
    public protected(set) string $domain;

    /**
     * 设置 模块领域 名称
     */
    public function withDomain(string $name): static
    {
        $this->domain = $name;

        return $this;
    }

    /**
     * 初始化 模块领域 名称
     */
    protected function bootDomain(): void
    {
        $this->domain = ModuleInspector::make($this->route)->domain();
    }
}
