<?php

declare(strict_types=1);

use App\Factories\UserFactory;
use App\Models\User;
use App\Routes\User\UserRoute;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;
use Pin\Route\RouteRegistrar;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

beforeEach(function () {
    RouteRegistrar::register(UserRoute::class);
});

it('asserts resource creation successfully', function () {
    UserRoute::Create->register(
        function (Request $request) {
            return ApiResponse::make(0, '', [
                'id' => User::create($request->json()->all())->id,
            ])->toResponse($request);
        }
    );

    $data = UserFactory::new()->definition();
    UserRoute::Create->testing($this)->withPayload($data)->created(
        fn (User $user) => expect($user->realname)->toBe($data['realname'])
    );
});

it('asserts resource deletion successfully', function () {
    UserRoute::Delete->register(
        function (Request $request, int $id) {
            User::find($id)->delete();

            return ApiResponse::make(0, '', [
                'deleted' => true,
            ])->toResponse($request);
        }
    );

    UserRoute::Delete->testing($this)
        ->withPayload(UserFactory::new()->definition())
        ->withFactory(UserFactory::class)
        ->deleted(
            fn (User $user) => expect($user->exists)->toBeFalse()
        );
});

it('asserts resource update successfully', function () {
    UserRoute::Update->register(
        function (Request $request, int $id) {
            $data = $request->json()->all();

            User::find($id)->update($data);

            return ApiResponse::make(0, '', [
                'updated' => true,
            ])->toResponse($request);
        }
    );

    UserRoute::Update->testing($this)
        ->withPayload([...UserFactory::new()->definition(), 'v' => true])
        ->withFactory(UserFactory::class)
        ->updated(
            fn (User $user) => expect($user->v)->toBe(1)
        );
});

it('asserts paginated response successfully', function () {
    UserRoute::List->register(
        function (Request $request) {
            return ApiResponse::make(
                0,
                '',
                User::where('username', $request->query('username'))->pagination()
            )->toResponse($request);
        }
    );

    $data = UserFactory::new()->definition();

    User::create($data);

    UserRoute::List->testing($this)
        ->withPayload(['username' => $data['username']])
        ->paginated(
            function (array $items, int $total, int $totalPage) use ($data) {
                expect($items[0]['username'])->toBe($data['username'])
                    ->and($total)->toBe(1)
                    ->and($totalPage)->toBe(1);
            }
        );
});

it('assert successfully', function () {
    UserRoute::Index->register(
        fn () => ApiResponse::make()
    );
    UserRoute::Index->testing($this)->successful()->assertCode(0);
});

it('sends index request', function () {
    UserRoute::Index->testJson($this, ['name' => 'foo'])
        ->assertJsonPath('data.id', 0)
        ->assertJsonPath('data.path', '/')
        ->assertJsonPath('data.method', 'GET')
        ->assertJsonPath('data.route.uri', '/')
        ->assertJsonPath('data.route.action.as', 'user.index')
        ->assertJsonPath('data.query.name', 'foo');
});

it('sends list request', function () {
    UserRoute::List->testJson($this, ['name' => 'foo'])
        ->assertJsonPath('data.id', 0)
        ->assertJsonPath('data.path', 'api/users')
        ->assertJsonPath('data.method', 'GET')
        ->assertJsonPath('data.route.uri', 'api/users')
        ->assertJsonPath('data.route.action.as', 'users')
        ->assertJsonPath('data.route.action.middleware.0', TrimStrings::class)
        ->assertJsonPath('data.query.name', 'foo');
});

it('sends create request', function () {
    UserRoute::Create->testJson($this, ['name' => 'foo'])
        ->assertJsonPath('data.id', 0)
        ->assertJsonPath('data.path', 'api/users')
        ->assertJsonPath('data.method', 'POST')
        ->assertJsonPath('data.route.uri', 'api/users')
        ->assertJsonPath('data.route.action.as', 'users.create')
        ->assertJsonPath('data.post.name', 'foo');
});

it('sends update request', function () {
    UserRoute::Update->testing($this)
        ->withRouteParams(['id' => 1])
        ->json(['name' => 'foo'])
        ->assertJsonPath('data.id', 1)
        ->assertJsonPath('data.path', 'api/users/1')
        ->assertJsonPath('data.method', 'PUT')
        ->assertJsonPath('data.route.uri', 'api/users/{id}')
        ->assertJsonPath('data.route.action.as', 'users.update')
        ->assertJsonPath('data.post.name', 'foo');
});
