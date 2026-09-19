<?php

declare(strict_types=1);

namespace Pin\Database\Schema;

use Illuminate\Support\Str;
use Pin\Support\DataBag;

/**
 * 数据库字段结构。
 *
 * @property string $name 字段名
 * @property string|null $type_name 类型名称
 * @property string|null $type 完整类型
 * @property string|null $collation 排序规则
 * @property bool $nullable 是否允许 NULL
 * @property mixed $default 默认值
 * @property bool $auto_increment 是否为自增字段
 * @property array{type: string|null, expression: string|null}|null $generation 生成列定义
 * @property string|null $comment 数据库字段注释
 * @property string $label 字段名称
 */
class Column extends DataBag
{
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);

        $this->label = $this->parseLabel();
    }

    /**
     * 解析字段名称。
     */
    protected function parseLabel(): string
    {
        if ($comment = $this->comment ?? null) {
            return explode('|', $comment, 2)[0];
        }

        return Str::headline($this->name);
    }
}
