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

it('wraps invalid private key failures during decryption', function () {
    $encrypted = Rsa::encrypt('payload');

    expect(fn () => Rsa::decrypt($encrypted, 'invalid-private-key'))
        ->toThrow(CryptException::class);
});

it('rejects invalid base64 ciphertext', function () {
    $encrypted = Rsa::encrypt('payload');

    expect(fn () => Rsa::decrypt($encrypted.'!'))->toThrow(CryptException::class);
});

it('rejects invalid base64 signatures', function () {
    $signature = Rsa::sign('payload');

    expect(Rsa::verify('payload', $signature.'!'))->toBeFalse();
});

it('reports encryption failures when PHP warnings are suppressed', function () {
    expect(fn () => @Rsa::encrypt(str_repeat('x', 1024)))
        ->toThrow(CryptException::class);
});

it('reports signing failures when PHP warnings are suppressed', function () {
    expect(fn () => @Rsa::sign('payload', 'invalid-private-key'))
        ->toThrow(CryptException::class);
});

it('keeps input out of RSA error messages', function (string $method) {
    try {
        Rsa::{$method}('sensitive-plaintext', 'invalid-key');
        $this->fail('Expected the RSA operation to fail.');
    } catch (CryptException $exception) {
        expect($exception->getMessage())->not->toContain('sensitive-plaintext');
    }
})->with(['encrypt', 'sign']);

it('uses the current key pair', function (bool $configure) {
    $originalCiphertext = Rsa::encrypt('payload');
    $originalSignature = Rsa::sign('payload');
    $key = openssl_pkey_new(['private_key_bits' => 1024]);
    openssl_pkey_export($key, $privateKey);
    $publicKey = openssl_pkey_get_details($key)['key'];

    if ($configure) {
        config([
            'pin.crypt.private_key' => $privateKey,
            'pin.crypt.public_key' => $publicKey,
        ]);
    }

    $privateKey = $configure ? null : $privateKey;
    $publicKey = $configure ? null : $publicKey;
    $encrypted = Rsa::encrypt('payload', $publicKey);
    $signature = Rsa::sign('payload', $privateKey);

    expect(Rsa::decrypt($encrypted, $privateKey))->toBe('payload')
        ->and(Rsa::verify('payload', $signature, $publicKey))->toBeTrue()
        ->and(Rsa::verify('payload', $originalSignature, $publicKey))->toBeFalse();

    openssl_private_decrypt(base64_decode($encrypted), $decrypted, $key);
    expect($decrypted)->toBe('payload');

    if (! $configure) {
        expect(Rsa::decrypt($originalCiphertext))->toBe('payload')
            ->and(Rsa::verify('payload', $originalSignature))->toBeTrue();
    }
})->with([
    'configured keys' => true,
    'explicit keys' => false,
]);

it('wraps invalid public key failures during verification', function () {
    expect(fn () => Rsa::verify('payload', Rsa::sign('payload'), 'invalid-public-key'))
        ->toThrow(CryptException::class);
});
