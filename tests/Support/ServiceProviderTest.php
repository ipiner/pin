<?php

declare(strict_types=1);

use Pin\Support\ServiceProvider;

it('merges config recursively', function () {
    $path = makeConfigFile([
        'db' => [
            'host' => '127.0.0.1',
            'port' => 3306,
        ],
    ]);

    config()->set('test', [
        'db' => [
            'host' => 'localhost',
        ],
    ]);

    $provider = new class($this->app) extends ServiceProvider
    {
        public function callMerge($path, $key)
        {
            $this->mergeConfigFrom($path, $key);
        }
    };

    $provider->callMerge($path, 'test');

    expect(config('test'))->toBe([
        'db' => [
            'host' => 'localhost',
            'port' => 3306,
        ],
    ]);
});

it('merges deep nested config', function () {
    $path = makeConfigFile([
        'a' => [
            'b' => [
                'c' => 1,
                'd' => 2,
            ],
        ],
    ]);

    config()->set('test', [
        'a' => [
            'b' => [
                'c' => 999,
            ],
        ],
    ]);

    $provider = new class($this->app) extends ServiceProvider
    {
        public function callMerge($path, $key)
        {
            $this->mergeConfigFrom($path, $key);
        }
    };

    $provider->callMerge($path, 'test');

    expect(config('test'))->toBe([
        'a' => [
            'b' => [
                'c' => 999,
                'd' => 2,
            ],
        ],
    ]);
});

it('prefers user config over default config', function () {
    $path = makeConfigFile([
        'app' => [
            'debug' => false,
        ],
    ]);

    config()->set('test', [
        'app' => [
            'debug' => true,
        ],
    ]);

    $provider = new class($this->app) extends ServiceProvider
    {
        public function callMerge($path, $key)
        {
            $this->mergeConfigFrom($path, $key);
        }
    };

    $provider->callMerge($path, 'test');

    expect(config('test.app.debug'))->toBeTrue();
});

function makeConfigFile(array $data): string
{
    $path = sys_get_temp_dir().'/test_config_'.uniqid().'.php';

    file_put_contents(
        $path,
        '<?php return '.var_export($data, true).';'
    );

    return $path;
}
