<?php

declare(strict_types=1);

namespace Pin\Crypt;

use Illuminate\Support\Str;
use Throwable;

/**
 * AES-128-CBC 加解密
 */
class Aes
{
    protected const string CIPHER = 'aes-128-cbc';

    protected const int KEY_LENGTH = 16;

    /**
     * 解密字符串
     *
     * @throws CryptException
     */
    public function decrypt(string $encrypted): string
    {
        try {
            if ($this->isRandomKey($encrypted)) {
                $key = substr($encrypted, 1, static::KEY_LENGTH);
                $iv = $key;
                $encrypted = substr($encrypted, static::KEY_LENGTH + 1);
            } else {
                $key = config('pin.crypt.key');
                $iv = config('pin.crypt.iv');
                $encrypted = substr($encrypted, 1);
            }

            $decrypted = openssl_decrypt($encrypted, static::CIPHER, $key, 0, $iv);

            if ($decrypted === false) {
                throw new CryptException('解密数据失败');
            }

            return $decrypted;
        } catch (Throwable $e) {
            throw $this->normalizeException($e)->withLogLevel('warning');
        }
    }

    /**
     * 加密字符串
     *
     * @param  bool  $randomKey  使用随机密钥
     *
     * @throws CryptException
     */
    public function encrypt(string $plain, bool $randomKey = false): string
    {
        try {
            if ($randomKey) {
                $key = Str::random(static::KEY_LENGTH);
                $iv = $key;
            } else {
                $key = config('pin.crypt.key');
                $iv = config('pin.crypt.iv');
            }

            $encrypted = openssl_encrypt($plain, static::CIPHER, $key, 0, $iv);

            if ($encrypted === false) {
                throw new CryptException('加密数据失败');
            }

            return $randomKey
                ? chr(random_int(65, 90)).$key.$encrypted
                : chr(random_int(97, 122)).$encrypted;
        } catch (Throwable $e) {
            throw $this->normalizeException($e);
        }
    }

    /**
     * 是否使用随机密钥
     */
    protected function isRandomKey(string $str): bool
    {
        $prefix = $str[0] ?? '';

        return $prefix >= 'A' && $prefix <= 'Z';
    }

    /**
     * 转换加解密异常
     */
    protected function normalizeException(Throwable $e): CryptException
    {
        return $e instanceof CryptException
            ? $e
            : new CryptException($e->getMessage(), $e->getCode(), $e);
    }
}
