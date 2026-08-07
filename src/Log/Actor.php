<?php

declare(strict_types=1);

namespace Pin\Log;

use Illuminate\Contracts\Auth\Authenticatable;
use Pin\Auth\ConsoleUser;

/**
 * 当前执行主体（Actor）解析器
 */
class Actor
{
    /**
     * 获取 Actor 的唯一标识 ID
     */
    public function id(): int
    {
        return $this->user()?->id ?? 0;
    }

    /**
     * 获取 Actor 类型标识
     */
    public function type(): string
    {
        $user = $this->user();

        return match (true) {
            $user instanceof ConsoleUser => 'console',
            $user === null => 'guest',
            default => strtolower(class_basename($user)),
        };
    }

    /**
     * 获取当前 Actor 用户对象
     */
    public function user(): Authenticatable|ConsoleUser|null
    {
        return match (true) {
            auth()->hasUser() => auth()->user(),   // Web 已登录用户
            app()->runningInHttp() => null,        // HTTP 请求未登录用户（guest）
            default => app(ConsoleUser::class),    // CLI 执行用户
        };
    }

    /**
     * 获取 Actor 名称
     */
    public function username(): string
    {
        return $this->user()?->username ?? 'unknown';
    }
}
