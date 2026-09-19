<?php

declare(strict_types=1);

namespace Pin\Attributes;

/**
 * 构造时解析 $config. 配置引用，普通字符串保持原样。
 */
class Config
{
    protected const string CONFIG_PREFIX = '$config.';

    /**
     * 配置值保留原始类型；配置不存在时为 null。
     */
    public readonly mixed $value;

    /**
     * @param  string  $key  普通字符串或 $config.app.name 形式的配置引用
     */
    public function __construct(string $key)
    {
        $this->value = $this->resolveValue($key);
    }

    /**
     * 子类可扩展值的解析方式。
     */
    protected function resolveValue(string $value): mixed
    {
        if (! str_starts_with($value, static::CONFIG_PREFIX)) {
            return $value;
        }

        return config(substr($value, strlen(static::CONFIG_PREFIX)));
    }
}
