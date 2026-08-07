<?php

declare(strict_types=1);

use App\Factories\UserFactory;
use App\Routes\User\UserRoute;

it('handles factory class assignment and detection', function () {
    $t = UserRoute::Index->testing($this);
    $invoker = $this->invoker($t);

    expect($invoker->factoryClass)->toBe('Database\\Factories\\UserFactory')
        ->and($invoker->hasFactory())->toBeFalse();

    $t->withFactory(UserFactory::class);

    expect($invoker->factoryClass)->toBe(UserFactory::class)
        ->and($invoker->hasFactory())->toBeTrue()
        ->and($invoker->factory())->toBeInstanceOf(UserFactory::class);
});

it('throws exception when factory class does not exist', function () {
    $this->invoker(UserRoute::List->testing($this))->factory();
})->throws('Database\\Factories\\UserFactory');
