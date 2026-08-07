<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Pin\IdGenerator\IdGenerator;
use Pin\Models\Concerns\RedisId;
use Pin\Models\Concerns\SnowflakeId;
use Pin\Models\Concerns\TimestampId;
use Pin\Testing\Concerns\InteractsWithRedis;

uses(InteractsWithRedis::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/migrations');
});

it('generates timestamp id', function () {
    $id = TestTimestampIdModel::create()->id;
    expect($id)->toBeGreaterThan(time() - 10);
});

it('generates redis id', function () {
    expect(TestRedisIdModel::create()->id)->toBe(1);

    IdGenerator::Redis->generate();
    expect(TestRedisIdModel::create()->id)->toBe(3);
});

it('generates snowflake id', function () {
    $id = TestSnowflakeIdModel::create()->id;
    $generated = (int) IdGenerator::Snowflake->generate();

    expect($id)->toBeLessThan($generated);
});

class TestTimestampIdModel extends Model
{
    use TimestampId;

    public $timestamps = false;

    protected $table = 'timestamp_id';
}

class TestRedisIdModel extends Model
{
    use RedisId;

    public $timestamps = false;

    protected $table = 'redis_id';
}

class TestSnowflakeIdModel extends Model
{
    use SnowflakeId;

    public $timestamps = false;

    protected $table = 'snowflake_id';
}
