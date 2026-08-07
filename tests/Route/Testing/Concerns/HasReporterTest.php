<?php

declare(strict_types=1);

use App\Routes\User\UserRoute;
use Pin\Route\Testing\Reporter;

it('handles report request enabled', function () {
    $t = UserRoute::Index->testing($this)->withReporter(
        new class extends Reporter
        {
            public ?bool $reportRequestEnabled = true;
        }
    );

    $invoker = $this->invoker($this->invoker($t)->reporter());
    expect($invoker->reportRequestEnabled())->toBeTrue();

    $t->withReportRequestEnabled(false);
    expect($invoker->reportRequestEnabled())->toBeFalse();
});
