<?php

declare(strict_types=1);

namespace Pin\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Throwable;

/**
 * 基于 Token 的认证 Guard。
 *
 * Guard 负责从当前请求中解析 Token，并通过用户提供器加载认证用户。
 * 在 HTTP 场景中，它会读取请求 Token；在控制台场景中，它会创建
 * 一个代表当前运行环境的用户上下文。
 */
class Guard implements \Illuminate\Contracts\Auth\Guard
{
    use GuardHelpers;

    /**
     * Guard 注册名称。
     */
    public const string NAME = 'pin';

    /**
     * 请求属性中用于保存未认证原因的键名。
     */
    public const string UNAUTHENTICATED_CODE = 'unauthenticated.code';

    /**
     * 标记当前请求是否已经完成用户解析。
     */
    protected bool $userResolved = false;

    public function __construct(
        UsersProvider $provider,
        protected TokenResolver $tokenResolver,
    ) {
        $this->provider = $provider;
    }

    /**
     * 注销当前认证用户。
     *
     * 该操作会清理当前用户状态，并使当前请求关联的 Token 失效。
     */
    public function logout(): void
    {
        $this->forgetUser();
        $this->tokenResolver->forgetToken();

        $this->userResolved = false;
    }

    /**
     * 获取当前认证用户。
     *
     * 用户只会在首次访问时解析一次，解析结果会在当前请求生命周期内复用。
     * 如果认证失败，将返回 null，并把失败原因交由异常处理器统一处理。
     */
    public function user(): ?Authenticatable
    {
        // 已解析过，直接返回（包括 null）
        if ($this->userResolved || $this->user) {
            return $this->user;
        }

        $this->userResolved = true;

        try {
            return $this->user = $this->resolveUser();
        } catch (Throwable $e) {
            // 重要：不能抛异常，否则可能导致中间件链中断
            // 如：Sanctum 的 AuthenticateSession 会调用 $request->user()

            report($e);

            // 将错误码写入 request，由 `Exception Handler` 统一处理
            app()->request->attributes->set(static::UNAUTHENTICATED_CODE, $e->getCode());

            return null;
        }
    }

    /**
     * 校验给定凭证是否可以解析为有效用户。
     */
    public function validate(array $credentials = []): bool
    {
        $key = config('auth.guards.token_key', 'token');

        $resolver = clone $this->tokenResolver;
        $resolver->resolve($credentials[$key]);
        $id = $resolver->getUid();

        return $id > 0 && $this->provider->retrieveById($id);
    }

    /**
     * 在非生产环境中解析调试登录用户。
     *
     * 支持通过用户 ID 或用户名快速获取用户。生产环境以及 Sanctum Token
     * 不会进入该流程。
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
     * 通过认证 Token 加载用户。
     */
    protected function resolveTokenUser(string $token): ?Authenticatable
    {
        $this->tokenResolver->resolve($token);
        $id = (int) $this->tokenResolver->getUid();

        return $id > 0 ? $this->provider->retrieveById($id) : null;
    }

    /**
     * 根据当前运行环境解析用户。
     */
    protected function resolveUser(): ?Authenticatable
    {
        if (app()->runningInHttp()) {
            return $this->resolveUserForHttp();
        }

        return $this->resolveUserForConsole();
    }

    /**
     * 为控制台环境创建认证用户。
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
     * 从 HTTP 请求中解析认证用户。
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
}
