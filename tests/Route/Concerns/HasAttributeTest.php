<?php

declare(strict_types=1);

use App\Routes\User\UserRoute;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Pin\Route\Attributes\Middleware;
use Pin\Route\Attributes\TestingMethod;
use Pin\Route\Attributes\Title;

it('resolves route attribute', function () {
    $o = UserRoute::Index->attribute(TestingMethod::class);

    expect($o->value)
        ->toBe(Pin\Route\Testing\TestingMethod::Successful->value)
        ->and(UserRoute::Index->attribute(TestingMethod::class))->toBe($o) // memorize

         // description
        ->and(UserRoute::Update->attribute(Title::class))->toBeNull()
        ->and(UserRoute::List->attribute(Title::class)->value)->toBe('用户列表')

        // middleware
        ->and(
            UserRoute::List->attribute(Middleware::class)->value
        )->toBe(TrimStrings::class);
});
