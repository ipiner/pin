<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

trait HasModule
{
    /**
     * 模块信息
     *
     * @var array{name: string|null, namespace: string|null}
     */
    protected array $module;

    /**
     * 解析所属模块
     *
     * @return array{name: string|null, namespace: string|null}
     */
    public function module(): array
    {
        if (isset($this->module)) {
            return $this->module;
        }

        if (
            str_starts_with($this->class, 'App\\Modules\\')
            || (
                str_starts_with($this->class, 'App\\Routes\\')
                && str_ends_with($this->basename, 'Route')
                && count($this->parts) > 3
            )
        ) {
            return $this->module = [
                'name' => $this->parts[2],
                'namespace' => 'App\\Modules\\'.$this->parts[2],
            ];
        }

        return $this->module = [
            'name' => null,
            'namespace' => null,
        ];
    }
}
