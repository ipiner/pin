<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Pin\Pagination\Pagination;

it('gets available page sizes', function () {
    expect(Pagination::getAvailablePageSizes())->toBe([15, 30, 50, 100]);

    config(['pin.pagination.available_page_sizes' => [10, 20]]);
    expect(Pagination::getAvailablePageSizes())->toBe([10, 20]);
});

it('gets page size', function () {
    expect(Pagination::getPageSize())->toBe(15);

    $this->app->request->query->set('page_size', 123);
    expect(Pagination::getPageSize())->toBe(15);

    config(['pin.pagination.available_page_sizes' => []]);
    expect(Pagination::getPageSize())->toBe(123);

    config(['pin.pagination.available_page_sizes' => [10, 20]]);
    config(['pin.pagination.page_size_name' => 'per-page']);
    $this->app->request->query->set('per-page', 123);
    expect(Pagination::getPageSize())->toBe(15);

    $this->app->request->query->set('per-page', 20);
    expect(Pagination::getPageSize())->toBe(20);
});

it('accepts a numeric default page size from configuration', function () {
    config(['pin.pagination.default_page_size' => '20']);

    expect(Pagination::getDefaultPageSize())->toBe(20)
        ->and(Pagination::getPageSize())->toBe(20);
});

it('uses the explicit page size before request input', function () {
    $this->app['request']->query->set('page_size', 30);

    expect(Pagination::getPageSize(50))->toBe(50)
        ->and(Pagination::getPageSize(0))->toBe(15);
});

it('uses the default for nonpositive page sizes', function (int $pageSize) {
    config(['pin.pagination.available_page_sizes' => [0, -1, 15]]);

    expect(Pagination::getPageSize($pageSize))->toBe(15);
})->with([0, -1]);

it('does not read items when only pagination metadata is requested', function () {
    $paginator = new class([], 21, 10) extends LengthAwarePaginator
    {
        public function items(): array
        {
            throw new LogicException('Items should not be read.');
        }
    };

    expect(Pagination::make($paginator)->toArray(false))->toBe([
        'total' => 21,
        'total_page' => 3,
        'items' => null,
    ]);
});

it('reports zero pages for an empty result', function () {
    expect(Pagination::make(new LengthAwarePaginator([], 0, 15))->toArray())->toBe([
        'total' => 0,
        'total_page' => 0,
        'items' => [],
    ]);
});

it('serializes to json', function () {
    $pagination = Pagination::make(
        new LengthAwarePaginator([1], 21, 10)
    );
    expect(json_encode($pagination))->toContain('"total_page":3');
});

it('converts to array', function () {
    $pagination = Pagination::make(
        new LengthAwarePaginator([1], 21, 10)
    );

    $data = $pagination->toArray();
    expect(is_array($data['items']))->toBeTrue()
        ->and($data['total_page'])->toBe(3);

    $data = $pagination->toArray(false);
    expect($data['items'])->toBeNull()
        ->and($pagination->toArray(fn () => true)['items'])->toBeTrue();

    $data = $pagination->toArray(TestPaginationResource::class);
    expect($data['items'])->toBe([['id' => 1]]);
});

it('passes items and their keys to the callback', function () {
    $pagination = Pagination::make(new LengthAwarePaginator(['first' => 1], 1, 15));

    expect($pagination->toArray(fn (array $items) => ['received' => $items])['items'])
        ->toBe(['received' => ['first' => 1]]);
});

class TestPaginationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->resource];
    }
}
