<?php

declare(strict_types=1);

use Pin\Database\QueryMonitor\QueryProfile;
use Pin\Tests\Database\QueryMonitor\TestCase;

uses(TestCase::class);

it('records query events', function () {
    $profile = new QueryProfile();

    $profile->record($this->getQueryExecuted());
    $profile->record($this->getQueryExecuted(time: 10));

    expect($profile->count)->toBe(2)
        ->and($profile->time)->toBe(1010);
});

it('determines slow queries', function () {
    $profile = new QueryProfile();
    $event = $this->getQueryExecuted(time: -1);

    expect($profile->isSlow($event))->toBeFalse();

    $event->time = 2000;

    foreach ([
        4000 => false,
        10 => false,
        1000 => true,
        1 => true,
    ] as $time => $result) {
        config(['database.connections.'.$event->connectionName.'.slow_threshold' => $time]);
        expect($profile->isSlow($event))->toBe($result, "Failed for threshold $time");
    }
});
