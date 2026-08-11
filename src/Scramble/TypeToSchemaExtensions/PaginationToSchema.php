<?php

declare(strict_types=1);

namespace Pin\Scramble\TypeToSchemaExtensions;

use Dedoc\Scramble\Support\Type\ArrayType;
use Dedoc\Scramble\Support\Type\IntegerType;
use Dedoc\Scramble\Support\Type\Type;
use Override;
use Pin\Pagination\Pagination;

class PaginationToSchema extends GenericTypeToSchema
{
    #[Override]
    protected function getGenericKey(): ?string
    {
        return 'items';
    }

    #[Override]
    protected function getHandledType(): string
    {
        return Pagination::class;
    }

    #[Override]
    protected function transformTemplateType(Type $type): Type
    {
        return new ArrayType(
            $type,
            new IntegerType(),
        );
    }
}
