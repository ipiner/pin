<?php

declare(strict_types=1);

namespace Pin\Scramble;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * 更新成功响应文档资源
 */
class Updated extends JsonResource implements SchemaType
{
    /**
     * 更新响应数据
     *
     * @return array{updated: bool, v: int|null}
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'updated' => true,
            /** @var int|null */
            'v' => 0,
        ];
    }
}
