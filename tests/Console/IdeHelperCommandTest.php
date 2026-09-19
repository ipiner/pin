<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Pin\Console\Commands\IdeHelperCommand;

it('runs IDE helpers in order and stops on failure', function (int $failure) {
    $commands = [
        'ide-helper:eloquent',
        'ide-helper:generate',
        'ide-helper:meta',
        'ide-helper:models',
    ];
    $calls = [];

    foreach ($commands as $index => $name) {
        $signature = $name;

        if ($name === 'ide-helper:models') {
            $signature .= ' {--nowrite} {--write-mixin}';
        }

        Artisan::command($signature, function () use ($name, $index, $failure, &$calls) {
            $calls[] = $name;

            if ($name === 'ide-helper:models') {
                expect($this->option('nowrite'))->toBeTrue()
                    ->and($this->option('write-mixin'))->toBeTrue();
            }

            return $index === $failure ? 2 : 0;
        });
    }

    $this->artisan(IdeHelperCommand::class)->assertExitCode($failure < 0 ? 0 : 2)->run();

    expect($calls)->toBe($failure < 0 ? $commands : array_slice($commands, 0, $failure + 1));
})->with([
    'success' => -1,
    'eloquent failure' => 0,
    'generate failure' => 1,
    'meta failure' => 2,
    'models failure' => 3,
]);
