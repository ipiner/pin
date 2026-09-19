<?php

declare(strict_types=1);

namespace Pin\Scramble;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * 删除成功响应文档资源
 */
class Deleted extends JsonResource implements SchemaType
{
    /**
     * 删除响应数据
     *
     * @return array{deleted: bool}
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'deleted' => true,
        ];
    }
}
