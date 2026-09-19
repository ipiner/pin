<?php

declare(strict_types=1);

namespace Pin\Cache;

use InvalidArgumentException;

/**
 * Hash 键与字段。
 */
class HashKey
{
    public function __construct(public string $key, public string $field)
    {
    }

    /**
     * 按最后一个冒号拆分缓存键。
     */
    public static function parse(string $raw): static
    {
        $position = strrpos($raw, ':');

        if ($position === false) {
            return new static($raw, '');
        }

        return new static(
            substr($raw, 0, $position),
            substr($raw, $position + 1),
        );
    }

    /**
     * 解析同一 Hash 下的缓存键。
     *
     * @return array{0: string, 1: array<int, string>}
     */
    public static function parseMany(array $keys): array
    {
        $hashKey = '';
        $fields = [];

        foreach ($keys as $raw) {
            $item = static::parse((string) $raw);

            if ($fields && $hashKey !== $item->key) {
                throw new InvalidArgumentException('Cache keys must belong to the same hash.');
            }

            $hashKey = $item->key;
            $fields[] = $item->field;
        }

        return [$hashKey, $fields];
    }
}
