<?php

declare(strict_types=1);

namespace Pin\Auth;

use Illuminate\Http\Request;

/**
 * 命令行和队列任务的操作用户信息。
 */
class ConsoleUser
{
    /**
     * 默认控制台用户名。
     */
    public const string DEFAULT_USERNAME = 'console';

    /**
     * 当前系统用户 ID。
     */
    public int $id = 0;

    /**
     * 当前系统用户名。
     */
    public string $username;

    /**
     * 构造方法
     */
    public function __construct(?Request $request = null)
    {
        $this->username = $this->resolveUsername($request ?? app()->request);
        $this->id = $this->resolveUid();
    }

    /**
     * 解析系统用户 ID。
     */
    protected function resolveUid(): int
    {
        return function_exists('posix_geteuid') ? posix_geteuid() : 0;
    }

    /**
     * 解析系统用户名。
     */
    protected function resolveUsername(Request $request): string
    {
        // Linux: USER
        // Windows: USERNAME
        $name = $request->server('USER') ?? $request->server('USERNAME');

        return $name ?? self::DEFAULT_USERNAME;
    }
}
