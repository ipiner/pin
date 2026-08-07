<?php

declare(strict_types=1);

namespace Pin;

use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Str;
use Pin\Bootstrap\ApplicationBuilder;
use Pin\Bootstrap\LoadConfiguration;

/**
 * 自定义应用程序类
 *
 * @property Request $request 当前 HTTP 请求实例
 */
class Application extends \Illuminate\Foundation\Application
{
    /**
     * 当前请求唯一标识
     */
    protected string $requestId;

    /**
     * 构造函数
     *
     * @param  string|null  $basePath  应用基础路径
     */
    public function __construct($basePath = null)
    {
        parent::__construct($basePath);

        // 禁用 putenv，避免在多线程/协程环境（如 Swoole）中产生全局污染
        // Laravel 默认会通过 putenv 写入环境变量，这在长生命周期进程中是不安全的
        Env::disablePutenv();

        // 使用自定义 LoadConfiguration Bootstrap 替代 Laravel 默认的
        $this->bind(
            \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
            LoadConfiguration::class
        );
    }

    /**
     * @return ApplicationBuilder
     */
    public static function configure(?string $basePath = null)
    {
        return new ApplicationBuilder(parent::configure($basePath));
    }

    /**
     * 获取请求uuid
     */
    public function getRequestId(): string
    {
        if (! isset($this->requestId)) {
            $this->requestId = Str::uuid()->toString();
        }

        return $this->requestId;
    }

    /**
     * 已加载配置钩子
     */
    public function loadedConfiguration(): void
    {
    }

    /**
     * 当前运行环境是否为 HTTP 请求
     */
    public function runningInHttp(): bool
    {
        return empty($this['request']->server('argv'));
    }
}
