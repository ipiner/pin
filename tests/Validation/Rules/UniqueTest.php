<?php

declare(strict_types=1);

use App\Factories\UserFactory;
use App\Models\User;
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
