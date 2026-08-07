<?php

declare(strict_types=1);

namespace Pin\Crypt;

use Illuminate\Support\Str;
use Throwable;

/**
 * AES-128-CBC 加解密工具
 */
class Aes
{
    /**
     * 解密字符串
     *
     * @throws CryptException
     */
    public function decrypt(string $encrypted): string
    {
        // 判断是否使用随机 key（通过首字符）
        if ($this->isRandomKey($encrypted)) {
            // 格式：[A-Z][key][data]
            $key = substr($encrypted, 1, 16);
            $iv = $key;

            // 去掉前缀 + key
            $encrypted = substr($encrypted, 17);
        } else {
            // 使用配置 key
            $key = config('pin.crypt.key');
            $iv = config('pin.crypt.iv');

            // 去掉前缀
            $encrypted = substr($encrypted, 1);
        }

        try {
            $res = openssl_decrypt(
                $encrypted,
                'aes-128-cbc',
                $key,
                0,
                $iv
            );
            if ($res === false) {
                throw new CryptException(
                    'Unable to decrypt payload: openssl_decrypt() returned false.'
                );
            }

            return $res;
        } catch (Throwable $e) {
            throw $this->normalizeException($e)
                ->withContext(['encrypted' => $encrypted])
                ->withLogLevel('warning');
        }
    }

    /**
     * 加密字符串
     *
     * @param  string  $plain  明文
     * @param  bool  $randomKey  是否使用随机 key
     * @return string 加密后的字符串
     *
     * @throws CryptException
     */
    public function encrypt(string $plain, bool $randomKey = false): string
    {
        if ($randomKey) {
            // 生成随机 key（长度默认 16）
            $key = Str::random();
            $iv = $key;
        } else {
            // 使用配置 key
            $key = config('pin.crypt.key');
            $iv = config('pin.crypt.iv');
        }

        try {
            $encrypted = openssl_encrypt(
                $plain,
                'aes-128-cbc',
                $key,
                0,
                $iv
            );

            return $randomKey
                // A-Z：标识随机 key
                ? chr(random_int(65, 90)).$key.$encrypted
                // a-z：标识固定 key
                : chr(random_int(97, 122)).$encrypted;

        } catch (Throwable $e) {
            throw $this->normalizeException($e)->withContext(['plain' => $plain]);
        }
    }

    /**
     * 是否使用随机 key 模式
     *
     * - 首字符在 A-Z 范围 → 随机 key
     * - 否则 → 固定 key
     */
    protected function isRandomKey(string $str): bool
    {
        $prefix = $str[0];

        return $prefix >= 'A' && $prefix <= 'Z';
    }

    /**
     * 规范化异常类型
     *
     * 将任意 Throwable 统一转换为 CryptException
     */
    protected function normalizeException(Throwable $e): CryptException
    {
        return $e instanceof CryptException
            ? $e
            : new CryptException($e->getMessage(), $e->getCode(), $e);
    }
}
