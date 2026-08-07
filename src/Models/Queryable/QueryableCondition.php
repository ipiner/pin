<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

/**
 * 查询条件
 */
class QueryableCondition
{
    /**
     * 查询类型
     */
    public string $type;

    /**
     * @param  string  $column  查询字段
     * @param  array|string|null  $value  查询值
     * @param  string  $type  查询类型
     */
    public function __construct(
        public string $column,
        public array|string|null $value,
        string|QueryableType $type,
    ) {
        $this->type = is_string($type) ? $type : $type->value;
    }
}
