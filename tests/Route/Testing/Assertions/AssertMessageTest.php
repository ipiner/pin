<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pin\Http\ApiResponse;
use Pin\Route\Testing\TestResponse;

it('asserts response messages', function () {
    Route::get('/', function () {
        return ApiResponse::make(0, 'Created successful')->toResponse(request());
    });

    $resp = new TestResponse($this->getJson('/'));

    $resp->assertMessage('Created successful');
    $resp->assertMessageContains('success');
    $resp->assertMessageContains('SUCCESS', false);
    $resp->assertMessageMatch('/success/i');
    $resp->assertMessageUsing(fn (string $message) => $message !== 'Success');

    expect(true)->toBeTrue();
});
