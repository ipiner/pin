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

    $json = json_decode((new JsonFormatter())->format($record));
    expect($json->level)
        ->toBe('INFO')
        ->and($json->level_code)
        ->toBe(Level::Info->value);
});

it('formats batches with the same fields as individual records', function () {
    $formatter = new JsonFormatter();
    $records = [
        new LogRecord(new DateTimeImmutable(), 'app', Level::Info, 'first'),
        new LogRecord(new DateTimeImmutable(), 'app', Level::Error, 'second'),
    ];
    $batch = json_decode($formatter->formatBatch($records), true, flags: JSON_THROW_ON_ERROR);

    foreach ($records as $index => $record) {
        expect($batch[$index])->toBe(json_decode($formatter->format($record), true))
            ->and(array_key_first($batch[$index]))->toBe('datetime')
            ->and($batch[$index]['level'])->toBe($record->level->getName())
            ->and($batch[$index]['level_code'])->toBe($record->level->value)
            ->and($batch[$index])->not->toHaveKey('level_name');
    }
});

it('formats empty batches', function () {
    expect((new JsonFormatter())->formatBatch([]))->toBe('[]');
});

it('normalizes values in exception context', function () {
    $exception = (new Exception('failed'))->withContext([
        'created_at' => new DateTimeImmutable('2026-09-20 12:00:00'),
        'cause' => new RuntimeException('context failure'),
    ]);
    $data = (new JsonFormatter())->normalizeValue($exception);

    expect($data['context']['created_at'])->toBe('2026-09-20 12:00:00')
        ->and($data['context']['cause']['message'])->toBe('context failure');
});

it('limits recursive exception context', function () {
    $exception = new Exception('failed');
    $exception->withContext(['self' => $exception]);
    $formatter = (new JsonFormatter())->setMaxNormalizeDepth(2);
    $data = $formatter->normalizeValue($exception);

    expect($data['context']['self']['context'])
        ->toBe('Over 2 levels deep, aborting normalization');
});

it('limits previous exceptions at the configured depth', function () {
    $exception = new Exception('failed', previous: new RuntimeException('previous'));
    $formatter = (new JsonFormatter())->setMaxNormalizeDepth(0);
    $data = $formatter->normalizeValue($exception);

    expect($data['previous'])->toBe([
        'message' => 'Over 0 levels deep, aborting normalization',
    ]);
});
