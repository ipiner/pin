<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pin\Http\ApiResponse;
use Pin\Route\Testing\TestResponse;

it('asserts created response and passes created id to callback', function () {
    Route::get('/create', function () {
        return ApiResponse::make(0, '', ['id' => 1])->toResponse(request());
    });

    new TestResponse($this->getJson('/create'))
        ->assertCreated(fn (int $id) => expect($id)->toBe(1));
});

it('asserts deleted response', function () {
    Route::get('/delete', function () {
        return ApiResponse::make(0, '', ['deleted' => true])->toResponse(request());
    });

    new TestResponse($this->getJson('/delete'))->assertDeleted();
});

it('asserts updated response', function () {
    Route::get('/update', function () {
        return ApiResponse::make(0, '', ['updated' => true])->toResponse(request());
    });

    new TestResponse($this->getJson('/update'))->assertUpdated();
});
