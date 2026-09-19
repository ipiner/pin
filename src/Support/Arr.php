<?php

declare(strict_types=1);

namespace Pin\Support;

/**
 * 数组工具类
 */
class Arr
{
    /**
     * 递归脱敏。
     */
    public static function maskSensitive(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = Str::maskSensitive($value, (string) $key);
        }

        return $data;
    }

    /**
     * 递归合并数组。
     *
     * @param  array|bool  $array  首个数组，或是否保留数字键
     */
    public static function merge(array|bool $array, array ...$arrays): array
    {
        $preserveNumericKeys = $array === true;
        $result = is_array($array) ? $array : (array_shift($arrays) ?? []);

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (! array_key_exists($key, $result)) {
                    $result[$key] = $value;
                } elseif (! $preserveNumericKeys && is_int($key)) {
                    $result[] = $value;
                } elseif (is_array($value) && is_array($result[$key])) {
                    $result[$key] = static::merge(
                        $preserveNumericKeys,
                        $result[$key],
                        $value
                    );
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * 递归将 null 替换为空字符串。
     */
    public static function nullToEmptyString(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = static::nullToEmptyString($value);
            } elseif ($value === null) {
                $data[$key] = '';
            }
        }

        return $data;
    }

    /**
     * 将含 id 和父级字段的数组转换为树。
     *
     * @param  string  $pidKey  父级字段名
     * @param  string  $childrenKey  子节点字段名
     */
    public static function toTree(
        array $data,
        string $pidKey = 'pid',
        string $childrenKey = 'children'
    ): array {
        $groups = [];

        foreach ($data as $item) {
            $groups[$item[$pidKey]][] = $item;
        }

        return static::toTreeInternal($groups, 0, $childrenKey);
    }

    /**
     * 从父级分组递归构建子树。
     */
    protected static function toTreeInternal(
        array $groups,
        int $pid,
        string $childrenKey = 'children'
    ): array {
        $items = $groups[$pid] ?? [];

        foreach ($items as $key => $item) {
            $id = $item['id'];

            if (isset($groups[$id])) {
                $items[$key][$childrenKey] = static::toTreeInternal(
                    $groups,
                    $id,
                    $childrenKey
                );
            }
        }

        return $items;
    }
}
