<?php

declare(strict_types=1);

namespace Pin\Support;

use Illuminate\Support\Facades\Request;
use LogicException;

/**
 * 计时器
 */
class Timer
{
    /**
     * 计时起点
     *
     * @var array<string, float>
     */
    protected array $timers = [];

    /**
     * 计算自请求开始以来的时间
     *
     * @param  float|null  $requestTime  请求起始时间戳（秒）
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
     * @throws LogicException
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
     * @throws LogicException
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
