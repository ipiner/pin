<?php

declare(strict_types=1);

use App\Factories\UserFactory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Pin\Auth\Auth;
use Pin\Auth\ConsoleUser;
use Pin\Auth\Guard;
use Pin\Auth\TokenResolver;
use Pin\Auth\UsersProvider;
use Pin\Errors\Errors;
use Pin\Tests\InteractsWithDatabase;
use Pin\Token\Exceptions\TokenMissingException;

uses(InteractsWithDatabase::class)->beforeEach(function () {
    // 清空 argv，保证每次测试环境一致
    app()->request->server->set('argv', null);
    $this->auth = auth(Guard::NAME);
});

afterEach(function () {
    // 测试中的生产环境模拟结束后，允许 Testbench 正常回滚测试迁移。
    app()->instance('env', 'testing');
});

it('returns console user when running in console', function () {
    app()->request->server->set('argv', [__FILE__]);
    $user = $this->auth->user();

    $consoleUser = new ConsoleUser();

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->id)->toBe($consoleUser->id)
        ->and($user->username)->toBe($consoleUser->username)
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
        $this->auth->forgetUser();
        app()->request->query->set('token', ''.$token);

        expect($this->auth->user()?->username)->toBe($expected);
    }
});

it('returns null in production environment when token invalid', function () {
    app()->instance('env', 'production');
    app()->request->query->set('token', Auth::token()->encode([], 10));

    expect($this->auth->user())->toBeNull();
});

it('validate returns false for empty token', function () {
    expect($this->auth->validate(['token' => null]))->toBeFalse();
});

it('does not reuse the authenticated identity when validating invalid credentials', function (array $credentials) {
    $user = UserFactory::new()->create();
    $raw = Auth::token()->encode(['uid' => $user->id], 60);
    app()->request->query->set('token', $raw);

    expect($this->auth->id())->toBe($user->id)
        ->and($this->auth->validate($credentials))->toBeFalse()
        ->and($this->auth->id())->toBe($user->id)
        ->and(Auth::token()->decode($raw)->uid)->toBe($user->id);
})->with([
    'missing' => [[]],
    'null' => [['token' => null]],
    'array' => [['token' => ['invalid']]],
    'integer' => [['token' => 1]],
    'sanctum' => [['token' => '1|other-token']],
    'invalid' => [['token' => 'invalid-token']],
]);

it('validates another user without changing the current user or logout target', function () {
    [$current, $other] = UserFactory::new()->count(2)->create();
    $currentToken = Auth::token()->encode(['uid' => $current->id], 60);
    $otherToken = Auth::token()->encode(['uid' => $other->id], 60);
    app()->request->query->set('token', $currentToken);

    expect($this->auth->id())->toBe($current->id)
        ->and($this->auth->validate(['token' => $otherToken]))->toBeTrue()
        ->and($this->auth->id())->toBe($current->id);

    $this->auth->logout();

    expect($this->auth->user())->toBeNull()
        ->and(fn () => Auth::token()->decode($currentToken))->toThrow(TokenMissingException::class)
        ->and(Auth::token()->decode($otherToken)->uid)->toBe($other->id);
});

it('returns false for expired or revoked credential tokens', function () {
    $user = UserFactory::new()->create();
    $expired = Auth::token()->encode(['uid' => $user->id, 'exp' => time() - 60], 60);
    $revoked = Auth::token()->encode(['uid' => $user->id], 60);
    Auth::token()->forget(Auth::token()->decode($revoked));

    expect($this->auth->validate(['token' => $expired]))->toBeFalse()
        ->and($this->auth->validate(['token' => $revoked]))->toBeFalse();
});

it('can resolve a user after clearing a cached guest result', function () {
    $user = UserFactory::new()->create();
    expect($this->auth->user())->toBeNull();
    app()->request->query->set('token', Auth::token()->encode(['uid' => $user->id], 60));

    expect($this->auth->user())->toBeNull()
        ->and($this->auth->forgetUser())->toBe($this->auth)
        ->and($this->auth->id())->toBe($user->id);
});

it('revokes a request token on logout even before the user is resolved', function () {
    $raw = Auth::token()->encode(['uid' => 1], 60);
    app()->request->query->set('token', $raw);

    $this->auth->logout();

    expect($this->auth->user())->toBeNull()
        ->and(fn () => Auth::token()->decode($raw))->toThrow(TokenMissingException::class);
});

it('does not immediately authenticate a debug user again after logout', function () {
    $user = UserFactory::new()->create();
    app()->request->query->set('token', $user->username);
    expect($this->auth->id())->toBe($user->id);

    $this->auth->logout();

    expect($this->auth->user())->toBeNull();
});

it('refreshes user and token state when the application request is rebound', function () {
    [$first, $second] = UserFactory::new()->count(2)->create();
    $firstToken = Auth::token()->encode(['uid' => $first->id], 60);
    app()->request->query->set('token', $firstToken);
    expect($this->auth->id())->toBe($first->id);

    $secondToken = Auth::token()->encode(['uid' => $second->id], 60);
    app()->instance('request', Request::create('/', parameters: ['token' => $secondToken]));

    expect(auth(Guard::NAME))->toBe($this->auth)
        ->and($this->auth->id())->toBe($second->id);
    $this->auth->logout();

    expect(Auth::token()->decode($firstToken)->uid)->toBe($first->id)
        ->and(fn () => Auth::token()->decode($secondToken))->toThrow(TokenMissingException::class);
});

it('clears an old authentication error when setting a user', function () {
    $user = UserFactory::new()->create();
    app()->request->attributes->set(Guard::UNAUTHENTICATED_CODE, Errors::TokenInvalid->code());

    expect($this->auth->setUser($user))->toBe($this->auth)
        ->and(app()->request->attributes->has(Guard::UNAUTHENTICATED_CODE))->toBeFalse()
        ->and($this->auth->user())->toBe($user);
});

it('never resolves debug credentials in production', function (string $field) {
    $user = UserFactory::new()->create();
    app()->instance('env', 'production');
    app()->request->query->set('token', (string) $user->$field);

    expect($this->auth->user())->toBeNull()
        ->and(app()->request->attributes->get(Guard::UNAUTHENTICATED_CODE))->toBe(Errors::TokenInvalid->code());
})->with(['id', 'username']);

it('loads the Pin user only once per request', function () {
    $user = new User(['id' => 1, 'username' => 'tester']);
    $provider = Mockery::mock(UsersProvider::class);
    $provider->shouldReceive('retrieveById')->once()->with(1)->andReturn($user);
    $request = Request::create('/', parameters: ['token' => Auth::token()->encode(['uid' => 1], 60)]);
    $guard = new Guard($provider, new TokenResolver($request));

    expect($guard->user())->toBe($user)
        ->and($guard->user())->toBe($user);
});
