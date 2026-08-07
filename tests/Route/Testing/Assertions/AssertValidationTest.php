<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pin\Http\ApiResponse;
use Pin\Route\Testing\TestResponse;

it('asserts invalid fields', function () {
    Route::get('/', function () {
        return ApiResponse::make(
            0,
            '',
            ['errors' => ['username' => [], 'password' => []]]
        )->toResponse(request());
    });

    new TestResponse($this->getJson('/'))
        ->assertInvalid('password, username');
});

it('asserts valid fields', function () {
    Route::get('/', function () {
        return ApiResponse::make(0)->toResponse(request());
    });

    new TestResponse($this->getJson('/'))
        ->assertValid('password, username');
});
