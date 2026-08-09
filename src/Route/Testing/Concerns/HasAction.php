<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Concerns;

use Pin\Action\Action;
use Pin\Module\ModuleInspector;

/**
 * Action 支持
 *
 * @template TAction of Action
 */
trait HasAction
{
    /**
     * 当前绑定的 Action 类名
     *
     * @var class-string<TAction>
     */
    protected string $actionClass;

    /**
     * 设置 Action 类
     *
     * @param  class-string<TAction>  $actionClass  要绑定的 Action 类名
     */
    public function withAction(string $actionClass): static
    {
        $this->actionClass = $actionClass;

        return $this;
    }

    /**
     * 获取 Action 实例
     *
     * @return TAction 返回 Action 实例
     */
    protected function action(): Action
    {
        return app($this->actionClass);
    }

    /**
     * 初始化 Action 类
     */
    protected function bootAction(): void
    {
        $this->actionClass = $this->guessActionClass();
    }

    /**
     * 推导 ActionClass
     */
    protected function guessActionClass(): string
    {
        $attr = $this->route->attribute(\Pin\Route\Attributes\Action::class);
        if ($attr) {
            return $attr->value;
        }

        return ModuleInspector::make($this->route)->action($this->route);
    }

    /**
     * 判断当前 Action 是否存在
     */
    protected function hasAction(): bool
    {
        return isset($this->actionClass) && class_exists($this->actionClass);
    }
}
