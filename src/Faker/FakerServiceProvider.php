<?php

declare(strict_types=1);

namespace Pin\Faker;

use Illuminate\Support\Facades\Validator;
use Pin\Support\ServiceProvider;

/**
 * 测试数据服务提供者
 */
class FakerServiceProvider extends ServiceProvider
{
    /**
     * 单例服务
     */
    public $singletons = [
        Faker::class,
        RuleParser::class,
        ValueResolver::class,
        InferManager::class,
    ];

    /**
     * 注册占位验证规则
     */
    public function boot(): void
    {
        Validator::extend('fake', static fn () => true);
    }
}
