<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Concerns;

use Pin\Module\ModuleInspector;

/**
 * Factory 支持
 */
trait HasFactory
{
    /**
     * 当前绑定的 Factory 类名
     *
     * @var class-string
     */
    protected ?string $factoryClass;

    /**
     * 设置 Factory 类
     *
     * @param  class-string  $factoryClass  要绑定的 Factory 类名
     */
    public function withFactory(string $factoryClass): static
    {
        $this->factoryClass = $factoryClass;

        return $this;
    }

    /**
     * 获取 Factory 实例
     */
    protected function factory()
    {
        return $this->factoryClass::new();
    }

    /**
     * 初始化 Factory 类
     */
    protected function bootFactory(): void
    {
        // UserRoute -> Database\Factories\UserFactory
        $this->factoryClass = ModuleInspector::make($this->route)->factory();
    }

    /**
     * 判断当前 Factory 是否存在
     */
    protected function hasFactory(): bool
    {
        return isset($this->factoryClass) && class_exists($this->factoryClass);
    }
}
