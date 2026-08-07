<?php

declare(strict_types=1);

namespace Pin\Token\Drivers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Str;
use Pin\Support\DataBag;
use Pin\Token\Exceptions\TokenExpiredException;
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
        // 如果配置不完整，则与默认配置合并
        if (count($config) < 3) {
            $config = array_merge(config('pin.token.drivers.session'), $config);
        }

        $this->config = new SessionDriverConfig($config);
    }

    /**
     * 解码 Token
     *
     * @throws TokenExpiredException
     * @throws TokenMissingException
     */
    public function decode(string $encodedPayload): Token
    {
        $token = $this->decrypt($encodedPayload);

        // max_age：绝对生命周期控制（不可恢复）
        $this->validateMaxAge($token);

        // 从缓存中重新加载过期时间
        $this->reloadExpiredAt($token);

        // 统一过期校验
        $this->validateExpired($token);

        // 自动续期逻辑
        $this->refresh($token);

        return $token;
    }

    /**
     * 生成 Token
     *
     * @param  int|null  $expires  覆盖默认过期时间
     */
    public function encode(TokenPayload $payload, ?int $expires = null): string
    {
        $payload->expires ??= $expires ?? $this->config->expires;
        $payload->iat ??= now()->getTimestamp();
        $payload->exp ??= now()->getTimestamp() + $payload->expires;
        $payload->jti ??= $this->config->cache_prefix.Str::uuid();

        $this->persist($payload);

        return $this->encrypt($payload);
    }

    /**
     * 主动注销 Token
     */
    public function forget(Token|string|null $token): bool
    {
        if (! $token) {
            return false;
        }

        if (is_string($token)) {
            return $this->cache->forget($token);
        }

        return $this->cache->forget($token->jti);
    }

    /**
     * 判断 Token 是否过期
     */
    protected function isExpired(Token $token): bool
    {
        $expired = parent::isExpired($token);
        if ($expired) {
            $this->forget($token);
        }

        return $expired;
    }

    /**
     * 持久化 token 信息到 cache
     *
     * 只存：
     * - jti => exp
     *
     * TTL：
     * - 2 倍 expires（用于 decode 阶段容错判断）
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
     * 自动刷新入口
     */
    protected function refresh(Token $token): bool
    {
        return $this->shouldRefresh($token) && $this->touch($token);
    }

    /**
     * 判断是否需要刷新
     */
    protected function shouldRefresh(Token $token): bool
    {
        if ($this->config->refresh_before <= 0) {
            return false;
        }

        return $token->exp - now()->getTimestamp() < $this->config->refresh_before;
    }

    /**
     * 刷新 Token（延长 exp）
     */
    protected function touch(Token $token): bool
    {
        $token->exp = now()->getTimestamp() + $token->expires;

        return $this->persist($token->payload);
    }

    /**
     * 验证最大生命周期
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
     * 从缓存中重新加载过期时间
     */
    protected function reloadExpiredAt(Token $token): void
    {
        $exp = $this->cache->get($token->jti);
        if (! $exp) {
            throw new TokenMissingException($token);
        }

        $token->exp = (int) $exp;
    }
}

/**
 * Session 驱动配置对象
 *
 * 用于控制 Session Token 的生命周期与行为策略：
 * - expires        默认过期时间（秒）
 * - cache_prefix    cache key 前缀
 * - max_age         token 最大有效期（绝对生命周期）
 * - refresh_before  距离过期多少秒内自动续期
 */
class SessionDriverConfig extends DataBag
{
    // 仅作为结构定义容器
}
