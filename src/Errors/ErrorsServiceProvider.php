<?php

declare(strict_types=1);

namespace Pin\Errors;

use Pin\Support\ServiceProvider;

/**
 * 错误码服务提供者
 */
class ErrorsServiceProvider extends ServiceProvider
{
    /**
     * 加载错误定义
     */
    public function boot(): void
    {
        Registry::register(Errors::cases());
        Registry::load(app_path('Errors'));
    }
}
