<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Pin\Http\ApiResponse;
use Pin\Pagination\Pagination;
use Pin\Route\Testing\TestResponse;

it('asserts paginated response', function () {
    Route::get('/', function () {
        return ApiResponse::make(
            0,
            '',
            Pagination::make(new LengthAwarePaginator(range(1, 10), 10, 5))
        )->toResponse(request());
    });

    new TestResponse($this->getJson('/'))
        ->assertPaginated(function (array $items, int $total, int $totalPage) {
            expect($items)->toHaveCount(10);
            expect($total)->toBe(10);
            expect($totalPage)->toBe(2);
        });
});
