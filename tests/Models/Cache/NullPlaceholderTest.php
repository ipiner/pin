<?php

declare(strict_types=1);

use Pin\Models\Cache\NullPlaceholder;

it('creates a placeholder and converts to string', function () {
    $placeholder = NullPlaceholder::make(60);
    $value = $placeholder->toString();

    expect($value)->toBeString()->toEndWith('__cache:null_placeholder__');
});

it('checks if a value is a holder value', function () {
    $valid = '1713955200__cache:null_placeholder__';
    $invalid = 'some_random_value';

    expect(NullPlaceholder::isHolderValue($valid))->toBeTrue()
        ->and(NullPlaceholder::isHolderValue($invalid))->toBeFalse()
        ->and(NullPlaceholder::isHolderValue(null))->toBeFalse();
});

it('parses a valid placeholder', function () {
    $timestamp = time() + 60;
    $value = $timestamp.'__cache:null_placeholder__';
    $parsed = NullPlaceholder::parse($value);

    expect($parsed)->toBeInstanceOf(NullPlaceholder::class)
        ->and($parsed->isExpired())->toBeFalse();
});

it('returns null when parsing invalid values', function () {
    expect(NullPlaceholder::parse('invalid'))->toBeNull()
        ->and(NullPlaceholder::parse('1234567890_invalid_suffix'))->toBeNull()
        ->and(NullPlaceholder::parse(null))->toBeNull();
});

it('checks expired status', function () {
    $expired = new NullPlaceholder(time() - 10);
    $notExpired = new NullPlaceholder(time() + 10);

    expect($expired->isExpired())->toBeTrue()
        ->and($notExpired->isExpired())->toBeFalse();
});

it('parses and detects expired placeholder', function () {
    $timestamp = time() - 5;
    $value = $timestamp.'__cache:null_placeholder__';
    $parsed = NullPlaceholder::parse($value);

    expect($parsed)->toBeInstanceOf(NullPlaceholder::class)
        ->and($parsed->isExpired())->toBeTrue();
});
