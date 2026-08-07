<?php

declare(strict_types=1);

use App\Factories\AdminFactory;
use App\Factories\MenuFactory;
use App\Factories\UserFactory;
use App\Models\Menu;
use Pin\Models\Cache\SimpleCacher;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\InteractsWithDatabase;
use Pin\Tests\Models\Models\Admin;
use Pin\Tests\Models\Models\User;

uses(InteractsWithDatabase::class, InteractsWithRedis::class);

it('gets cached model items', function () {
    $cacher = new SimpleCacher(Admin::class);
    $id = time();

    expect($cacher->get($id))->toBeNull();

    AdminFactory::new(['id' => $id])->create();
    $item = $cacher->get($id);
    expect($item->id)->toBe($id)
        ->and($cacher->ttl(100)->get($id))->toBe($item);

    // cache all
    expect((new SimpleCacher(Menu::class))->get($id))->toBeNull();

    // cache none
    $cacher = new SimpleCacher(User::class);

    expect($cacher->get($id))->toBeNull();

    UserFactory::new(['id' => $id])->create();

    expect($cacher->get($id)->id)->toBe($id);
});

it('gets all cached models', function () {
    $cacher = new SimpleCacher(Menu::class);

    expect($cacher->getAll())->toBeEmpty();

    $id = time();

    MenuFactory::new(['id' => $id])->create();

    expect($cacher->get($id)->id)->toBe($id)
        ->and($cacher->getAll()[$id]->id)->toBe($id);
});
