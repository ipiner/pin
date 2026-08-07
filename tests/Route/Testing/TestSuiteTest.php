<?php

declare(strict_types=1);

use App\Routes\User\UserRoute;
use Pin\Http\ApiResponse;
use Pin\Route\Testing\TestingMethod;
use Pin\Route\Testing\TestingTask;

it('runs test suite and assertions successfully', function () {
    UserRoute::Index->register(
        fn () => ApiResponse::make(0)->toResponse(request())
    );

    UserRoute::tests($this, [UserRoute::Index])->run();
    UserRoute::tests($this, [UserRoute::Index])->tasks()->each->run();

    expect(true)->toBeTrue();
});

it('returns correct assertion methods for routes', function (string $name, string $expected) {
    $assertions = UserRoute::tests($this)->tasks()
        ->keyBy(fn (TestingTask $assertion) => $assertion->testing->route->name);

    expect($assertions[$name]->method)->toBe($expected, $name);

})->with([
    [UserRoute::Create->name, TestingMethod::Created->value],
    [UserRoute::Update->name, TestingMethod::Updated->value],
    [UserRoute::Delete->name, TestingMethod::Deleted->value],
    [UserRoute::Index->name, TestingMethod::Successful->value],
    [UserRoute::List->name, TestingMethod::Successful->value],
]);
