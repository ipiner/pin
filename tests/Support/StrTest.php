<?php

declare(strict_types=1);

use Pin\Errors\Errors;
use Pin\Support\Str;

afterAll(function () {
    Str::setSensitiveValueMasker(null);
});

it('explodes strings', function () {
    expect(Str::explode(null))->toBe([])
        ->and(Str::explode(' '))->toBe([])
        ->and(Str::explode(' foo, bar ,'))->toBe(['foo', 'bar'])
        ->and(Str::explode(' foo; bar ;', ';'))->toBe(['foo', 'bar']);
});

it('explodes strings to integers', function () {
    expect(Str::explodeToIntegers(null))->toBe([])
        ->and(Str::explodeToIntegers('123'))->toBe([123])
        ->and(Str::explodeToIntegers('1a,2'))->toBe([1, 2])
        ->and(Str::explodeToIntegers('a,2'))->toBe([0, 2]);
});

it('formats strings with placeholders', function ($expected, $str, $delimiter) {
    $replacement = ['foo' => 'foo', 'bar' => 'bar'];
    expect(Str::format($str, $replacement, $delimiter))
        ->toBe($expected);
})->with([
    ['hello foo bar', 'hello :foo :bar', ':'],
    ['hello foo bar', 'hello {foo} {bar}', '{}'],
    ['hello foo bar', 'hello %foo% %bar%', '%%'],
    ['hello {foo} {bar}', 'hello {foo} {bar}', '%%'],
]);

it('checks if a string is valid UTF-8', function () {
    expect(Str::isValidUtf8(''))->toBeTrue()
        ->and(Str::isValidUtf8(Illuminate\Support\Str::password()))->toBeTrue()
        ->and(Str::isValidUtf8('中'[0]))->toBeFalse();
});

it('masks sensitive values', function () {
    expect(Str::maskSensitive(['user' => 'test', 'password' => 'test123']))
        ->toBe(['user' => 'test', 'password' => 'tes******']);

    expect(Str::maskSensitive('test123'))->toBe('tes******');
    expect(Str::maskSensitive('test123', 'password'))->toBe('tes******');
    expect(Str::maskSensitive('test123', 'PASSWORD'))->toBe('tes******');
    expect(Str::maskSensitive('test123', 'foo'))->toBe('test123');

    Str::setSensitiveValueMasker(fn ($value, $key) => substr($value, 0, 3).'......');
    expect(Str::maskSensitive('test123', 'password'))->toBe('tes......');
    expect(Str::maskSensitive('test123', 'foo'))->toBe('tes......');
});

it('converts values to string', function () {
    $cases = [
        ['str', 'str'],
        [1, '1'],
        [Errors::Success, '0|success'],
        [Illuminate\Support\Str::of('str'), 'str'],
    ];

    foreach ($cases as $case) {
        expect(Str::string($case[0]))->toBe($case[1]);
    }
});
