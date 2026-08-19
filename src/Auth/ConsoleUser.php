<?php

declare(strict_types=1);

namespace Pin\Auth;

use Illuminate\Http\Request;

/**
 * 控制台用户上下文。
 *
 * 在命令行、队列任务或其他非 HTTP 场景中，为认证系统提供一个
 * 可识别的用户上下文，使相关流程可以复用统一的用户模型。
 */
class ConsoleUser
{
    /**
     * 默认控制台用户名。
     *
     * 当运行环境无法提供系统用户名时使用。
     */
    public const DEFAULT_USERNAME = 'console';

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
     * 解析当前运行环境的用户 ID。
     */
    protected function resolveUid(): int
    {
        return function_exists('posix_geteuid') ? posix_geteuid() : 0;
    }

    /**
     * 从服务器环境变量中解析当前用户名。
     *
     * Linux 通常使用 USER，Windows 通常使用 USERNAME。
     */
    protected function resolveUsername(Request $request): string
    {
        // Linux: USER
        // Windows: USERNAME
        $name = $request->server('USER') ?? $request->server('USERNAME');

        return $name ?? self::DEFAULT_USERNAME;
    }
}
