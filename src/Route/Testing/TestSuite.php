<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Pin\Route\Attributes\TestingMethod as TestingMethodAttribute;
use Pin\Route\Routable;

/**
 * 路由测试套件
 */
class TestSuite
{
    /**
     * Route 名称关键字与测试方法映射。
     *
     * @var array<string, string>
     */
    protected array $testingMethods = [
        'Create' => TestingMethod::Created->value,
        'Update' => TestingMethod::Updated->value,
        'Delete' => TestingMethod::Deleted->value,
        'Index' => TestingMethod::Paginated->value,
    ];

    /**
     * @param  array<Routable>  $routes
     */
    public function __construct(
        protected TestCase|TestbenchTestCase $testCase,
        protected array $routes
    ) {
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
        return collect($this->routes)->map(fn (Routable $route) => new TestingTask(
            $route->testing($this->testCase),
            $this->resolveTestingMethod($route)
        ));
    }

    /**
     * 根据 Route 名称解析对应测试方法
     */
    protected function resolveTestingMethod(Routable $route): string
    {
        $attribute = $route->attribute(TestingMethodAttribute::class);
        if ($attribute) {
            return $attribute->value;
        }

        foreach ($this->testingMethods as $name => $method) {
            if (str_contains($route->name, $name)) {
                return $method;
            }
        }

        return TestingMethod::Successful->value;
    }
}
