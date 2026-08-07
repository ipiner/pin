<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Assertions;

use Closure;

/**
 * 分页响应断言支持。
 *
 * 为 TestResponse 提供统一的分页响应断言能力。
 */
trait AssertPagination
{
    /**
     * 分页响应断言。
     *
     * @param  Closure(array, int, int): void|null  $assert
     */
    public function assertPaginated(?Closure $assert = null): static
    {
        $this->assertSuccessful()->assertJsonStructure([
            'data' => [
                'total',
                'total_page',
                'items',
            ],
        ]);

        if ($assert) {
            $data = $this->response->json('data');
            $assert($data['items'], $data['total'], $data['total_page']);
        }

        return $this;
    }
}
