<?php

declare(strict_types=1);

namespace Pin\Route;

use BackedEnum;

/**
 * 路由枚举接口
 */
interface Routable extends BackedEnum
{
    /**
     * 注册当前枚举中的所有路由
     */
    public static function registerRoutes(): void;

    /**
     * 获取路由属性
     *
     * @template TAttribute of object
     *
     * @param  class-string<TAttribute>  $class
     * @return TAttribute|null
     */
    public function attribute(string $class): mixed;

    /**
     * 获取路由定义
     */
    public function definition(): RouteDefinition;
}
