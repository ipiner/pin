<?php

declare(strict_types=1);

use Pin\Crypt\CryptException;
use Pin\Support\Facades\Aes;

it('encrypts and decrypts', function () {
    $str = uniqid();

    // 普通加密/解密
    expect(Aes::decrypt(Aes::encrypt($str)))->toBe($str);
    // 随机 key
    expect(Aes::decrypt(Aes::encrypt($str, true)))->toBe($str);
    // 空字符串
    expect(Aes::decrypt(Aes::encrypt('')))->toBe('');
});

it('throws exception when decrypting invalid content', function () {
    expect(fn () => Aes::decrypt('中'[0]))->toThrow(CryptException::class);
});

it('throws exception when encrypting with invalid key', function () {
    // 模拟配置返回错误长度 key
    config()->set('pin.crypt.key', 'short');
    config()->set('pin.crypt.iv', 'short');

    expect(fn () => Aes::encrypt(uniqid()))->toThrow(CryptException::class);
});
