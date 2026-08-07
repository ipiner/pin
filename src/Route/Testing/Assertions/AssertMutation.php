<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Assertions;

use Closure;
use Illuminate\Testing\Fluent\AssertableJson;

/**
 * 数据变更类断言支持。
 */
trait AssertMutation
{
    /**
     * 创建成功断言。
     *
     * @param  Closure(int, static): void|null  $assert
     */
    public function assertCreated(?Closure $assert = null): static
    {
        $this->assertSuccessful()->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', fn (int $id) => $id > 0)
            ->etc()
        );

        if ($assert) {
            $id = $this->response->json('data.id');
            $assert($id, $this);
        }

        return $this;
    }

    /**
     * 删除成功断言。
     */
    public function assertDeleted(): static
    {
        $this->assertSuccessful()->assertJsonPath('data.deleted', true);

        return $this;
    }

    /**
     * 更新成功断言。
     */
    public function assertUpdated(): static
    {
        $this->assertSuccessful()->assertJsonPath('data.updated', true);

        return $this;
    }
}
