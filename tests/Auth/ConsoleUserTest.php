<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Pin\Auth\ConsoleUser;

it('resolves uid', function (?int $returnValue, int $expected) {
    $user = Mockery::mock(ConsoleUser::class)->makePartial();
    $user->shouldAllowMockingProtectedMethods()
        ->shouldReceive('geteuid')
        ->andReturn($returnValue);

    $user->__construct();

    $this->assertSame($expected, $user->id);
})->with([
    'returns posix uid' => [9527, 9527],
    'falls back to zero when uid is unavailable' => [null, 0],
]);

it('resolves username', function (?string $userEnv, ?string $usernameEnv, string $expected) {
    $request = Request::create(
        uri: '/',
        server: ['USER' => $userEnv, 'USERNAME' => $usernameEnv]
    );
    expect(new ConsoleUser($request)->username)->toBe($expected);
})->with([
    'uses USER environment variable' => ['linux_user', null, 'linux_user'],
    'uses USERNAME environment variable' => [null, 'windows_user', 'windows_user'],
    'prefers USER over USERNAME' => ['linux_user', 'windows_user', 'linux_user'],
    'uses default username' => [null, null, ConsoleUser::DEFAULT_USERNAME],
]);
