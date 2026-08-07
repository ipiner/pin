<?php

declare(strict_types=1);

namespace Pin\Route;

use BackedEnum;

/**
 * 可路由定义接口
 */
interface Routable extends BackedEnum
{
    /**
     * 注册当前枚举中的所有路由
     */
    public static function registerRoutes(): void;

    /**
     * 获取当前枚举 Case 上指定类型的 Attribute 实例。
     *
     * @param  class-string  $class  要获取的 Attribute 类名
     */
    public function attribute(string $class): mixed;

    /**
     * 获取当前枚举对应的路由定义信息
     */
    public function definition(): RouteDefinition;
}
