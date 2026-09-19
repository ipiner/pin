<?php

declare(strict_types=1);

use App\Factories\UserFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use Pin\Tests\InteractsWithDatabase;
use Pin\Validation\Rules\Unique;

uses(InteractsWithDatabase::class);

it('validates unique rule', function () {
    $error = '';
    $fail = function ($message) use (&$error) {
        $error = $message;
    };

    $user = UserFactory::new()->create(['realname' => 'foo']);

    // existing username triggers error
    (new Unique(User::class))->validate('username', $user->username, $fail);
    expect($error)->toBe(__('validation.unique'));

    $error = '';
    (new Unique(User::class))->where(['username', '=', $user->username])
        ->validate('username', $user->username, $fail);
    expect($error)->toBe(__('validation.unique'));

    $error = '';
    (new Unique(User::class))->where('realname', 'bar')
        ->validate('username', $user->username, $fail);
    expect($error)->toBe('');

    $error = '';
    (new Unique(User::class))->whereNot('realname', 'foo')
        ->validate('username', $user->username, $fail);
    expect($error)->toBe('');

    $error = '';
    (new Unique(User::class))->ignore($user->id)
        ->validate('username', $user->username, $fail);
    expect($error)->toBe('');

    // custom message
    (new Unique(User::class))->message('用户名exists')
        ->validate('username', $user->username, $fail);
    expect($error)->toBe('用户名exists');
});

it('checks uniqueness without retrieving models', function () {
    $user = UserFactory::new()->create();
    $retrieved = 0;

    Event::listen('eloquent.retrieved: '.User::class, function () use (&$retrieved) {
        $retrieved++;
    });

    expect(new Unique(User::class)->exists('username', $user->username))->toBeTrue()
        ->and(new Unique(User::class)->exists('username', 'missing'))->toBeFalse()
        ->and($retrieved)->toBe(0);
});

it('keeps model scopes in uniqueness queries', function () {
    $visible = UserFactory::new()->create(['realname' => 'visible']);
    $hidden = UserFactory::new()->create(['realname' => 'hidden']);
    $model = new class extends User
    {
        protected $table = 'users';

        protected static function booted(): void
        {
            self::addGlobalScope('visible', function (Builder $query) {
                $query->where('realname', 'visible');
            });
        }
    };
    $rule = new Unique($model::class);

    expect($rule->exists('username', $visible->username))->toBeTrue()
        ->and($rule->exists('username', $hidden->username))->toBeFalse();
});

it('replaces conditions for the same column', function () {
    $user = UserFactory::new()->create(['realname' => '']);
    $rule = new Unique(User::class)->where('realname', 'foo')->where('realname', '');

    expect($rule->exists('username', $user->username))->toBeTrue();

    $rule->whereNot('realname', '');

    expect($rule->exists('username', $user->username))->toBeFalse();

    $rule->where(['realname', '=', '']);

    expect($rule->exists('username', $user->username))->toBeTrue();
});
