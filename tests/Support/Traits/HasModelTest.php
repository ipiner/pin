<?php

declare(strict_types=1);

use App\Models\User;
use Pin\Support\Traits\HasModel;

beforeEach(function () {
    $this->service = new HasModelService();

    $this->invoker = $this->invoker($this->service);
});

it('returns model attributes', function () {
    $this->invoker->bootModel();

    expect($this->invoker->attributes())->toBe([]);

    $this->service->withModel(User::class);

    if (is_file(database_path('schemas/testing/users.php'))) {
        expect($this->service->attributes()['created_at'])->toBe('Created At');
    } else {
        expect($this->service->attributes())->toBe([]);
    }
});

it('boots model', function () {
    $this->invoker->bootModel();

    expect($this->service->modelClass)->toBe('App\Models\HasModel');

    $this->invoker->bootModel(User::class);

    expect($this->service->modelClass)->toBe('App\Models\HasModel');
});

it('sets and resolves model', function () {
    $this->service->withModel(User::class);

    expect($this->invoker->modelClass)->toBe(User::class)
        ->and($this->invoker->model())->toBeInstanceOf(User::class);
});

it('throws exception when model class does not exist', function () {
    $this->invoker->bootModel();

    $this->expectExceptionMessage('Class "App\Models\HasModel" not found');
    $this->invoker->model();
});

class HasModelService
{
    use HasModel;
}
