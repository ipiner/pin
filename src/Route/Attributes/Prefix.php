<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;
use Pin\Attributes\Config;

/**
 * 路由前缀
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Prefix extends Config
{
}
