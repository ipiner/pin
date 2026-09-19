<?php

declare(strict_types=1);

namespace Pin\Log;

use Illuminate\Log\Logger;

/**
 * 日志处理器注册回调
 */
class ExtraTapper
{
    /**
     * 构造函数
     */
    public function __construct(protected ExtraProcessor $processor)
    {
    }

    /**
     * 注册上下文处理器
     */
    public function __invoke(Logger $logger): void
    {
        $logger->getLogger()->pushProcessor($this->processor);
    }
}
