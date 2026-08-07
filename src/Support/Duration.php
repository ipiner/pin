<?php

declare(strict_types=1);

namespace Pin\Support;

/**
 * Duration
 *
 * 执行耗时与内存使用统计工具
 */
class Duration
{
    /**
     * 初始内存占用（构造时记录）
     */
    protected int $memory;

    /**
     * 构造函数
     *
     * @param  float  $start  开始时间（microtime(true)）
     * @param  float  $end  结束时间（microtime(true)）
     */
    public function __construct(
        protected readonly float $start,
        protected readonly float $end
    ) {
        $this->memory = memory_get_usage();
    }

    /**
     * 获取执行耗时（秒）
     *
     * @param  int  $decimals  保留小数位数，默认 4 位
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
