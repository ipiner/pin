<?php

declare(strict_types=1);

namespace Pin\Scramble;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 创建成功响应文档资源
 */
class Created extends JsonResource
{
    /**
     * 定义创建响应结构
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => 0,
        ];
    }
}
