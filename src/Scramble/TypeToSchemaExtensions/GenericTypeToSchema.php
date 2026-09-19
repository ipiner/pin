<?php

declare(strict_types=1);

namespace Pin\Scramble\TypeToSchemaExtensions;

use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use Dedoc\Scramble\Support\Generator\Types\Type as OpenApiType;
use Dedoc\Scramble\Support\Type\Generic;
use Dedoc\Scramble\Support\Type\KeyedArrayType;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Dedoc\Scramble\Support\Type\TypeWalker;
use Override;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use Pin\Scramble\SchemaType;

/**
 * 泛型响应 Schema 转换
 */
abstract class GenericTypeToSchema extends TypeToSchemaExtension
{
    /**
     * 获取泛型对应字段
     */
    abstract protected function getGenericKey(): ?string;

    /**
     * 获取目标类型
     *
     * @return class-string
     */
    abstract protected function getHandledType(): string;

    /**
     * 判断是否处理当前类型
     */
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof Generic
            && $type->isInstanceOf($this->getHandledType())
            && $this->getTemplateType($type) !== null;
    }

    /**
     * 转换响应 Schema
     *
     * @param  Generic  $type
     */
    #[Override]
    public function toSchema(Type $type): OpenApiType
    {
        return $this->openApiTransformer->transform($this->resolveReturnType($type));
    }

    /**
     * 分析嵌套文档资源
     */
    protected function analyzeSchemaType(Type $type): void
    {
        new TypeWalker()->walk($type, function (Type $type) {
            if ($type instanceof ObjectType && $type->isInstanceOf(SchemaType::class)) {
                $this->infer->analyzeClass($type->name);
            }
        });
    }

    /**
     * 获取泛型参数
     */
    protected function getTemplateType(Generic $type): ?Type
    {
        return $type->templateTypes[1]
            ?? $type->templateTypes[0]
            ?? null;
    }

    /**
     * 解析泛型返回类型
     */
    protected function resolveReturnType(Generic $type): Type
    {
        $returnType = clone $type->getMethodDefinition('toArray')->getReturnType();

        if (! $returnType instanceof KeyedArrayType) {
            return $returnType;
        }

        $genericKey = $this->getGenericKey();
        $templateType = $this->getTemplateType($type);

        if ($genericKey === null || ! $templateType) {
            return $returnType;
        }

        foreach ($returnType->items as $index => $item) {
            if ($item->key !== $genericKey) {
                continue;
            }

            $this->analyzeSchemaType($templateType);
            $item = clone $item;
            $item->value = $this->transformTemplateType($templateType);
            if ($doc = $item->getAttribute('docNode')) {
                $item->setAttribute('docNode', $this->resolveFieldDoc($doc));
            }
            $returnType->items[$index] = $item;

            break;
        }

        return $returnType;
    }

    /**
     * 保留字段说明，移除泛型类型声明
     */
    protected function resolveFieldDoc(PhpDocNode $doc): PhpDocNode
    {
        $doc = clone $doc;

        foreach ($doc->children as $index => $node) {
            if ($node instanceof PhpDocTagNode && $node->name === '@var') {
                $doc->children[$index] = new PhpDocTextNode($node->value->description);
            }
        }

        return $doc;
    }

    /**
     * 转换泛型字段类型
     */
    protected function transformTemplateType(Type $type): Type
    {
        return $type;
    }
}
