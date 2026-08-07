<?php

declare(strict_types=1);

namespace Pin\Validation;

use Illuminate\Support\Facades\Validator;
use Pin\Support\ServiceProvider;

/**
 * 自定义验证规则服务提供者
 */
class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        Validator::extend('q', fn () => true);
    }
}
