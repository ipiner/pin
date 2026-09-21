<?php

declare(strict_types=1);

namespace Pin\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Http\Request;
use Pin\Token\Exceptions\TokenException;
use Throwable;

/**
 * 基于 Token 的认证 Guard
 */
class Guard implements GuardContract
{
    use GuardHelpers;

    /**
     * Guard 注册名称
     */
    public const string NAME = 'pin';

    /**
     * 未认证原因的请求属性键名
     */
    public const string UNAUTHENTICATED_CODE = 'unauthenticated.code';

    /**
     * 是否已解析用户
     */
    protected bool $userResolved = false;

    public function __construct(
        UsersProvider $provider,
        protected TokenResolver $tokenResolver,
    ) {
        $this->provider = $provider;
    }

    /**
     * 清理用户解析状态
     */
    public function forgetUser(): static
    {
        $this->user = null;
        $this->userResolved = false;
        $this->tokenResolver->getRequest()->attributes->remove(static::UNAUTHENTICATED_CODE);

        return $this;
    }

    /**
     * 注销请求 Token
     */
    public function logout(): void
    {
        try {
            $this->revokeToken();
        } finally {
            $this->forgetUser();
            $this->userResolved = true;
        }
    }

    /**
     * 设置当前请求
     */
    public function setRequest(Request $request): static
    {
        $this->tokenResolver->setRequest($request);

        return $this->forgetUser();
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;
        $this->userResolved = true;
        $this->tokenResolver->getRequest()->attributes->remove(static::UNAUTHENTICATED_CODE);

        return $this;
    }

    /**
     * 获取当前认证用户
     */
    public function user(): ?Authenticatable
    {
        // 复用解析结果（包括 null）
        if ($this->userResolved || $this->user) {
            return $this->user;
        }

        $this->userResolved = true;
        $this->tokenResolver->getRequest()->attributes->remove(static::UNAUTHENTICATED_CODE);

        try {
            return $this->user = $this->resolveUser();
        } catch (Throwable $e) {
            // 保存认证错误码
            $this->tokenResolver->getRequest()->attributes->set(
                static::UNAUTHENTICATED_CODE,
                $e->getCode(),
            );
            report($e);

            return null;
        }
    }

    /**
     * 校验认证凭证
     */
    public function validate(array $credentials = []): bool
    {
        $token = $credentials[$this->tokenResolver->getTokenKey()] ?? null;

        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        $resolver = clone $this->tokenResolver;

        try {
            $resolver->resolve($token);
        } catch (TokenException) {
            return false;
        }

        $id = $resolver->getUid();

        return $id > 0 && $this->provider->retrieveById($id);
    }

    /**
     * 在非生产环境中解析调试登录用户
     *
     * @throws AuthenticationException
     */
    protected function resolveDebugUser(string $requestToken): ?Authenticatable
    {
        if (app()->isProduction() || $this->tokenResolver->isSanctumToken($requestToken)) {
            return null;
        }

        // 数字 => 按 ID 登录
        if (ctype_digit($requestToken)) {
            return $this->provider->retrieveById($requestToken)
                ?: throw new AuthenticationException('', 404);
        }

        // 短字符串 => 按 username 登录
        if (strlen($requestToken) < 30) {
            return $this->provider->findByUsername($requestToken)
                ?: throw new AuthenticationException('', 404);
        }

        return null;
    }

    /**
     * 通过认证 Token 加载用户
     */
    protected function resolveTokenUser(string $token): ?Authenticatable
    {
        $this->tokenResolver->resolve($token);
        $id = (int) $this->tokenResolver->getUid();

        return $id > 0 ? $this->provider->retrieveById($id) : null;
    }

    /**
     * 根据当前运行环境解析用户
     */
    protected function resolveUser(): ?Authenticatable
    {
        if (app()->runningInHttp()) {
            return $this->resolveUserForHttp();
        }

        return $this->resolveUserForConsole();
    }

    /**
     * 为控制台环境创建认证用户
     */
    protected function resolveUserForConsole(): Authenticatable
    {
        $model = $this->provider->getModel();

        /** @var ConsoleUser $user */
        $user = app(ConsoleUser::class);

        return new $model([
            'id' => $user->id,
            'username' => $user->username,
        ]);
    }

    /**
     * 从 HTTP 请求中解析认证用户
     */
    protected function resolveUserForHttp(): ?Authenticatable
    {
        $token = $this->tokenResolver->getRequestToken();

        if (! $token) {
            return null;
        }

        // Debug 登录（开发辅助）
        if ($user = $this->resolveDebugUser($token)) {
            return $user;
        }

        return $this->resolveTokenUser($token);
    }

    /**
     * 注销请求 Token
     */
    protected function revokeToken(): void
    {
        if (! $this->tokenResolver->getResolvedToken()) {
            try {
                $this->tokenResolver->resolve($this->tokenResolver->getRequestToken());
            } catch (TokenException) {
                // 已失效或非法的 Token 无需再注销
                return;
            }
        }

        $this->tokenResolver->forgetToken();
    }
}
