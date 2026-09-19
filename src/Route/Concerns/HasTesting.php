<?php

declare(strict_types=1);

namespace Pin\Route\Concerns;

use Illuminate\Foundation\Testing\TestCase;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Pin\Route\Routable;
use Pin\Route\Testing\Testing;
use Pin\Route\Testing\TestResponse;
use Pin\Route\Testing\TestSuite;

/**
 * 路由测试支持
 */
trait HasTesting
{
    /**
     * 创建路由测试实例
     */
    public function testing(TestCase|TestbenchTestCase $testCase): Testing
    {
        $testing = new Testing($testCase, $this);
        $this->configureTesting($testing);

        return $testing;
    }

    /**
     * 发送 JSON 测试请求
     *
     * @param  array<string, mixed>|null  $payload
     * @param  array<string, string>  $headers
     */
    public function testJson(
        TestCase|TestbenchTestCase $testCase,
        ?array $payload = null,
        array $headers = []
    ): TestResponse {
        return $this->testing($testCase)->json($payload, $headers);
    }

    /**
     * 创建测试套件
     *
     * @param  Routable[]|null  $routes
     */
    public static function tests(
        TestCase|TestbenchTestCase $testCase,
        ?array $routes = null
    ): TestSuite {
        return new TestSuite($testCase, $routes ?? static::cases());
    }

    /**
     * 配置路由测试
     */
    protected function configureTesting(Testing $testing): void
    {
    }
}
