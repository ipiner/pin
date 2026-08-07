<?php

declare(strict_types=1);

use App\Factories\UserFactory;
use App\Models\User;
use App\Routes\User\UserRoute;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

it('finds model instances', function () {
    $t = UserRoute::Index->testing($this)
        ->withFactory(UserFactory::class);

    $invoker = $this->invoker($t);

    expect($t->modelClass)->toBe(User::class)
        ->and($invoker->findModel(-1))->toBeNull()
        ->and($invoker->findModel(new User()))->toBeInstanceOf(User::class)
        ->and($invoker->findModel(null))->toBeInstanceOf(User::class);
});
