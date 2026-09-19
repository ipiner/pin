<?php

declare(strict_types=1);

namespace Pin\Scramble;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * 下拉选项响应文档资源
 */
class SelectOption extends JsonResource implements SchemaType
{
    /**
     * 选项响应数据
     *
     * @return array{label: string, value: int|string}
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'label' => '',
            /** @var int|string */
            'value' => 0,
        ];
    }
}
