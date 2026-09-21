<?php

declare(strict_types=1);

namespace Pin\Token\Drivers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Str;
use Override;
use Pin\Token\Exceptions\TokenExpiredException;
use Pin\Token\Exceptions\TokenInvalidException;
use Pin\Token\Exceptions\TokenMissingException;
use Pin\Token\Token;
use Pin\Token\TokenPayload;

/**
 * Session Token 驱动
 */
class SessionDriver extends Driver
{
    use AesHelper;

    /**
     * 驱动配置
     */
    protected SessionDriverConfig $config;

    public function __construct(protected Repository $cache, array $config)
    {
        $this->config = new SessionDriverConfig(array_replace(
            config('pin.token.drivers.session', []),
            $config,
        ));
    }

    /**
     * 解码 Token
     *
     * @throws TokenInvalidException
     * @throws TokenExpiredException
     * @throws TokenMissingException
     */
    #[Override]
    public function decode(string $encodedPayload): Token
    {
        $token = $this->decrypt($encodedPayload);

        if (! isset($token->iat, $token->expires, $token->jti)) {
            throw new TokenInvalidException($token);
        }

        $this->validateMaxAge($token);
        $this->reloadExpiredAt($token);
        $this->validateExpired($token);
        $this->refresh($token);

        return $token;
    }

    /**
     * 编码 Token
     *
     * @param  int|null  $expires  有效期（秒）
     */
    #[Override]
    public function encode(TokenPayload $payload, ?int $expires = null): string
    {
        $now = now()->getTimestamp();

        $payload->expires ??= $expires ?? $this->config->expires;
        $payload->iat ??= $now;
        $payload->exp ??= $now + $payload->expires;
        $payload->jti ??= $this->config->cache_prefix.Str::uuid();

        $this->persist($payload);

        return $this->encrypt($payload);
    }

    /**
     * 注销 Token
     */
    public function forget(Token|string|null $token): bool
    {
        if (! $token) {
            return false;
        }

        return $this->cache->forget(is_string($token) ? $token : $token->jti);
    }

    /**
     * 判断 Token 是否过期
     */
    #[Override]
    protected function isExpired(Token $token): bool
    {
        $expired = parent::isExpired($token);

        if ($expired) {
            $this->forget($token);
        }

        return $expired;
    }

    /**
     * 缓存 Token 过期时间
     */
    protected function persist(TokenPayload $payload): bool
    {
        return $this->cache->put(
            $payload->jti,
            $payload->exp,
            $payload->expires * 2,
        );
    }

    /**
     * 按需续期
     */
    protected function refresh(Token $token): bool
    {
        return $this->shouldRefresh($token) && $this->touch($token);
    }

    /**
     * 判断是否需要续期
     */
    protected function shouldRefresh(Token $token): bool
    {
        if ($this->config->refresh_before <= 0) {
            return false;
        }

        return $token->exp - now()->getTimestamp() < $this->config->refresh_before;
    }

    /**
     * 延长 Token 有效期
     */
    protected function touch(Token $token): bool
    {
        $token->exp = now()->getTimestamp() + $token->expires;

        return $this->persist($token->payload);
    }

    /**
     * 校验最大有效期
     */
    protected function validateMaxAge(Token $token): void
    {
        if (
            $this->config->max_age > 0
            && now()->getTimestamp() > $token->iat + $this->config->max_age
        ) {
            throw new TokenExpiredException($token);
        }
    }

    /**
     * 读取缓存中的过期时间
     */
    protected function reloadExpiredAt(Token $token): void
    {
        $expiresAt = $this->cache->get($token->jti);

        if (! $expiresAt) {
            throw new TokenMissingException($token);
        }

        $token->exp = (int) $expiresAt;
    }
}
