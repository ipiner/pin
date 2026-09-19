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

it('wraps empty ciphertext failures', function () {
    expect(fn () => Aes::decrypt(''))->toThrow(CryptException::class);
});

it('keeps plaintext out of encryption error context', function () {
    config(['pin.crypt.iv' => 'short']);

    try {
        Aes::encrypt('sensitive-plaintext');
        $this->fail('Expected encryption to fail.');
    } catch (CryptException $exception) {
        expect($exception->getContext())->not->toHaveKey('plain')
            ->and($exception->getMessage())->not->toContain('sensitive-plaintext');
    }
});

it('decrypts existing payload formats', function (string $encrypted) {
    config([
        'pin.crypt.key' => '0123456789abcdef',
        'pin.crypt.iv' => 'fedcba9876543210',
    ]);

    expect(Aes::decrypt($encrypted))->toBe('兼容旧数据:0');
})->with([
    'configured key' => 'akWiG46pvxbD5tD9pWQKqLLlTd8l5kSbEfzN9Oe79nzo=',
    'random key' => 'A0123456789abcdefnI6zeT6ssi7gFejFqa6aWr5JdAG1Ha4VebNV5EDXAa4=',
]);
