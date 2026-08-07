<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

/**
 * 单个测试任务封装
 */
class TestingTask
{
    public function __construct(public Testing $testing, public string $method)
    {
    }

    /**
     * 执行测试任务
     */
    public function run(): TestResponse
    {
        return $this->testing->{$this->method}();
    }
}
