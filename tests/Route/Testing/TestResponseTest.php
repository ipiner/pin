<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pin\Errors\Errors;
use Pin\Errors\IError;
use Pin\Http\ApiResponse;
use Pin\Route\Testing\TestResponse;

it('asserts response code', function (int|IError $code, int $status) {
    Route::get(
        '/',
        fn () => ApiResponse::make($code)->withStatusCode($status)->toResponse(request())
    );

    new TestResponse($this->getJson('/'))->assertCode($code, $status);
})->with([
    [Errors::DeleteFailed, 422],
    [0, 200],
]);
