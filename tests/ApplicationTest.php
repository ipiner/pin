<?php

declare(strict_types=1);

use Illuminate\Support\Str;

it('gets request id', function () {
    $id = $this->app->getRequestId();

    expect(Str::isUuid($id))->toBeTrue();
});

it('detects debug mode', function () {
    expect($this->app->hasDebugModeEnabled())->toBeFalse();

    config(['app.debug' => true]);
    expect($this->app->hasDebugModeEnabled())->toBeTrue();
});

it('detects running in http mode', function () {
    expect($this->app->runningInHttp())->toBeFalse();
});
