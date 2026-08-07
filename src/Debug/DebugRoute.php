<?php

declare(strict_types=1);

namespace Pin\Debug;

use Illuminate\Support\Facades\Route;
use Pin\Route\Attributes\Name;
use Pin\Route\InteractsWithRoute;
use Pin\Route\Routable;

/**
 * 调试路由定义
 *
 * 仅在 Debug 模式下生效，用于系统调试和信息查看
 */
enum DebugRoute: string implements Routable
{
    use InteractsWithRoute;

    /**
     * 调试首页
     */
    case Index = 'GET:/api/debug';

    /**
     * 路由信息
     */
    case Routes = 'GET:/api/debug/routes';

    /**
     * 所有错误码
     */
    case Errors = 'GET:/api/debug/errors';

    /**
     * PHP 信息查看
     */
    #[Name('debug.phpinfo')]
    case Phpinfo = 'GET:/api/debug/phpinfo/{flag?}';

    /**
     * 配置信息查看
     */
    #[Name('debug.config')]
    case Config = 'GET:/api/debug/config/{key?}';

    /**
     * 生成 TypeScript 接口定义、字段文本和列表页表格列定义
     */
    case GenerateTypescript = 'GET:/api/debug/typescript/generate';

    /**
     * 注册路由
     *
     * 该组路由仅在 Debug 模式下生效，并无需认证
     */
    public static function registerRoutes(): void
    {
        Route::withoutMiddleware('auth')->group(
            fn () => self::addRoutes()
        );
    }

    /**
     * 获取 Controller 类名。
     *
     * @return class-string
     */
    protected function controller(): string
    {
        return DebugController::class;
    }
}
