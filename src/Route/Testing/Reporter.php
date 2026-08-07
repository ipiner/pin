<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

use Illuminate\Support\Str;
use Pin\Route\Routable;
use Pin\Support\Json;

/**
 * HTTP 测试请求输出 Reporter
 */
class Reporter
{
    /**
     * ANSI CLI 颜色映射
     */
    public const array COLORS = [
        'green' => '0;32',
        'red' => '0;31',
        'yellow' => '1;33',
    ];

    /**
     * HTTP Method 对应颜色
     */
    public const array METHOD_COLORS = [
        'GET' => 'green',
        'POST' => 'yellow',
        'PUT' => 'yellow',
        'DELETE' => 'red',
    ];

    /**
     * 是否启用请求输出
     */
    public ?bool $reportRequestEnabled = null;

    /**
     * 输出流资源
     *
     * @var resource
     */
    protected $stream;

    /**
     * 创建 Reporter 实例
     *
     * @param  resource|null  $stream
     */
    public function __construct($stream = null)
    {
        $this->stream = $stream ?? STDOUT;
    }

    /**
     * 输出 HTTP 请求与响应结果
     */
    public function reportRequest(
        Routable $route,
        string $uri,
        TestResponse $response,
    ): bool {
        $enabled = $this->reportRequestEnabled();

        if ($enabled) {
            fwrite($this->stream, $this->formatRequest($route, $uri, $response));
        }

        return $enabled;
    }

    /**
     * CLI ANSI 彩色输出
     */
    protected function color(string $text, string $color): string
    {
        return sprintf(
            "\033[%sm%s\033[0m",
            static::COLORS[$color] ?? '0',
            $text
        );
    }

    /**
     *  格式化 HTTP 请求测试输出
     */
    protected function formatRequest(
        Routable $route,
        string $uri,
        TestResponse $response,
    ): string {
        $status = $response->status();
        $method = str_pad($route->method(), 6);

        return sprintf(
            "\n[%-20s] [%s] %s %-40s %s",
            Str::limit($route->title() ?? $route->name(), 20),
            $this->color((string) $status, $this->statusColor($status)),
            $this->color($method, $this->methodColor($route->method())),
            Str::limit($uri, 40),
            Str::limit(Json::encode($response->json()), 80)
        );
    }

    /**
     * 获取 HTTP Method 对应颜色
     */
    protected function methodColor(string $method): string
    {
        return static::METHOD_COLORS[strtoupper($method)] ?? 'green';
    }

    /**
     * 是否启用请求输出
     */
    protected function reportRequestEnabled(): bool
    {
        return $this->reportRequestEnabled
            ?? (bool) config('testing.report_request_enabled', true);
    }

    /**
     * 获取 HTTP 状态码对应颜色
     */
    protected function statusColor(int $status): string
    {
        return match (true) {
            $status >= 200 && $status < 300 => 'green',
            $status >= 500 => 'red',
            default => 'yellow',
        };
    }
}
