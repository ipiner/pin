<?php

declare(strict_types=1);

namespace Pin\Scramble;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * 创建成功响应文档资源
 */
class Created extends JsonResource implements SchemaType
{
    /**
     * 创建响应数据
     *
     * @return array{id: int}
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => 0,
        ];
    }
}
