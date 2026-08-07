<?php

namespace Pin\Tests\Models\Models;

use App\Factories\UserFactory;
use Pin\Models\Concerns\SoftDeletes;

class User extends \App\Models\User
{
    use SoftDeletes;

    public const REJECTED_USERNAME = 'pNYV1mNdErzHcjlzqeD5e3khCCJnj1';

    public static function cacheInMemorize(): bool|int
    {
        return false;
    }

    public static function createByFactory(array $attributes = []): static
    {
        return static::create(array_merge(UserFactory::new()->definition(), $attributes));
    }

    public static function getByUsername(string $value): ?static
    {
        return static::where('username', $value)->first();
    }

    public function subjectNameColumn()
    {
        return ['name', 'username'];
    }

    protected function onCreating(): void
    {
        parent::onCreating();
        $this->password = md5($this->password);
    }

    protected function onDeleting()
    {
        return $this->username !== static::REJECTED_USERNAME;
    }

    protected function onSaving(): bool
    {
        return $this->username !== static::REJECTED_USERNAME;
    }
}
