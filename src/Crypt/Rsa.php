<?php

declare(strict_types=1);

namespace Pin\Crypt;

use Pin\Support\Str;
use Throwable;

/**
 * RSA 加密、解密、签名工具
 */
class Rsa
{
    /**
     * 使用私钥解密
     *
     * @param  string  $str  base64 编码后的密文
     * @param  string|null  $privateKey  私钥（PEM 格式）
     *
     * @throws CryptException
     */
    public function decrypt(string $str, ?string $privateKey = null): string
    {
        $res = openssl_private_decrypt(
            base64_decode($str),
            $decrypted,
            openssl_get_privatekey($privateKey ?: config('pin.crypt.private_key'))
        );

        // 解密失败 或 非法 UTF-8
        if ($res === false || ! Str::isValidUtf8($decrypted)) {
            throw new CryptException('解密数据失败：'.$str);
        }

        return $decrypted;
    }

    /**
     * 使用公钥加密
     *
     * @param  string  $str  明文
     * @param  string|null  $publicKey  公钥（PEM 格式）
     *
     * @throws CryptException
     */
    public function encrypt(string $str, ?string $publicKey = null): string
    {
        try {
            openssl_public_encrypt(
                $str,
                $encrypted,
                openssl_get_publickey($publicKey ?: config('pin.crypt.public_key'))
            );

            return base64_encode($encrypted);
        } catch (Throwable $e) {
            throw new CryptException('加密数据失败：'.$str, $e->getCode(), $e);
        }
    }

    /**
     * 使用私钥签名
     *
     * @param  string  $str  原始数据
     * @param  string|null  $privateKey  私钥
     * @param  int  $algorithm  签名算法
     *
     * @throws CryptException
     */
    public function sign(
        string $str,
        ?string $privateKey = null,
        int $algorithm = OPENSSL_ALGO_SHA256
    ): string {
        try {
            openssl_sign(
                $str,
                $signature,
                $privateKey ?: config('pin.crypt.private_key'),
                $algorithm
            );

            return base64_encode($signature);
        } catch (Throwable $e) {
            throw new CryptException('数据签名失败：'.$str, $e->getCode(), $e);
        }
    }

    /**
     * 使用公钥验证签名
     *
     * @param  string  $str  原始数据
     * @param  string  $signature  base64 编码签名
     * @param  string|null  $publicKey  公钥
     * @param  int  $algorithm  签名算法
     */
    public function verify(
        string $str,
        string $signature,
        ?string $publicKey = null,
        int $algorithm = OPENSSL_ALGO_SHA256,
    ): bool {
        $result = openssl_verify(
            $str,
            base64_decode($signature),
            $publicKey ?: config('pin.crypt.public_key'),
            $algorithm
        );

        return $result === 1;
    }
}
