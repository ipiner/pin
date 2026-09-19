<?php

declare(strict_types=1);

namespace Pin\Debug;

use Dedoc\Scramble\Attributes\ExcludeAllRoutesFromDocs;
use Illuminate\Http\Request;
use Pin\Errors\IError;
use Pin\Errors\Registry;
use Pin\Http\ApiResponse;
use Pin\Http\Controller;
use Pin\Route\RouteRegistry;
use Pin\Route\RouteRegistryItem;

/**
 * 调试信息。
 */
#[ExcludeAllRoutesFromDocs]
class DebugController extends Controller
{
    /**
     * 获取配置。
     */
    public function config(?string $name = null): ApiResponse
    {
        return $this->success(config()->get($name));
    }

    /**
     * 获取请求信息。
     */
    public function index(): ApiResponse
    {
        $files = get_included_files();

        return $this->success([
            'request_id' => app()->getRequestId(),
            'count' => count($files),
            'files' => $files,
        ]);
    }

    /**
     * 获取错误码。
     */
    public function errors(): ApiResponse
    {
        $errors = collect(Registry::all())
            ->sortKeys()
            ->map(static fn (IError $error) => [
                'code' => $error->code(),
                'status' => $error->statusCode(),
                'message' => $error->message(),
            ])
            ->values();

        return $this->success($errors);
    }

    /**
     * 生成 TypeScript 类型、标签和表格列。
     */
    public function generateTypescript(
        Request $request,
        TypescriptGenerator $generator
    ): string {
        $schemas = $this->loadSchemas($request->query('connection', 'default'));
        $typescript = $generator->generate($schemas, $request->boolean('snake_case'));

        return '<pre>'.e($typescript).'</pre>';
    }

    /**
     * 获取 PHP 信息。
     */
    public function phpinfo(int $flags = INFO_ALL): string
    {
        ob_start();
        phpinfo($flags);

        return (string) ob_get_clean();
    }

    /**
     * 获取已注册路由。
     */
    public function routes(): ApiResponse
    {
        $routes = RouteRegistry::items()
            ->sortKeys()
            ->map(static fn (RouteRegistryItem $item) => [
                'name' => $item->route->getName(),
                'action' => $item->route->action,
                'case' => $item->case::class.'::'.$item->case->name,
                'title' => $item->case->title(),
                'uri' => $item->case->uri(),
            ])
            ->values();

        return $this->success($routes);
    }

    /**
     * 读取数据表结构。
     */
    protected function loadSchemas(string $connection): array
    {
        abort_if($connection === '..' || basename($connection) !== $connection, 404);

        $file = database_path("schemas/{$connection}/__schemas__.php");
        abort_unless(is_file($file), 404, '数据表结构文件不存在。');

        return require $file;
    }
}
