<?php

declare(strict_types=1);

namespace Pin\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher;
use Pin\Models\Model;

/**
 * Pin 用户提供器
 *
 * @template TModel of Model&Authenticatable
 */
class UsersProvider extends EloquentUserProvider
{
    /**
     * 用户模型类名
     *
     * @var class-string<TModel>
     */
    protected $model;

    /**
     * Provider 注册名称
     */
    public const string NAME = 'pin';

    public function __construct(Hasher $hasher, ?string $model = null)
    {
        parent::__construct($hasher, $model ?: User::class);

        $this->initialize();
    }

    /**
     * 根据用户名查找用户
     */
    public function findByUsername(string $username): ?Authenticatable
    {
        return $this->model::findBy('username', $username);
    }

    /**
     * 根据用户 ID 查找用户
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        return $this->model::find((int) $identifier);
    }

    /**
     * 初始化用户提供器
     */
    protected function initialize(): void
    {
        //
    }
}
