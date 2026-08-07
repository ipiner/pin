<?php

declare(strict_types=1);

namespace Pin\Database\Schema;

use Illuminate\Support\Str;
use Pin\Support\DataBag;

/**
 * 数据库字段 Schema DTO。
 *
 * Column 用于承载由数据库 schema driver 返回的字段结构，并为运行期 metadata
 * 提供统一的字段属性与展示名称。
 *
 * @property string $name 字段名
 * @property string|null $type_name 数据库类型名称，例如 int、varchar
 * @property string|null $type 完整类型定义，例如 varchar(255)
 * @property string|null $collation 字符集排序规则，仅字符串类型通常有效
 * @property bool $nullable 是否允许 NULL
 * @property mixed $default 默认值
 * @property bool $auto_increment 是否为自增字段
 * @property string|null $generation 生成列定义或表达式
 * @property string|null $comment 数据库字段注释
 * @property string $label 字段展示名称。优先取字段注释的第一段，缺省时由字段名生成
 */
class Column extends DataBag
{
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);
        $this->label = $this->parseLabel();
    }

    /**
     * 解析字段展示名称。
     *
     * 字段注释存在时，取 `|` 之前的内容作为 label；否则将字段名转换为自然语言格式。
     */
    protected function parseLabel(): string
    {
        return $this->comment
            ? explode('|', $this->comment)[0]
            : Str::headline($this->name);
    }
}
