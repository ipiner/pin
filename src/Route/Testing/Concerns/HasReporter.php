<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Concerns;

use Pin\Route\Testing\Reporter;

/**
 * Reporter 支持
 */
trait HasReporter
{
    /**
     * HTTP 测试输出 Reporter
     */
    protected Reporter $reporter;

    /**
     * 设置自定义 Reporter
     */
    public function withReporter(Reporter $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }

    /**
     * 是否启用请求日志输出
     */
    public function withReportRequestEnabled(bool $enabled = true): static
    {
        $this->reporter()->reportRequestEnabled = $enabled;

        return $this;
    }

    /**
     * 获取 Reporter 实例
     */
    protected function reporter(): Reporter
    {
        return $this->reporter ??= new Reporter();
    }
}
