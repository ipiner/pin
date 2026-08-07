<?php

declare(strict_types=1);

namespace Pin\Route\Concerns;

use Illuminate\Foundation\Testing\TestCase;
use Pin\Route\Testing\Testing;
use Pin\Route\Testing\TestResponse;
use Pin\Route\Testing\TestSuite;

/**
 * Route Testing 支持
 */
trait HasTesting
{
    /**
     * 创建 Route Testing DSL 实例。
     *
     * @param  TestCase  $testCase  Laravel 测试用例实例
     * @return Testing Route Testing DSL 实例
     */
    public function testing(TestCase|\Orchestra\Testbench\TestCase $testCase): Testing
    {
        $testing = new Testing($testCase, $this);
        $this->configureTesting($testing);

        return $testing;
    }

    /**
     * 发送 JSON 测试请求。
     *
     * 这是 `testing()->json()` 的快捷方式。
     *
     * @param  array<string, string>  $headers
     */
    public function testJson(
        TestCase|\Orchestra\Testbench\TestCase $testCase,
        ?array $payload = null,
        array $headers = []
    ): TestResponse {
        return $this->testing($testCase)->json($payload, $headers);
    }

    /**
     * 创建测试套件
     */
    public static function tests(
        TestCase|\Orchestra\Testbench\TestCase $testCase,
        ?array $routes = null
    ): TestSuite {
        return new TestSuite(
            $testCase,
            $routes ?? static::cases(),
        );
    }

    /**
     * 配置 Testing 实例
     */
    protected function configureTesting(Testing $testing): void
    {
    }
}
