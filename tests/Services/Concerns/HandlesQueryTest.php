<?php

declare(strict_types=1);

use App\Factories\UserFactory;
use App\Models\User;
use Illuminate\Support\Str;
use Pin\Models\Queryable\Queryable;
use Pin\Services\ModelService;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

beforeEach(function () {
    $this->service = new ModelService(User::class);
    $this->username = Str::random();

    UserFactory::new(['username' => $this->username])->create();
    UserFactory::new()->create();
});

it('returns paginated results', function () {
    config(['pin.pagination.available_page_sizes' => []]);
    $this->app['request']->query->set('page_size', 1);

    $data = $this->service->pagination()->toArray();

    expect($data['total'])->toBe(2)
        ->and($data['total_page'])->toBe(2)
        ->and($data['items'])->toHaveCount(1)
        ->and($data['items'][0]->username)->not->toBe($this->username);

    $data = $this->service->pagination(
        Queryable::fromPayload(['username' => $this->username], ['username' => 'eq'])
    )->toArray();

    expect($data['total'])->toBe(1)
        ->and($data['total_page'])->toBe(1)
        ->and($data['items'])->toHaveCount(1)
        ->and($data['items'][0]->username)->toBe($this->username);
});

it('returns all results when paging is disabled', function () {
    $data = $this->service->context('paging', false)
        ->pagination()
        ->toArray();

    expect($data['total'])->toBe(2)
        ->and($data['total_page'])->toBe(1)
        ->and($data['items'])->toHaveCount(2);

    $this->app['request']->query->set('username', $this->username);
    $data = $this->service->pagination(['username' => 'q:eq'])->toArray();

    expect($data['total'])->toBe(1)
        ->and($data['total_page'])->toBe(1)
        ->and($data['items'])->toHaveCount(1)
        ->and($data['items'][0]->username)->toBe($this->username);
});
