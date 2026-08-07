<?php

declare(strict_types=1);

use Pin\IdGenerator\Id;
use Pin\IdGenerator\IdGenerator;
use Pin\IdGenerator\IdGeneratorInterface;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\InteractsWithDatabase;

uses(
    InteractsWithDatabase::class,
    InteractsWithRedis::class,
);

it('generates custom ids', function () {
    app()->instance('pin.id.uniqid', new class implements IdGeneratorInterface
    {
        public function generate(int $count = 1): array|string
        {
            $ids = [];

            for ($i = 0; $i < $count; $i++) {
                $ids[] = uniqid();
            }

            return $count === 1
                ? $ids[0]
                : $ids;
        }
    });

    config(['pin.id-generator.default' => 'uniqid']);

    expect(array_unique(Id::generate(10000)))->toHaveCount(10000);
});

it('generates timestamp ids', function () {
    config(['pin.id-generator.default' => IdGenerator::Timestamp]);
    expect(Id::generate())->toBeGreaterThan(999999);

    config(['pin.id-generator.default' => 'timestamp']);

    expect(array_unique(Id::generate(10000)))->toHaveCount(10000);
});

it('generates redis ids', function () {
    config(['pin.id-generator.default' => IdGenerator::Redis]);
    expect(Id::generate())->toBe(1);

    config(['pin.id-generator.default' => 'redis']);
    expect(Id::generate(2))->toBe([2, 3]);
});

it('generates snowflake ids', function () {
    config(['pin.id-generator.default' => IdGenerator::Snowflake]);

    $id = Id::generate();
    expect(
        $this->app['pin.id.snowflake']->parseId($id)['timestamp']
    )->toBeGreaterThan(time() - 10);

    config(['pin.id-generator.default' => 'snowflake']);
    expect(array_unique(Id::generate(10000)))->toHaveCount(10000);
});
