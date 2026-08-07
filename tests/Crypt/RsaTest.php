<?php

declare(strict_types=1);

use Pin\Crypt\CryptException;
use Pin\Support\Facades\Rsa;

it('encrypts and decrypts', function () {
    $str = uniqid();

    // 普通加密/解密
    expect(Rsa::decrypt(Rsa::encrypt($str)))->toBe($str);
    // 空字符串加密/解密
    expect(Rsa::decrypt(Rsa::encrypt('')))->toBe('');
});

it('throws exception when encrypting with invalid public key', function () {
    expect(fn () => Rsa::encrypt('中', 'invalid-public-key'))
        ->toThrow(CryptException::class);
});

it('throws exception when decrypting invalid content', function () {
    expect(fn () => Rsa::decrypt('中'))->toThrow(CryptException::class);
});

it('signs and verifies', function () {
    $str = uniqid();
    $signature = Rsa::sign($str);

    // 验证签名
    expect(Rsa::verify($str, $signature))->toBeTrue();

    // 验证失败情况
    expect(Rsa::verify(uniqid(), $signature))->toBeFalse()
        ->and(Rsa::verify($str, uniqid()))->toBeFalse();
});

it('throws exception when signing with invalid private key', function () {
    expect(fn () => Rsa::sign('中', 'invalid-primary-key'))
        ->toThrow(CryptException::class);
});
