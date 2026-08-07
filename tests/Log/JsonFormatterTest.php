<?php

declare(strict_types=1);

use Monolog\Level;
use Monolog\LogRecord;
use Pin\Exceptions\Exception;
use Pin\Log\JsonFormatter;

it('formats log records', function () {
    $record = new LogRecord(
        new DateTimeImmutable(),
        'channel',
        Level::Info,
        'message'
    );

    $str = (new JsonFormatter())->format($record);
    expect(substr_count($str, "\n") > 1)->toBeFalse();

    $str = (new JsonFormatter(
        addJsonEncodeOption: JSON_PRETTY_PRINT
    ))->format($record);

    expect(substr_count($str, "\n") > 1)->toBeTrue();

    config(['pin.logging.json_pretty_print' => true]);
    $str = (new JsonFormatter())->format($record);
    expect(substr_count($str, "\n") > 1)->toBeTrue();
});

it('normalizes throwable exceptions', function () {
    $formatter = new JsonFormatter();
    $invoker = $this->invoker($formatter);

    $e = (new Exception(
        previous: new LogicException('previous message')
    ))->withContext([
        'foo' => 'bar',
    ]);

    $data = $invoker->normalizeException($e);

    expect($data)
        ->toHaveKey('previous')
        ->and($data)
        ->toHaveKey('context')
        ->and($data)
        ->not->toHaveKey('trace');

    // enabled trace
    config(['pin.logging.stack_trace.enabled' => true]);
    $data = $invoker->normalizeException($e);
    expect($data['previous']['message'])->toBe('previous message');

    // max depth
    $formatter->setMaxNormalizeDepth(-1);
    $data = $invoker->normalizeException($e);

    expect($data['previous']['message'])
        ->toBe('Over -1 levels deep, aborting normalization');

    $data = $invoker->normalizeException(
        new LogicException()
    );
    expect($data)
        ->not->toHaveKey('previous')
        ->and($data)
        ->not->toHaveKey('context')
        ->and($data)
        ->toHaveKey('trace');
});

it('converts records to json', function () {
    $record = new LogRecord(
        new DateTimeImmutable(),
        'channel',
        Level::Info,
        ''
    );

    $json = json_decode(
        $this->invoker(new JsonFormatter())->toJson($record->toArray())
    );
    expect($json->level)
        ->toBe('INFO')
        ->and($json->level_code)
        ->toBe(Level::Info->value);
});
