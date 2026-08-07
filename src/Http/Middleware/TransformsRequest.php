<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

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
     * 解析非生产环境下的明文输入
     *
     * 仅用于非生产环境（如 API 文档 Scramble 或本地调试），
     * 提供明文输入支持以便绕过加密流程进行开发验证。
     */
    public static function resolvePlainInput(string $input): ?string
    {
        if (! app()->isProduction() && str_starts_with($input, 'plain:')) {
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
