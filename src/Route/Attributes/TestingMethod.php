<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;
use Pin\Route\Testing\TestingMethod as TestingMethodEnum;

/**
 * 指定批量测试方法
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class TestingMethod
{
    /**
     * 对应的测试方法
     */
    public string $value;

    public function __construct(string|TestingMethodEnum $name)
    {
        $this->value = $name instanceof TestingMethodEnum ? $name->value : $name;
    }
}
