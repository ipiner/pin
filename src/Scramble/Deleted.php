<?php

declare(strict_types=1);

namespace Pin\Scramble;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 删除成功响应文档资源
 */
class Deleted extends JsonResource
{
    /**
     * 定义删除响应结构
     */
    public function toArray(Request $request): array
    {
        return [
            'deleted' => true,
        ];
    }
}
