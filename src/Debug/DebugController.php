<?php

declare(strict_types=1);

namespace Pin\Debug;

use Dedoc\Scramble\Attributes\ExcludeAllRoutesFromDocs;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Pin\Errors\IError;
use Pin\Errors\Registry;
use Pin\Http\ApiResponse;
use Pin\Http\Controller;
use Pin\Route\RouteRegistry;
use Pin\Route\RouteRegistryItem;

#[ExcludeAllRoutesFromDocs]
class DebugController extends Controller
{
    /**
     * 配置
     */
    public function config(?string $name = null): ApiResponse
    {
        return $this->success(config()->get($name));
    }

    /**
     * 调试首页
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
     * 错误码
     */
    public function errors(): ApiResponse
    {
        $data = collect(Registry::all())->map(fn (IError $item) => [
            'code' => $item->code(),
            'status' => $item->statusCode(),
            'message' => $item->message(),
        ])
            ->sortKeys()
            ->values();

        return $this->success($data);
    }

    /**
     * 生成 TypeScript 接口定义、字段文本和列表页表格列定义
     *
     * - 根据每个模型生成：
     * - 1. TypeScript 接口 (`export type Model ={ ... }`)
     * - 2. 字段文本 (`export const labels = { ... }`)
     * - 3. 列表页表格列定义 (`export const columns = [ ... ]`)
     *
     * - 自动跳过 `deleted_at` 字段
     * - 数字类型（int, decimal）映射为 TypeScript `number`，其他类型映射为 `string`
     */
    public function generateTypescript(Request $request): string
    {
        // 数据库连接
        $connection = $request->query('connection', 'default');
        $snakeCase = $request->boolean('snake_case');

        $result = [];
        $schemas = require database_path('schemas/'.$connection.'/__schemas__.php');
        ksort($schemas);
        foreach ($schemas as $name => $item) {
            $model = Str::studly(Str::singular($name));
            $result[$model] = "export type $model = {";
            $result[$model.'.labels'] = 'export const labels = {';
            $result[$model.'.columns'] = 'export const columns = [';
            ksort($item['columns']);
            foreach ($item['columns'] as $column) {
                $name = $column['name'];
                if ($name === 'deleted_at') {
                    continue;
                }

                $name = $snakeCase ? $name : Str::camel($name);
                $result[$model] .= sprintf(
                    "\n  %s: %s; // %s",
                    $name,
                    Str::contains($column['type'], ['int', 'decimal']) ? 'number' : 'string',
                    $column['label']
                );
                $result[$model.'.labels'] .= sprintf(
                    "\n  %s: '%s',",
                    $name,
                    $column['label']
                );
                $result[$model.'.columns'] .= sprintf(
                    "\n  table.column('%s', labels.%s),",
                    $name,
                    $name
                );
            }

            $result[$model] .= "\n};";
            $result[$model.'.labels'] .= "\n};";
            $result[$model.'.columns'] .= "\n];";
        }

        return '<pre>'.implode("\n\n", $result).'</pre>';
    }

    /**
     * phpinfo
     */
    public function phpinfo(int $flags = INFO_ALL): string
    {
        ob_start();
        phpinfo($flags);

        return (string) ob_get_clean();
    }

    /**
     * 已注册路由
     */
    public function routes(): ApiResponse
    {
        $data = RouteRegistry::items()->map(fn (RouteRegistryItem $item) => [
            'name' => $item->route->getName(),
            'action' => $item->route->action,
            'case' => get_class($item->case).'::'.$item->case->name,
            'title' => $item->case->title(),
            'uri' => $item->case->uri(),
        ])
            ->sortKeys()
            ->values();

        return $this->success($data);
    }
}
