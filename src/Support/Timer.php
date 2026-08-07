<?php

declare(strict_types=1);

namespace Pin\Support;

use Illuminate\Support\Facades\Request;
use LogicException;

/**
 * 简易计时器工具类，用于测量代码执行时间。
 */
class Timer
{
    /**
     * 活动计时器数组
     *
     * @var array<string, float>
     */
    protected array $timers = [];

    /**
     * 计算自请求开始以来的时间
     *
     * @param  float|null  $requestTime  请求起始时间（微秒）
     * @return Duration 计时持续时间对象
     */
    public static function durationSinceStartOfRequest(?float $requestTime = null): Duration
    {
        return new Duration(
            $requestTime ?? Request::server('REQUEST_TIME_FLOAT'),
            microtime(true)
        );
    }

    /**
     * 开始计时
     *
     * @param  string  $name  计时器名称，默认 'default'
     *
     * @throws LogicException 如果计时器已开启则抛出异常
     */
    public function start(string $name = 'default'): void
    {
        if (isset($this->timers[$name])) {
            throw new LogicException("timer['{$name}']已经开启");
        }
        $this->timers[$name] = microtime(true);
    }

    /**
     * 停止计时并返回持续时间
     *
     * @param  string  $name  计时器名称，默认 'default'
     * @return Duration 计时器持续时间对象
     *
     * @throws LogicException 如果计时器未开启则抛出异常
     */
    public function stop(string $name = 'default'): Duration
    {
        if (! isset($this->timers[$name])) {
            throw new LogicException("timer[{$name}]未开启");
        }
        $duration = new Duration($this->timers[$name], microtime(true));
        unset($this->timers[$name]);

        return $duration;
    }
}
