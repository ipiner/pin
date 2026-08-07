<?php

declare(strict_types=1);

namespace Pin\Support;

/**
 * 数组工具类
 */
class Arr
{
    /**
     * 递归遍历数组，根据字段名自动识别敏感信息并进行脱敏处理
     *
     * @param  array  $data  要处理的数组
     * @return array 返回脱敏后的数组
     */
    public static function maskSensitive(array $data): array
    {
        foreach ($data as $key => $value) {
            // 根据字段名进行脱敏处理
            $data[$key] = Str::maskSensitive($value, (string) $key);
        }

        return $data;
    }

    /**
     * 深度递归合并子数组
     *
     * 可选择是否保留数字键名
     *
     * @param  array|bool  $array
     *
     * - array：第一个需要合并的数组
     * - true：开启“保留数字键名”模式
     * @param  array  ...$arrays  其余需要合并的数组
     * @return array 合并后的数组
     */
    public static function merge(array|bool $array, array ...$arrays): array
    {
        $preserveNumericKeys = false;

        // 当第一个参数为 true，表示开启“保留数字键”
        if ($array === true) {
            $preserveNumericKeys = true;
        } elseif ($array !== false) {
            array_unshift($arrays, $array);
        }

        $result = array_shift($arrays);

        while (! empty($arrays)) {
            $array = array_shift($arrays);

            foreach ($array as $key => $value) {
                // 不存在该 key，直接赋值
                if (! array_key_exists($key, $result)) {
                    $result[$key] = $value;

                    // 数字键处理
                } elseif (! $preserveNumericKeys && is_int($key)) {
                    $result[] = $value;

                    // 子数组递归合并
                } elseif (is_array($value) && is_array($result[$key])) {
                    $result[$key] = static::merge(
                        $preserveNumericKeys,
                        $result[$key],
                        $value
                    );

                    // 其他情况直接覆盖
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * 递归遍历数组，将所有 `null` 值替换为 `''`
     */
    public static function nullToEmptyString(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // 递归处理子数组
                $data[$key] = static::nullToEmptyString($value);
            } elseif ($value === null) {
                $data[$key] = '';
            }
        }

        return $data;
    }

    /**
     * 将扁平数组转换为树结构数组
     *
     * 要求数据结构：
     * - 每个元素必须包含 id 和 pid
     *
     * @param  array  $data  原始数组
     * @param  string  $pidKey  父级字段名
     * @param  string  $childrenKey  子节点字段名
     */
    public static function toTree(
        array $data,
        string $pidKey = 'pid',
        string $childrenKey = 'children'
    ): array {
        $groups = [];

        // 按 pid 分组
        foreach ($data as $item) {
            $groups[$item[$pidKey]][] = $item;
        }

        // 从根节点（pid = 0）开始构建树
        return static::toTreeInternal($groups, 0, $childrenKey);
    }

    /**
     * 内部递归构建树结构
     *
     * @param  array  $groups  分组后的数据
     * @param  int  $pid  当前父节点 ID
     * @param  string  $childrenKey  子节点字段名
     */
    protected static function toTreeInternal(
        array $groups,
        int $pid,
        string $childrenKey = 'children'
    ): array {
        $items = $groups[$pid] ?? [];

        foreach ($items as $key => $item) {
            $id = $item['id'];

            // 如果当前节点存在子节点，递归构建
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
