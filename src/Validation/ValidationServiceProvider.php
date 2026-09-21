<?php

declare(strict_types=1);

namespace Pin\Validation;

use Illuminate\Support\Facades\Validator;
use Pin\Support\ServiceProvider;

/**
 * 验证服务提供者
 */
class ValidationServiceProvider extends ServiceProvider
{
    /**
     * 注册查询标记规则
     */
    public function boot(): void
    {
        Validator::extend('q', static fn (): bool => true);
    }
}
