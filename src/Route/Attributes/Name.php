<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;
use Pin\Attributes\Config;

/**
 * 路由名称。
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Name extends Config
{
}
