<?php

declare(strict_types=1);

namespace Pin\Cache;

use Pin\Support\Facades\RuntimeCache;

/**
 * HashKey
 *
 * 将字符串 key 映射为 Redis Hash 结构：
 * - key   => hash key
 * - field => hash field
 */
class HashKey
{
    public function __construct(public string $key, public string $field)
    {
    }

    /**
     * 解析单个 key
     *
     * 使用最后一个 ":" 作为分隔符
     *
     * ```
     * users:1 => users
     * // key => users
     * // field => 1
     * ```
     */
    public static function parse(string $raw): static
    {
        return RuntimeCache::rememberForever(
            static::class.'.'.$raw,
            function () use ($raw) {
                $pos = strrpos($raw, ':');

                if ($pos === false) {
                    return new static($raw, '');
                }

                return new static(
                    substr($raw, 0, $pos),
                    substr($raw, $pos + 1),
                );
            }
        );
    }

    /**
     * 批量解析 keys
     *
     * @return array{0: string, 1: array<int, string>}
     */
    public static function parseMany(array $keys): array
    {
        $hashKey = '';
        $fields = [];

        foreach ($keys as $raw) {
            $item = static::parse($raw);

            $hashKey = $item->key;
            $fields[] = $item->field;
        }

        return [$hashKey, $fields];
    }
}
