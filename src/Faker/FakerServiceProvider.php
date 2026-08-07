<?php

declare(strict_types=1);

namespace Pin\Faker;

use Illuminate\Support\Facades\Validator;
use Pin\Support\ServiceProvider;

/**
 * Fake 数据生成服务提供者
 */
class FakerServiceProvider extends ServiceProvider
{
    /**
     * Fake 数据生成相关单例
     */
    public $singletons = [
        Faker::class,
        RuleParser::class,
        ValueResolver::class,
        InferManager::class,
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        Validator::extend('fake', fn () => true);
    }
}
