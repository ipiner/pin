<?php

declare(strict_types=1);

namespace Pin\Module\Concerns;

trait HasController
{
    /**
     * 按命名约定解析出的控制器类名。
     */
    protected string $controller;

    /**
     * 解析第一个存在的控制器候选类
     *
     * 都不存在时返回最后一个兜底候选。
     */
    public function controller(): string
    {
        return $this->controller ??= $this->resolveFirstExistingClass(
            $this->getControllerCandidates()
        );
    }

    /**
     * 按从具体到通用的顺序生成控制器候选类名。
     *
     * @return list<class-string|string>
     */
    protected function getControllerCandidates(): array
    {
        $namespace = $this->module()['namespace'];
        $domain = $this->domain();

        if ($namespace) {
            return [
                // 模块根控制器：App\Modules\Product\ProductController
                sprintf('%s\\%sController', $namespace, $domain),

                // 模块领域控制器：App\Modules\Product\Category\CategoryController
                sprintf('%s\\%s\\%sController', $namespace, $domain, $domain),
            ];
        }

        return [
            // 独立模块控制器：App\Modules\Login\LoginController
            sprintf('App\\Modules\\%s\\%sController', $domain, $domain),

            // Laravel 默认控制器：App\Http\Controllers\CategoryController
            sprintf('App\\Http\\Controllers\\%sController', $domain),
        ];
    }
}
