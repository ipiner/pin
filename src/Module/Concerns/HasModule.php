<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

trait HasModule
{
    /**
     * 缓存当前类解析出的模块信息。
     *
     * @var array{name: string|null, namespace: string|null}|null
     */
    protected array $module;

    /**
     * 从 `App\Modules\...` 类或嵌套路由类解析所属模块。
     *
     * @return array{name: string|null, namespace: string|null}
     */
    public function module(): array
    {
        if (isset($this->module)) {
            return $this->module;
        }

        // `App\Modules\Product\ProductService` 和
        // `App\Routes\Product\ProductRoute` 都会映射到 `App\Modules\Product`。
        if (
            str_starts_with($this->class, 'App\\Modules')
            || (str_ends_with($this->basename, 'Route') && count($this->parts) > 3)
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
