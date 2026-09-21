<?php

declare(strict_types=1);

namespace Pin\Crypt;

use OpenSSLAsymmetricKey;
use Pin\Support\Str;
use Throwable;

/**
 * RSA 加解密与签名
 */
class Rsa
{
    /**
     * @var array<string, array{pem: string, key: OpenSSLAsymmetricKey}>
     */
    protected array $keys = [];

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
        try {
            $encrypted = base64_decode($str, true);

            if ($encrypted === false) {
                throw new CryptException('解密数据失败');
            }

            $result = openssl_private_decrypt(
                $encrypted,
                $decrypted,
                $this->resolveKey($privateKey, true)
            );

            if (! $result || ! Str::isValidUtf8($decrypted)) {
                throw new CryptException('解密数据失败');
            }

            return $decrypted;
        } catch (Throwable $e) {
            throw $this->normalizeException($e, '解密数据失败');
        }
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
            $result = openssl_public_encrypt(
                $str,
                $encrypted,
                $this->resolveKey($publicKey, false)
            );

            if (! $result) {
                throw new CryptException('加密数据失败');
            }

            return base64_encode($encrypted);
        } catch (Throwable $e) {
            throw $this->normalizeException($e, '加密数据失败');
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
            $result = openssl_sign(
                $str,
                $signature,
                $this->resolveKey($privateKey, true),
                $algorithm
            );

            if (! $result) {
                throw new CryptException('数据签名失败');
            }

            return base64_encode($signature);
        } catch (Throwable $e) {
            throw $this->normalizeException($e, '数据签名失败');
        }
    }

    /**
     * 使用公钥验证签名
     *
     * @param  string  $str  原始数据
     * @param  string  $signature  base64 编码签名
     * @param  string|null  $publicKey  公钥
     * @param  int  $algorithm  签名算法
     *
     * @throws CryptException
     */
    public function verify(
        string $str,
        string $signature,
        ?string $publicKey = null,
        int $algorithm = OPENSSL_ALGO_SHA256,
    ): bool {
        $signature = base64_decode($signature, true);

        if ($signature === false) {
            return false;
        }

        try {
            return openssl_verify(
                $str,
                $signature,
                $this->resolveKey($publicKey, false),
                $algorithm
            ) === 1;
        } catch (Throwable $e) {
            throw $this->normalizeException($e, '签名验证失败');
        }
    }

    /**
     * 解析并复用密钥
     */
    protected function resolveKey(?string $key, bool $private): OpenSSLAsymmetricKey
    {
        $type = $private ? 'private' : 'public';
        $pem = $key ?: config("pin.crypt.{$type}_key");

        if (isset($this->keys[$type]) && $this->keys[$type]['pem'] === $pem) {
            return $this->keys[$type]['key'];
        }

        $resolved = $private
            ? openssl_pkey_get_private($pem)
            : openssl_pkey_get_public($pem);

        if (! $resolved) {
            throw new CryptException('无效的 RSA 密钥');
        }

        $this->keys[$type] = ['pem' => $pem, 'key' => $resolved];

        return $resolved;
    }

    /**
     * 转换加解密异常
     */
    protected function normalizeException(Throwable $e, string $message): CryptException
    {
        return $e instanceof CryptException
            ? $e
            : new CryptException($message, $e->getCode(), $e);
    }
}
