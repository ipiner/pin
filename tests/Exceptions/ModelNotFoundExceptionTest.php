<?php

declare(strict_types=1);

use App\Models\User;
use Pin\Errors\Errors;
use Pin\Exceptions\ModelNotFoundException;
use Pin\Support\Facades\RuntimeCache;

beforeEach(function () {
    $this->databasePath = $this->app->databasePath();
    $this->app->useDatabasePath(sys_get_temp_dir().'/pin-model-exception-'.uniqid());
    RuntimeCache::flush();

    $this->e = new ModelNotFoundException(
        (new Illuminate\Database\Eloquent\ModelNotFoundException())
            ->setModel(User::class, 1)
    );
});

afterEach(function () {
    $this->app['files']->deleteDirectory($this->app->databasePath());
    $this->app->useDatabasePath($this->databasePath);
    RuntimeCache::flush();
});

it('gets caller information', function () {
    $caller = $this->e->getCaller();

    expect($caller['file'])->not()->toBe(__FILE__);
});

it('allows overriding model caller information', function () {
    $caller = $this->e->getCaller();

    expect($this->e->getCaller('custom.php', 123))
        ->toBe(['file' => 'custom.php', 'line' => 123])
        ->and($this->e->getCaller(line: 123))
        ->toBe(['file' => $caller['file'], 'line' => 123]);
});

it('uses the original location when the trace is empty', function () {
    $previous = new Illuminate\Database\Eloquent\ModelNotFoundException();
    $previous->setModel(User::class);
    new ReflectionProperty(Exception::class, 'trace')->setValue($previous, []);

    $exception = new ModelNotFoundException($previous);

    expect($exception->getCaller())->toBe([
        'file' => $previous->getFile(),
        'line' => $previous->getLine(),
    ]);
});

it('initializes model not found exception', function () {
    expect($this->e->getStatusCode())->toBe(404)
        ->and($this->e->getCode())->toBe(Errors::ModelNotFound->code())
        ->and($this->e->getMessage())->toBe('User not found')
        ->and($this->e->getContext()['message'])->toContain(User::class);
});

it('resolves model labels', function () {
    expect($this->invoker($this->e)->modelLabel(User::class))
        ->toBe('User')
        ->and($this->invoker($this->e)->modelLabel('UserAddress'))
        ->toBe('User Address');
});

it('uses configured model labels', function () {
    $directory = database_path('schemas/testing');
    $this->app['files']->ensureDirectoryExists($directory);
    $this->app['files']->put($directory.'/users.php', "<?php return ['label' => '成员'];");
    RuntimeCache::flush();

    $exception = new ModelNotFoundException(
        (new Illuminate\Database\Eloquent\ModelNotFoundException())->setModel(User::class)
    );

    expect($exception->getMessage())->toBe('成员 not found');
});
