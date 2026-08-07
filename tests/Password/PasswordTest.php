<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Pin\Password\PasswordException;
use Pin\Support\Facades\Aes;
use Pin\Support\Facades\Password;

it('checks password', function () {
    $password = strtoupper(Str::random(32));
    $salt = Str::random(8);
    $hashed = Password::hash($password, $salt);

    expect(Password::check($password, $salt, $hashed))->toBeTrue()
        ->and(Password::check($password, '', $hashed))->toBeFalse();
});

it('encodes and decodes request password', function () {
    $raw = Str::random();
    $encoded = Password::encodeToRequest($raw);

    expect(Password::decodeFromRequest($encoded))->toBe(Password::encode($raw));
    expect(fn () => Password::decodeFromRequest(Aes::encrypt($raw)))
        ->toThrow(PasswordException::class);
});

it('throws exception when request password is invalid', function () {
    expect(fn () => Password::decodeFromRequest(Str::random(32)))
        ->toThrow(PasswordException::class);
});

it('throws exception when password length is invalid', function () {
    expect(fn () => Password::hash(Str::random(), ''))->toThrow(PasswordException::class);
});

it('throws exception when password contains lowercase letters', function () {
    expect(fn () => Password::hash(strtoupper(Str::random(31)).'a', ''))
        ->toThrow(PasswordException::class);
});

it('hashes password successfully', function () {
    $password = Str::random();
    $encoded = Password::encode($password);

    expect($encoded)->toBe(strtoupper(md5(strtoupper($password))))
        ->and(Hash::check($encoded, Password::hash($encoded, '')))->toBeTrue();
});
