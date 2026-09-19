<?php

declare(strict_types=1);

namespace Pin\Scramble\TypeToSchemaExtensions;

use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Type\Type;
use Override;
use Pin\Http\ApiResponse;

/**
 * API 响应 Schema
 */
class ApiResponseToSchema extends GenericTypeToSchema
{
    /**
     * 生成 JSON 响应
     */
    #[Override]
    public function toResponse(Type $type): Response
    {
        return Response::make(200)
            ->setContent(
                'application/json',
                Schema::fromType($this->openApiTransformer->transform($type))
            );
    }

    #[Override]
    protected function getGenericKey(): ?string
    {
        return 'data';
    }

    #[Override]
    protected function getHandledType(): string
    {
        return ApiResponse::class;
    }
}
