<?php

declare(strict_types=1);

namespace Pin\Errors;

/**
 * 错误消息翻译。
 */
class Translator
{
    /**
     * 翻译消息。
     */
    public static function trans(
        string $message,
        array $replace = [],
        ?string $locale = null
    ): string {
        if (app()->has('translator')) {
            return __($message, $replace, $locale);
        }

        return static::transFallback($message, $replace);
    }

    /**
     * 替换消息占位符。
     */
    public static function transFallback(string $message, array $replace = []): string
    {
        if (! $replace) {
            return $message;
        }

        $pairs = [];

        foreach ($replace as $key => $value) {
            $pairs[':'.$key] = $value;
        }

        return strtr($message, $pairs);
    }
}
