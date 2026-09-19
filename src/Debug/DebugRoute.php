<?php

declare(strict_types=1);

namespace Pin\Debug;

use Illuminate\Support\Facades\Route;
use Pin\Route\Attributes\Name;
use Pin\Route\InteractsWithRoute;
use Pin\Route\Routable;

/**
 * 调试路由。
 */
enum DebugRoute: string implements Routable
{
    use InteractsWithRoute;

    /**
     * 调试首页。
     */
    case Index = 'GET:/api/debug';

    /**
     * 路由信息。
     */
    case Routes = 'GET:/api/debug/routes';

    /**
     * 错误码。
     */
    case Errors = 'GET:/api/debug/errors';

    /**
     * PHP 信息。
     */
    #[Name('debug.phpinfo')]
    case Phpinfo = 'GET:/api/debug/phpinfo/{flag?}';

    /**
     * 配置信息。
     */
    #[Name('debug.config')]
    case Config = 'GET:/api/debug/config/{key?}';

    /**
     * 生成 TypeScript 代码。
     */
    case GenerateTypescript = 'GET:/api/debug/typescript/generate';

    /**
     * 注册调试路由。
     */
    public static function registerRoutes(): void
    {
        Route::withoutMiddleware('auth')->group(static fn () => self::addRoutes());
    }

    /**
     * 获取控制器。
     *
     * @return class-string
     */
    protected function controller(): string
    {
        return DebugController::class;
    }
}
