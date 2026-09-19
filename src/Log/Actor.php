<?php

declare(strict_types=1);

namespace Pin\Log;

use Illuminate\Contracts\Auth\Authenticatable;
use Pin\Auth\ConsoleUser;

/**
 * 日志操作用户
 */
class Actor
{
    /**
     * 获取用户 ID
     */
    public function id(): int
    {
        return $this->user()?->id ?? 0;
    }

    /**
     * 获取用户类型
     */
    public function type(): string
    {
        $user = $this->user();

        return match (true) {
            $user instanceof ConsoleUser => 'console',
            ! $user => 'guest',
            default => strtolower(class_basename($user)),
        };
    }

    /**
     * 获取当前用户
     */
    public function user(): Authenticatable|ConsoleUser|null
    {
        $guard = auth()->guard();

        return match (true) {
            $guard->hasUser() => $guard->user(),
            app()->runningInHttp() => null,
            default => app(ConsoleUser::class),
        };
    }

    /**
     * 获取用户名
     */
    public function username(): string
    {
        return $this->user()?->username ?? 'unknown';
    }
}
