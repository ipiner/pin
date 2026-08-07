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
