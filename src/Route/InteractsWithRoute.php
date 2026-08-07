<?php

declare(strict_types=1);

namespace Pin\Route;

/**
 * 组合路由枚举所需的属性读取、定义解析、注册和测试能力。
 */
trait InteractsWithRoute
{
    use Concerns\HasAttribute,
        Concerns\HasDefinition,
        Concerns\HasRegister,
        Concerns\HasTesting;
}
