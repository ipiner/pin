<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\User\Actions\CreateUserAction;
use App\Modules\User\UserController;
use App\Routes\User\UserRoute;
use Pin\Module\ModuleInspector;
use Pin\Support\Facades\RuntimeCache;

beforeEach(function () {
    RuntimeCache::flush();
});

it('exports metadata for classes inside app modules', function () {
    $inspector = ModuleInspector::make(CreateUserAction::class);

    expect($inspector->toArray())->toBe([
        'basename' => 'CreateUserAction',
        'parts' => ['App', 'Modules', 'User', 'Actions', 'CreateUserAction'],
        'module' => [
            'name' => 'User',
            'namespace' => 'App\\Modules\\User',
        ],
        'domain' => 'User',
        'controller' => UserController::class,
        'model' => User::class,
        'factory' => 'Database\\Factories\\UserFactory',
    ]);
});

it('memoizes inspectors by class string', function () {
    $first = ModuleInspector::make(UserRoute::class);
    $second = ModuleInspector::make(UserRoute::class);

    expect($second)->toBe($first);
});

it('shares inspectors across route instances and normalized class names', function () {
    $inspector = ModuleInspector::make(UserRoute::class);

    expect(ModuleInspector::make(UserRoute::Create))->toBe($inspector)
        ->and(ModuleInspector::make('\\'.UserRoute::class))->toBe($inspector)
        ->and((new ModuleInspector('\\'.UserRoute::class))->toArray())
        ->toBe($inspector->toArray());
});

it('isolates inspector caches from other runtime values', function () {
    RuntimeCache::put(UserRoute::class, 'other value');

    expect(ModuleInspector::make(UserRoute::class))->toBeInstanceOf(ModuleInspector::class)
        ->and(RuntimeCache::get(UserRoute::class))->toBe('other value');
});

it('isolates inspector subclasses', function () {
    $base = ModuleInspector::make(UserRoute::class);
    $extension = new class(UserRoute::class) extends ModuleInspector
    {
        public function domain(): string
        {
            return 'Custom';
        }
    };
    $custom = $extension::make(UserRoute::class);

    expect($custom)->toBeInstanceOf($extension::class)->not->toBe($base)
        ->and($custom->domain())->toBe('Custom')
        ->and($extension::make(UserRoute::Create))->toBe($custom);
});

it('resolves existing candidates and falls back to the last class', function () {
    $inspector = $this->invoker(new ModuleInspector(UserRoute::class));

    expect($inspector->resolveFirstExistingClass(['MissingClass', User::class, 'Fallback']))
        ->toBe(User::class)
        ->and($inspector->resolveFirstExistingClass(['MissingClass', 'Fallback']))
        ->toBe('Fallback')
        ->and($inspector->resolveFirstExistingClass([]))->toBeNull();
});
