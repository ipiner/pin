<?php

declare(strict_types=1);

namespace Pin\Scramble;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 更新成功响应文档资源
 */
class Updated extends JsonResource
{
    /**
     * 定义更新响应结构
     */
    public function toArray(Request $request): array
    {
        return [
            'updated' => true,
            /** @var int|null */
            'v' => 0,

        ];
    }
}
