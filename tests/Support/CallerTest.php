<?php

declare(strict_types=1);

use Pin\Support\Caller;

afterEach(function () {
    Caller::setApplicationFileResolver(null);
});

it('can override application file resolver', function () {
    Caller::setApplicationFileResolver(
        fn ($file) => str_contains($file, 'custom-app')
    );

    expect(
        $this->invoker(Caller::class)->isApplicationFile('/custom-app/User.php')
    )->toBeTrue();

    expect(
        $this->invoker(Caller::class)->isApplicationFile('/app/User.php')
    )->toBeFalse();
});

it('returns fallback caller when no application file exists', function () {
    $backtrace = [
        [
            'file' => '/project/vendor/a.php',
            'line' => 1,
        ],
    ];

    $result = Caller::resolve($backtrace);

    expect($result['file'])->toBe('/project/vendor/a.php')
        ->and($result['line'])->toBe(1);
});

it('returns first application file from backtrace', function () {
    $backtrace = [
        [
            'file' => '/project/vendor/laravel/framework/src/xxx.php',
            'line' => 10,
        ],
        [
            'file' => '/project/app/Services/UserService.php',
            'line' => 123,
        ],
    ];

    $result = Caller::resolve($backtrace);

    expect($result['file'])->toBe('/project/app/Services/UserService.php')
        ->and($result['line'])->toBe(123);
});

it('excludes vendor directories without excluding similar file names', function (string $file) {
    $frame = ['file' => $file, 'line' => 12, 'function' => 'handle'];

    expect(Caller::resolve([
        ['file' => 'C:\\project\\vendor\\package\\File.php', 'line' => 1],
        ['file' => 'vendor/package/File.php', 'line' => 2],
        $frame,
    ]))->toBe($frame);
})->with(['/vendor-project/app/Service.php', '/project/app/vendor_report.php']);

it('skips incomplete application frames', function () {
    $frame = ['file' => '/project/app/Service.php', 'line' => 12];

    expect(Caller::resolve([
        ['file' => '/project/app/incomplete.php'],
        $frame,
    ]))->toBe($frame);
});
