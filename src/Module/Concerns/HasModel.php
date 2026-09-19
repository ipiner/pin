<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

trait HasModel
{
    /**
     * 模型类名
     */
    protected string $model;

    /**
     * 解析模型类名
     */
    public function model(): string
    {
        return $this->model ??= $this->resolveFirstExistingClass(
            $this->getModelCandidates()
        );
    }

    /**
     * 生成模型候选类名。
     *
     * @return list<string>
     */
    protected function getModelCandidates(): array
    {
        ['name' => $module, 'namespace' => $namespace] = $this->module();
        $domain = $this->domain();
        $default = "App\\Models\\{$domain}";

        if (! $namespace) {
            return [$default];
        }

        return [
            "{$namespace}\\Models\\{$domain}",
            "App\\Models\\{$module}\\{$domain}",
            "App\\Models\\{$module}\\{$module}{$domain}",
            "App\\Models\\{$module}{$domain}",
            $default,
        ];
    }
}
