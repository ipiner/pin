<?php

declare(strict_types=1);

namespace Pin\Route;

/**
 * 路由枚举支持
 */
trait InteractsWithRoute
{
    use Concerns\HasAttribute;
    use Concerns\HasDefinition;
    use Concerns\HasRegister;
    use Concerns\HasTesting;
}
