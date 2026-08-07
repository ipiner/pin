<?php

declare(strict_types=1);

namespace Pin\Scramble\TypeToSchemaExtensions;

use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Type\ArrayItemType_;
use Dedoc\Scramble\Support\Type\Generic;
use Dedoc\Scramble\Support\Type\Type;
use Pin\Http\ApiResponse;
use Pin\Pagination\Pagination;

/**
 * 响应泛型到 OpenAPI Schema 的转换扩展
 */
class ResponseTypeToSchema extends TypeToSchemaExtension
{
    /**
     * 支持解析的数据包装类型
     */
    public const array HANDLES = [
        ApiResponse::class => 'data',
        Pagination::class => 'items',
    ];

    /**
     * 判断当前类型是否需要由该扩展处理
     */
    public function shouldHandle(Type $type)
    {
        if (! $type instanceof Generic) {
            return false;
        }

        if (! isset(static::HANDLES[$type->name])) {
            return false;
        }

        return isset($type->templateTypes[0]);
    }

    /**
     * 将响应泛型中的数据类型补入实际响应字段
     *
     * @param  Generic  $type
     */
    public function toSchema(Type $type)
    {
        $dataType = $type->templateTypes[1] ?? $type->templateTypes[0] ?? null;
        $this->infer->analyzeClass($type->value ?? $type->name);
        $array = $type->getMethodDefinition('toArray')->type->getReturnType();
        $array->items[] = new ArrayItemType_(static::HANDLES[$type->name], $dataType);

        return $this->openApiTransformer->transform($array);
    }

    /**
     * 转化为 `Dedoc\Scramble\Support\Generator\Response`
     */
    public function toResponse(Type $type)
    {
        return Response::make(200)
            ->setContent(
                'application/json',
                Schema::fromType($this->openApiTransformer->transform($type)),
            );

    }
}
