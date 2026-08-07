<?php

declare(strict_types=1);

namespace Pin\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher;
use Pin\Models\Model;

/**
 * 用户提供器。
 *
 * 基于 Laravel EloquentUserProvider 扩展，负责根据认证结果加载
 * 应用用户模型，并为 Guard 提供用户查询能力。
 *
 * @template TModel of Model
 */
class UsersProvider extends EloquentUserProvider
{
    /**
     * 用户模型类名。
     *
     * @var class-string<TModel>
     */
    protected $model;

    /**
     * Provider 注册名称。
     */
    public const string NAME = 'pin';

    public function __construct(Hasher $hasher, ?string $model = null)
    {
        parent::__construct($hasher, null);
        $this->model = $model ?: User::class;

        $this->initialize();
    }

    /**
     * 根据用户名查找用户。
     */
    public function findByUsername(string $username): ?Authenticatable
    {
        return $this->model::findBy('username', $username);
    }

    /**
     * 根据用户 ID 查找用户。
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        return $this->model::find((int) $identifier);
    }

    /**
     * 初始化用户提供器扩展点。
     *
     * 子类可重写该方法以完成自定义初始化逻辑。
     */
    protected function initialize(): void
    {
        //
    }
}
