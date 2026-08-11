<?php

declare(strict_types=1);

namespace Pin\Scramble\TypeToSchemaExtensions;

use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Type\Type;
use Pin\Http\ApiResponse;

class ApiResponseToSchema extends GenericTypeToSchema
{
    #[\Override]
    public function toResponse(Type $type)
    {
        return Response::make(200)
            ->setContent(
                'application/json',
                Schema::fromType($this->openApiTransformer->transform($type)),
            );

    }

    #[\Override]
    protected function getGenericKey(): ?string
    {
        return 'data';
    }

    #[\Override]
    protected function getHandledType(): string
    {
        return ApiResponse::class;
    }
}
