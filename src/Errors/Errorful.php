<?php

declare(strict_types=1);

namespace Pin\Errors;

use Pin\Attributes\Attribute;
use Pin\Errors\Attribute\Group;
use Pin\Exceptions\Exception;
use Throwable;

/**
 * 错误枚举行为。
 */
trait Errorful
{
    /**
     * 获取业务错误码。
     */
    public function code(): int
    {
        return (int) $this->value;
    }

    /**
     * 创建异常。
     */
    public function exception(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null
    ): Exception {
        $error = Error::parse($this);

        return new Exception(
            $message ?? $this->message(),
            $code ?? $error->code,
            $previous
        )->withStatusCode($error->statusCode);
    }

    /**
     * 获取错误消息。
     */
    public function message(array $replace = []): string
    {
        $error = Registry::resolve($this);

        return $error === $this ? $this->translate($replace) : $error->message($replace);
    }

    /**
     * 获取 HTTP 状态码。
     */
    public function statusCode(): int
    {
        return Error::parse($this)->statusCode;
    }

    /**
     * 抛出异常。
     *
     * @throws Exception
     */
    public function throw(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null
    ): never {
        throw $this->exception($message, $code, $previous);
    }

    /**
     * 获取枚举项属性。
     *
     * @template TAttribute of object
     *
     * @param  class-string<TAttribute>  $class
     * @return TAttribute|null
     */
    protected function attribute(string $class): ?object
    {
        return Attribute::get($this, $class);
    }

    /**
     * 翻译错误消息。
     */
    protected function translate(array $replace = []): string
    {
        $key = Error::parse($this)->messageKey;
        $group = $this->translationGroup();

        if ($group === false) {
            return Translator::transFallback($key, $replace);
        }

        return Translator::trans($group ? $group.'.'.$key : $key, $replace);
    }

    /**
     * 获取翻译分组。
     */
    protected function translationGroup(): string|false
    {
        $group = $this->attribute(Group::class)
            ?? Attribute::get(static::class, Group::class);

        return $group?->value ?? '';
    }
}
