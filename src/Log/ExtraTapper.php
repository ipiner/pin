<?php

declare(strict_types=1);

namespace Pin\Log;

use Illuminate\Log\Logger;

/**
 * 日志扩展 Tap 回调
 */
class ExtraTapper
{
    /**
     * 构造方法
     */
    public function __construct(protected ExtraProcessor $processor)
    {
    }

    /**
     * Tap 回调方法
     */
    public function __invoke(Logger $logger): void
    {
        $logger->getLogger()->pushProcessor($this->processor);
    }
}
