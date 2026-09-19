<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest as BaseTransformsRequest;
use Illuminate\Support\Facades\Request;
use Override;

/**
 * 请求字段转换基类
 */
abstract class TransformsRequest extends BaseTransformsRequest
{
    /**
     * 待转换字段。
     *
     * @var string[]
     */
    protected array $fields = [];

    /**
     * 解析非生产环境或 API 文档的明文输入
     */
    public static function resolvePlainValue(string $input): ?string
    {
        if (! str_starts_with($input, 'plain:')) {
            return null;
        }

        if (app()->isProduction() && ! Request::isFromApiDocument()) {
            return null;
        }

        return substr($input, 6);
    }

    /**
     * 转换字段值
     */
    abstract protected function normalize(string $value): string;

    /**
     * 转换指定字段
     */
    #[Override]
    protected function transform($key, $value)
    {
        if (! in_array($key, $this->fields, true)) {
            return $value;
        }

        $value = (string) $value;

        if ($value === '') {
            return $value;
        }

        return $this->normalize($value);
    }
}
