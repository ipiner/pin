<?php

declare(strict_types=1);

namespace Pin\Scramble;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 下拉选项响应文档资源
 */
class SelectOption extends JsonResource
{
    /**
     * 定义选项响应结构
     */
    public function toArray(Request $request): array
    {
        return [
            'label' => '',
            // @var int|string
            'value' => 0,
        ];
    }
}
