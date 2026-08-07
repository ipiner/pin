<?php

declare(strict_types=1);

namespace Pin\Errors;

use Pin\Attributes\Attribute;
use Pin\Errors\Attribute\Group;
use Pin\Exceptions\Exception;
use Throwable;

/**
 * 为错误枚举提供行为能力（Behavior Trait）
 *
 * 使 IError 类枚举具备：
 * - 标准错误码 / HTTP 状态码访问能力
 * - 异常构建能力
 * - 国际化错误消息支持
 */
trait Errorful
{
    /**
     * 获取业务错误码
     */
    public function code(): int
    {
        return Error::parse($this)->code;
    }

    /**
     * 创建异常实例
     *
     * 支持覆盖默认错误信息与错误码
     */
    public function exception(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null
    ): Exception {
        $err = Error::parse($this);

        return new Exception(
            $message ?? $this->message(),
            $code ?? $err->code,
            $previous
        )
            ->withStatusCode($err->statusCode);
    }

    /**
     * 获取错误消息（支持翻译）
     *
     * 支持参数替换：
     * - {name} → 动态变量
     */
    public function message(array $replace = []): string
    {
        return $this->translate($replace);
    }

    /**
     * 获取 HTTP 状态码
     */
    public function statusCode(): int
    {
        return Error::parse($this)->statusCode;
    }

    /**
     * 直接抛出异常
     *
     * @throws Exception
     */
    public function throw(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null
    ) {
        throw $this->exception($message, $code, $previous);
    }

    /**
     * 获取当前枚举 case 上指定类型的 Attribute。
     *
     * @template TAttribute
     *
     * @param  class-string<TAttribute>  $class
     * @return TAttribute|null
     */
    protected function attribute(string $class): mixed
    {
        return Attribute::get($this, $class);
    }

    /**
     * 翻译错误消息
     */
    protected function translate(array $replace = []): string
    {
        $key = Error::parse($this)->messageKey;
        $group = $this->translationGroup();
        if ($group === false) {
            return Translator::transFallback($key);
        }

        return Translator::trans(
            $group ? $group.'.'.$key : $key,
            $replace
        );
    }

    /**
     * 获取翻译分组
     */
    protected function translationGroup(): string|false
    {
        $attr = $this->attribute(Group::class)
            ?? Attribute::get(static::class, Group::class);

        return $attr?->value ?? '';
    }
}
