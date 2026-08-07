<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

trait HasModel
{
    /**
     * 按命名约定解析出的模型类名。
     */
    protected string $model;

    /**
     * 解析第一个存在的模型候选类
     *
     * 都不存在时返回最后一个兜底候选。
     */
    public function model(): string
    {
        return $this->model ??= $this->resolveFirstExistingClass(
            $this->getModelCandidates()
        );
    }

    /**
     * 从模块专属路径到 `App\Models` 依次生成模型候选类名。
     *
     * @return list<class-string|string>
     */
    protected function getModelCandidates(): array
    {
        $module = $this->module();
        $namespace = $module['namespace'];
        $module = $module['name'];
        $domain = $this->domain();

        $candidates = [
            // 默认模型：App\Models\Category
            sprintf('App\\Models\\%s', $domain),
        ];

        if (! $namespace) {
            return $candidates;
        }

        return [
            // 模块内模型：App\Modules\Product\Models\Product
            sprintf('%s\\Models\\%s', $namespace, $domain),

            // 模块分组模型：App\Models\Product\Product
            sprintf('App\\Models\\%s\\%s', $module, $domain),

            // 模块分组复合模型：App\Models\Product\ProductCategory
            sprintf('App\\Models\\%s\\%s%s', $module, $module, $domain),

            // 顶层复合模型：App\Models\ProductCategory
            sprintf('App\\Models\\%s%s', $module, $domain),
            ...$candidates,
        ];
    }
}
