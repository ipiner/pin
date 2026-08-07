<?php

declare(strict_types=1);

use App\Factories\UserFactory;
use Illuminate\Support\Str;
use Pin\Auth\Auth;
use Pin\Auth\ConsoleUser;
use Pin\Auth\Guard;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class)->beforeEach(function () {
    // 清空 argv，保证每次测试环境一致
    app()->request->server->set('argv', null);
    $this->auth = auth(Guard::NAME);
});

it('returns console user when running in console', function () {
    app()->request->server->set('argv', [__FILE__]);
    $user = $this->auth->user();

    $consoleUser = new ConsoleUser();

    expect($user->username)->toBe($consoleUser->username)
        ->and($user)->toBe($this->auth->user());
});

it('returns null for no token or invalid token', function (?string $token) {
    app()->request->query->set('token', $token);
    expect($this->auth->user())->toBeNull();
})->with([
    'no token provided' => null,
    'sanctum token' => '1|'.Str::random(),
]);

it('sets unauthenticated code when token invalid', function () {
    expect(app()->request->attributes->get(Guard::UNAUTHENTICATED_CODE))->toBeNull();

    app()->request->query->set('token', 'x');
    $this->auth->user();

    expect(app()->request->attributes->get(Guard::UNAUTHENTICATED_CODE))->not()->toBeNull();
});

it('resolves user in non-production environment', function () {
    $user = UserFactory::new()->create();

    $tokens = [
        $user->id => $user->username,
        $user->username => $user->username,
        Auth::token()->encode([], 10) => null,
        Auth::token()->encode(['uid' => $user->id], 10) => $user->username,
    ];

    foreach ($tokens as $token => $expected) {
        $this->auth->logout();
        app()->request->query->set('token', ''.$token);

        expect($this->auth->user()?->username)->toBe($expected);
    }
});

it('returns null in production environment when token invalid', function () {
    config(['app.env' => 'production']);
    app()->request->query->set('token', Auth::token()->encode([], 10));

    expect($this->auth->user())->toBeNull();
});

it('validate returns false for empty token', function () {
    expect($this->auth->validate(['token' => null]))->toBeFalse();
});
