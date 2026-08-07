<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Concerns;

use Pin\Models\Model;
use Pin\Module\ModuleInspector;

/**
 * 提供 Route Testing 中的模型能力
 */
trait HasModel
{
    use \Pin\Support\Traits\HasModel {
        attributes as private;
    }

    /**
     * 初始化当前 Route 对应的模型类。
     */
    protected function bootModel(): void
    {
        $this->withModel(
            ModuleInspector::make($this->route)->model()
        );
    }

    /**
     * 查找或创建模型实例。
     *
     * @param  Model|int|null  $id  模型实例、模型 ID 或 null
     */
    protected function findModel(Model|int|null $id): ?Model
    {
        return match (true) {
            $id instanceof Model => $id,
            $id === null => $this->factory()->create(),
            default => $this->modelClass::find($id),
        };
    }
}
