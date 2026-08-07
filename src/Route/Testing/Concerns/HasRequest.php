<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Concerns;

use Closure;
use Pin\Models\Model;
use Pin\Route\Testing\TestResponse;
use Pin\Support\Json;

/**
 * HTTP 请求支持
 */
trait HasRequest
{
    /**
     * 当前请求的路由参数
     *
     * @var array<string, int|string>
     */
    protected array $routeParams = [];

    /**
     * 资源创建断言
     *
     * @param  Closure|null  $assert  创建成功后的自定义断言
     */
    public function created(
        ?Closure $assert = null
    ): TestResponse {
        $payload = $this->payload ?? $this->action()->fakeData();

        return $this->json($payload)->assertCreated(
            function (int $id) use ($assert) {
                $model = $this->modelClass::find($id);
                $this->testCase->assertNotNull($model);
                if ($assert) {
                    $assert($model);
                }
            }
        );
    }

    /**
     * 资源删除断言
     *
     * @param  Closure|Model|int|null  $id  删除后的自定义断言 或 Model 实例或 ID，`null` 时自动创建模型
     * @param  Closure(Model): void|null  $assert  删除后的自定义断言
     */
    public function deleted(
        Closure|Model|int|null $id = null,
        ?Closure $assert = null
    ): TestResponse {
        if ($id instanceof Closure) {
            $assert = $id;
            $id = null;
        }
        $model = $this->findModel($id);
        $this->testCase->assertNotNull($model);
        $this->testCase->assertTrue($model->exists);

        $resp = $this->withRouteParams(['id' => $model->id])
            ->json()
            ->assertDeleted();
        $this->testCase->assertNull($this->findModel($model->id));
        $model->exists = false;
        $this->testCase->assertFalse($model->exists);
        if ($assert) {
            $assert($model);
        }

        return $resp;
    }

    /**
     * 执行 JSON 请求测试
     *
     * @param  array<string, mixed>|null  $payload  请求数据
     * @param  array<string, string>  $headers  自定义请求头
     * @return TestResponse 包装后的响应对象
     */
    public function json(?array $payload = null, array $headers = []): TestResponse
    {
        $payload = (array) ($payload ?? $this->payload);
        if ($this->isRead()) {
            $routeParams = [
                ...$payload,
                ...$this->routeParams,
            ];
            $payload = [];
        } else {
            $routeParams = $this->routeParams;
        }

        $uri = $this->route->route($routeParams, false);
        $resp = $this->testCase->json(
            $this->route->definition()->method,
            $uri,
            $payload,
            $headers,
            Json::DEFAULT_ENCODE_OPTIONS
        );
        $resp = new TestResponse($resp);

        $this->reporter()->reportRequest($this->route, $uri, $resp);

        return $resp;
    }

    /**
     * 分页响应断言
     *
     * @param  Closure(array, int, int): void|null  $assert
     */
    public function paginated(?Closure $assert = null): TestResponse
    {
        return $this->json()->assertPaginated(
            function (array $items, int $total, int $totalPage) use ($assert) {
                if ($assert) {
                    $assert($items, $total, $totalPage);
                }
            }
        );
    }

    /**
     * 一般成功断言
     */
    public function successful(): TestResponse
    {
        return $this->json()->assertSuccessful();
    }

    /**
     * 资源更新断言
     *
     * @param  Closure|Model|int|null  $id  更新后的自定义断言 或 Model 实例或 ID，`null` 时自动创建模型
     * @param  Closure(Model): void|null  $assert  更新后的自定义断言
     */
    public function updated(
        Closure|Model|int|null $id = null,
        ?Closure $assert = null
    ): TestResponse {
        if ($id instanceof Closure) {
            $assert = $id;
            $id = null;
        }
        $model = $this->findModel($id);
        $this->testCase->assertNotNull($model);

        $payload = $this->payload ?? $this->action()->fakeData();
        // 数据版本号
        if (isset($payload['v'])) {
            $payload['v'] = $model->v ?? 1;
        }

        $resp = $this->withRouteParams(['id' => $model->id])
            ->json($payload)
            ->assertUpdated();
        $model = $this->modelClass::find($model->id);
        $key = array_key_first($payload);
        if ($key && is_scalar($model->{$key})) {
            $this->testCase->assertSame($model->{$key}, $payload[$key]);
        }

        if ($assert) {
            $assert($model);
        }

        return $resp;
    }

    /**
     * 设置路由参数
     *
     * @param  array<string, int|string>  $routeParams  路由参数
     * @return $this
     */
    public function withRouteParams(array $routeParams): static
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    /**
     * 判断当前请求是否为 Read 请求
     */
    protected function isRead(): bool
    {
        return in_array(
            $this->route->definition()->method,
            ['GET', 'HEAD', 'OPTIONS']
        );
    }
}
