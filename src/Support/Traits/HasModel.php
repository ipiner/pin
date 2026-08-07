<?php

declare(strict_types=1);

namespace Pin\Support\Traits;

use Pin\Models\Model;
use Pin\Module\ModuleInspector;

/**
 * 为类提供模型绑定与实例化功能
 *
 * @template TModel of Model
 */
trait HasModel
{
    /**
     * 模型类名 (class-string)
     *
     * @var class-string<TModel>
     */
    public protected(set) string $modelClass;

    /**
     * 获取模型字段 attributes
     */
    public function attributes(): array
    {
        return $this->hasModel() ? $this->modelClass::metadata()->attributes : [];
    }

    /**
     * 设置模型类
     *
     * @param  class-string<TModel>  $modelClass  要绑定的模型类名
     * @return $this 返回当前对象，支持链式调用
     */
    public function withModel(string $modelClass): static
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    /**
     * 初始化模型类
     *
     * @param  class-string<TModel>|null  $modelClass  可选的模型类名
     */
    protected function bootModel(?string $modelClass = null): void
    {
        $this->modelClass ??= $modelClass ?? ModuleInspector::make(static::class)->model();
    }

    /**
     * 模型是否存在
     */
    protected function hasModel(): bool
    {
        return class_exists($this->modelClass);
    }

    /**
     * 获取模型实例
     *
     * @return TModel
     */
    protected function model()
    {
        return new $this->modelClass();
    }
}
