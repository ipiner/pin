<?php

declare(strict_types=1);

namespace Pin\Http;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Override;
use Pin\Database\QueryMonitor;
use Pin\Errors\Errors;
use Pin\Errors\IError;
use Pin\Support\Size;
use Pin\Support\Timer;

/**
 * API 统一响应
 *
 * @template TData
 */
class ApiResponse implements Responsable
{
    /**
     * JSON 编码选项
     */
    public const int JSON_ENCODE_OPTIONS = JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_INVALID_UTF8_IGNORE;

    /**
     * 业务状态码
     */
    public protected(set) int $code;

    /**
     * 响应消息
     */
    public protected(set) string $message;

    /**
     * 响应数据
     */
    public protected(set) mixed $data;

    /**
     * 附加元信息
     */
    public protected(set) ?array $meta;

    /**
     * HTTP 状态码
     */
    protected int $statusCode = 200;

    /**
     * HTTP 响应头
     *
     * @var array<string, string|null>
     */
    protected array $headers = [];

    /**
     * 创建响应实例
     *
     * @param  int|IError  $code  业务状态码
     * @param  string  $message  响应消息
     * @param  mixed  $data  响应数据
     * @param  array|null  $meta  元信息
     */
    public static function make(
        int|IError $code = Errors::None,
        string $message = '',
        mixed $data = null,
        ?array $meta = null
    ): static {
        $response = app(static::class);
        $response->code = is_int($code) ? $code : $code->code();
        $response->data = $data;
        $response->meta = $meta;
        $response->message = $message;
        $response->message = $response->resolveMessage();

        return $response;
    }

    /**
     * 是否符合 API 响应结构
     */
    public static function matches(mixed $data): bool
    {
        if ($data instanceof JsonResponse) {
            $data = $data->getData(true);
        }

        return is_array($data)
            && isset($data['code'], $data['message'])
            && array_key_exists('data', $data)
            && is_int($data['code'])
            && is_string($data['message']);
    }

    /**
     * 转换为基础响应数组
     *
     * @return array{
     *     code:int,
     *     message:string,
     *     data:TData,
     *     meta:array|null
     * }
     */
    public function toArray(): array
    {
        return [
            // 业务状态码，`0`成功，其它值失败
            'code' => $this->code,

            // 响应消息
            'message' => $this->message,

            /**
             * 响应数据
             *
             * @var TData
             */
            'data' => $this->data,

            /**
             * 元信息
             *
             * @example null
             */
            'meta' => $this->meta,
        ];
    }

    /**
     * 转换为 JsonResponse
     */
    #[Override]
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse(
            $this->responseData(),
            $this->statusCode,
            $this->headers,
            static::JSON_ENCODE_OPTIONS,
        );
    }

    /**
     * 设置响应头
     *
     * @return $this
     */
    public function withHeaders(string|array $key, ?string $value = null): self
    {
        $this->headers = array_merge(
            $this->headers,
            is_array($key) ? $key : [$key => $value]
        );

        return $this;
    }

    /**
     * 设置 HTTP 状态码
     *
     * @return $this
     */
    public function withStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * 获取调试信息
     *
     * @return array<string, mixed>
     */
    protected function debugData(): array
    {
        $monitor = app(QueryMonitor::class);

        return [
            /**
             * 请求唯一 ID
             */
            'request_id' => app()->getRequestId(),

            /**
             * 当前运行环境
             */
            'env' => app()->environment(),

            /**
             * 请求耗时（毫秒）
             */
            'time' => Timer::durationSinceStartOfRequest()->milliseconds(),

            /**
             * SQL 执行数量
             */
            'sql_count' => $monitor->profile->count,

            /**
             * SQL 总耗时（毫秒）
             */
            'sql_time' => $monitor->profile->time,

            /**
             * SQL 调试列表
             */
            'sqls' => $monitor->response->all(),

            /**
             * 当前内存占用
             */
            'memory' => Size::format(memory_get_usage(true)),

            /**
             * 峰值内存占用
             */
            'memory_peak' => Size::format(memory_get_peak_usage(true)),
        ];
    }

    /**
     * 解析响应消息
     */
    protected function resolveMessage(): string
    {
        return $this->message === '' ? Errors::getMessage($this->code) : $this->message;
    }

    /**
     * 构建响应数据
     *
     * @return array<string, mixed>
     */
    protected function responseData(): array
    {
        $data = $this->toArray();

        if (! $data['meta']) {
            unset($data['meta']);
        }

        if (app()->hasDebugModeEnabled()) {
            $data['debug'] = $this->debugData();
        }

        return $data;
    }
}
