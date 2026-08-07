<?php

declare(strict_types=1);

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
    expect($data['items'][0])->toBeTrue();
});

class TestPaginationResource
{
    public function resolve()
    {
        return true;
    }
}
