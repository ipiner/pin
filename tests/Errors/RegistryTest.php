<?php

declare(strict_types=1);

use Pin\Errors\Errors;
use Pin\Errors\Registry;
use Pin\Tests\Errors\Errors as TestsErrors;
use Pin\Tests\Errors\Fixtures\LoadErrors;
use Pin\Tests\Errors\OverrideErrors;

class_exists(TestsErrors::class);

beforeEach(function () {
    $this->registeredErrors = Registry::all();
});

afterEach(function () {
    $this->invoker(Registry::class)->errors = $this->registeredErrors;
});

it('returns unknown error when code does not exist', function () {
    expect(Registry::get(time())->code())->toBe(Errors::Unknown->code());
});

it('loads error enums and skips unrelated enums', function () {
    expect(Registry::load('xxxx'))->toBeFalse()
        ->and(Registry::load(__DIR__.'/Fixtures', 'Pin\\Tests\\Errors\\Fixtures'))->toBeTrue()
        ->and(Registry::get(-20))->toBe(LoadErrors::Failed)
        ->and(LoadErrors::Failed->message())->toBe('Failed');
});

it('uses an overridden unknown error as the fallback', function () {
    Registry::register([OverrideErrors::Unknown]);

    expect(Registry::get(-999))->toBe(OverrideErrors::Unknown)
        ->and(Errors::getMessage(-999, ['name' => 'Alice']))->toBe('Unknown Alice');
});
