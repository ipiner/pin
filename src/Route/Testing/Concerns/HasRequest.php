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
     * @param  Closure(Model): void|null  $assert
     */
    public function created(?Closure $assert = null): TestResponse
    {
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
     * @param  Closure|Model|int|null  $id  模型、ID 或回调；null 时创建模型
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

        $response = $this->withRouteParams([...$this->routeParams, 'id' => $model->id])
            ->json()
            ->assertDeleted();
        $this->testCase->assertNull($this->findModel($model->id));
        $model->exists = false;

        if ($assert) {
            $assert($model);
        }

        return $response;
    }

    /**
     * 执行 JSON 请求测试
     *
     * @param  array<string, mixed>|null  $payload  请求数据
     * @param  array<string, string>  $headers  自定义请求头
     */
    public function json(?array $payload = null, array $headers = []): TestResponse
    {
        $payload ??= $this->payload ?? [];
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
        $response = new TestResponse($this->testCase->json(
            $this->route->definition()->method,
            $uri,
            $payload,
            $headers,
            Json::DEFAULT_ENCODE_OPTIONS
        ));

        $this->reporter()->reportRequest($this->route, $uri, $response);

        return $response;
    }

    /**
     * 分页响应断言
     *
     * @param  Closure(array, int, int): void|null  $assert
     */
    public function paginated(?Closure $assert = null): TestResponse
    {
        return $this->json()->assertPaginated($assert);
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
     * @param  Closure|Model|int|null  $id  模型、ID 或回调；null 时创建模型
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
        if (isset($payload['v'])) {
            $payload['v'] = $model->v ?? 1;
        }

        $response = $this->withRouteParams([...$this->routeParams, 'id' => $model->id])
            ->json($payload)
            ->assertUpdated();
        $model = $this->modelClass::find($model->id);
        $this->testCase->assertNotNull($model);

        $key = array_key_first($payload);
        if ($key && is_scalar($model->{$key})) {
            $this->testCase->assertSame($payload[$key], $model->{$key});
        }

        if ($assert) {
            $assert($model);
        }

        return $response;
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
     * 判断是否为读取请求
     */
    protected function isRead(): bool
    {
        return in_array($this->route->definition()->method, ['GET', 'HEAD', 'OPTIONS'], true);
    }
}
