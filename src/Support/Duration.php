<?php

declare(strict_types=1);

namespace Pin\Support;

/**
 * 执行耗时与内存变化。
 */
class Duration
{
    /**
     * 初始内存占用（构造时记录）
     */
    protected int $memory;

    /**
     * @param  float  $start  开始时间戳（秒）
     * @param  float  $end  结束时间戳（秒）
     */
    public function __construct(
        protected readonly float $start,
        protected readonly float $end
    ) {
        $this->memory = memory_get_usage();
    }

    /**
     * 获取执行耗时（秒）
     */
    public function seconds(int $decimals = 4): float
    {
        return round($this->end - $this->start, $decimals);
    }

    /**
     * 获取执行耗时（毫秒）
     */
    public function milliseconds(): int
    {
        return (int) (($this->end - $this->start) * 1000);
    }

    /**
     * 获取内存使用变化（字节）
     */
    public function memoryUsage(): int
    {
        return memory_get_usage() - $this->memory;
    }
}
