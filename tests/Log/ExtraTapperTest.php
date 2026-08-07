<?php

declare(strict_types=1);

use Monolog\Logger;
use Pin\Log\ExtraProcessor;
use Pin\Log\ExtraTapper;

it('pushes injected processor into logger', function () {

    $processor = new ExtraProcessor();

    $monolog = Mockery::mock(Logger::class);
    $monolog->shouldReceive('pushProcessor')
        ->once()
        ->with($processor);

    $logger = Mockery::mock(Illuminate\Log\Logger::class);
    $logger->shouldReceive('getLogger')
        ->andReturn($monolog);

    (new ExtraTapper($processor))($logger);
});
