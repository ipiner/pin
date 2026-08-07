<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Collection;
use Pin\Route\Routable;

/**
 * Route 测试套件
 *
 * 用于批量执行一组 Route 测试任务
 */
class TestSuite
{
    /**
     * Route 名称关键字与测试方法映射。
     */
    protected array $testingMethods = [];

    /**
     * @param  array<Routable>  $routes
     */
    public function __construct(
        protected TestCase|\Orchestra\Testbench\TestCase $testCase,
        protected array $routes
    ) {
        $this->testingMethods = [
            'Create' => TestingMethod::Created->value,
            'Update' => TestingMethod::Updated->value,
            'Delete' => TestingMethod::Deleted->value,
            'Index' => TestingMethod::Paginated->value,
        ];
    }

    /**
     * 执行所有 Route 测试任务
     */
    public function run(): void
    {
        $this->tasks()->each->run();
    }

    /**
     * 获取所有待执行的测试任务
     *
     * @return Collection<int, TestingTask>
     */
    public function tasks(): Collection
    {
        return collect($this->routes)->map(function (Routable $route) {
            return new TestingTask(
                $route->testing($this->testCase),
                $this->resolveTestingMethod($route)
            );
        });
    }

    /**
     * 根据 Route 名称解析对应测试方法
     */
    protected function resolveTestingMethod(Routable $route): string
    {
        /** @var \Pin\Route\Attributes\TestingMethod|null $attr */
        $attr = $route->attribute(\Pin\Route\Attributes\TestingMethod::class);
        if ($attr !== null) {
            return $attr->value;
        }

        foreach ($this->testingMethods as $name => $method) {
            if (str_contains($route->name, $name)) {
                return $method;
            }
        }

        return TestingMethod::Successful->value;
    }
}
