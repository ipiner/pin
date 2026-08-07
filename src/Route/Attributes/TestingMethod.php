<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;
use Pin\Support\Str;

/**
 * 指定 Route Testing 批量测试时使用的测试方法。
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class TestingMethod
{
    /**
     * 对应的测试方法
     */
    public string $value;

    public function __construct(string|\Pin\Route\Testing\TestingMethod $name)
    {
        $this->value = Str::string($name);
    }
}
