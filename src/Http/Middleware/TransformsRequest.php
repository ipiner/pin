<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Illuminate\Support\Facades\Request;
use Override;

/**
 * 请求字段转换基类
 *
 * 用于在请求进入业务层之前对指定字段进行标准化处理
 */
abstract class TransformsRequest extends \Illuminate\Foundation\Http\Middleware\TransformsRequest
{
    /**
     * 需要进行转换的字段列表
     *
     * @var string[]
     */
    protected array $fields = [];

    /**
     * 解析明文输入
     *
     * 满足以下条件之一时，支持使用 `plain:` 明文输入
     *
     * - 非生产环境
     * - API 文档
     */
    public static function resolvePlainInput(string $input): ?string
    {
        if (
            str_starts_with($input, 'plain:')
            && (Request::isFromApiDocument() || ! app()->isProduction())
        ) {
            return substr($input, 6);
        }

        return null;
    }

    /**
     * 字段标准化处理逻辑
     */
    abstract protected function normalize(string $value): string;

    /**
     * 请求字段转换入口
     */
    #[Override]
    protected function transform($key, $value)
    {
        $value = (string) $value;

        if (! in_array($key, $this->fields) || $value === '') {
            return $value;
        }

        return $this->normalize($value);
    }
}
