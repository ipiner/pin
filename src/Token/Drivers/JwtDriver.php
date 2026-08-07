<?php

declare(strict_types=1);

namespace Pin\Token\Drivers;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Pin\Token\Exceptions\TokenExpiredException;
use Pin\Token\Exceptions\TokenInvalidException;
use Pin\Token\Token;
use Pin\Token\TokenPayload;
use Throwable;

/**
 * JWT Token 驱动
 */
class JwtDriver extends Driver
{
    public function __construct(protected array $config)
    {

    }

    /**
     * 解析 Token。
     */
    public function decode(string $encodedPayload): Token
    {
        try {
            $payload = JWT::decode($encodedPayload, new Key($this->getKey(), $this->getAlgo()));

            return new Token((array) $payload, $encodedPayload);
        } catch (ExpiredException $e) {
            throw new TokenExpiredException(new Token((array) $e->getPayload(), $encodedPayload), $e);
        } catch (Throwable $e) {
            throw new TokenInvalidException(new Token(['raw' => $encodedPayload], $encodedPayload), $e);
        }
    }

    /**
     * 生成 Token。
     */
    public function encode(TokenPayload $payload, ?int $expires = null): string
    {
        if (! isset($payload->exp) && $expires) {
            $payload->exp = now()->getTimestamp() + $expires;
        }

        return JWT::encode($payload->toArray(), $this->getKey(), $this->getAlgo());
    }

    /**
     * 获取签名算法。
     */
    protected function getAlgo(): string
    {
        return $this->config['algo'];
    }

    /**
     * 获取签名密钥。
     */
    protected function getKey(): string
    {
        return $this->config['key'] ?? config('app.key');
    }
}
