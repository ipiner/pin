<?php

declare(strict_types=1);

namespace Pin\Scramble\TypeToSchemaExtensions;

use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use Dedoc\Scramble\Support\Type\ArrayItemType_;
use Dedoc\Scramble\Support\Type\ArrayType;
use Dedoc\Scramble\Support\Type\Generic;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Override;
use Pin\Scramble\SchemaType;
use Throwable;

/**
 * 泛型响应类型到 OpenAPI Schema 的转换扩展。
 */
abstract class GenericTypeToSchema extends TypeToSchemaExtension
{
    /**
     * 获取泛型对应的返回字段。
     */
    abstract protected function getGenericKey(): ?string;

    /**
     * 获取当前扩展处理的类型。
     */
    abstract protected function getHandledType(): string;

    /**
     * 判断当前类型是否包含泛型参数。
     */
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof Generic
            && $this->getTemplateType($type) !== null
            && $type->isInstanceOf($this->getHandledType());
    }

    #[Override]
    public function toSchema(Type $type)
    {
        return $this->openApiTransformer->transform(
            $this->resolveReturnType(/** @var Generic $type */ $type)
        );
    }

    /**
     * 分析需要生成 Schema 的类型。
     */
    protected function analyzeSchemaType(Type $type): void
    {
        try {
            if ($type instanceof ObjectType && $type->name instanceof SchemaType) {
                $this->infer->analyzeClass($type->name);

                return;
            }

            if ($type instanceof ArrayType) {
                $this->analyzeSchemaType($type->value);
            }
        } catch (Throwable) {
            //
        }
    }

    /**
     * 获取泛型类型。
     */
    protected function getTemplateType(Generic $type): ?Type
    {
        return $type->templateTypes[1]
            ?? $type->templateTypes[0]
            ?? null;
    }

    /**
     * 解析泛型返回类型。
     */
    protected function resolveReturnType(Generic $type): Type
    {
        $returnType = clone $type
            ->getMethodDefinition('toArray')
            ->type
            ->getReturnType();

        $genericKey = $this->getGenericKey();
        $dataType = $this->getTemplateType($type);

        if ($genericKey === null || $dataType === null) {
            return $returnType;
        }

        $this->analyzeSchemaType($dataType);

        $returnType->items = array_map(
            fn (ArrayItemType_ $item) => $item->key === $genericKey
                ? new ArrayItemType_($genericKey, $this->transformTemplateType($dataType))
                : $item,
            $returnType->items,
        );

        return $returnType;
    }

    /**
     * 转换泛型类型。
     */
    protected function transformTemplateType(Type $type): Type
    {
        return $type;
    }
}
