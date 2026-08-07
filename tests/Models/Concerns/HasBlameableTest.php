<?php

declare(strict_types=1);

use App\Factories\UserFactory;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

it('fills blameable fields on create', function () {
    $user = UserFactory::new()->create();
    $this->actingAs($user);

    expect(UserFactory::new()->create())
        ->created_by->toBe($user->id)
        ->updated_by->toBe($user->id);
});

it('updates updated_by on update', function () {
    $user = UserFactory::new()->create();
    $this->actingAs($user);

    $model = UserFactory::new()->create();

    for ($i = 0; $i < 5; $i++) {
        $user = UserFactory::new()->create();
        $this->actingAs($user);
        $model->update(['username' => uniqid()]);
        expect($model->updated_by)->toBe($user->id);
    }
});
