<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

use Illuminate\Contracts\Support\DeferrableProvider;
use Pin\Support\ServiceProvider;

/**
 * ID 生成器服务提供者
 */
class IdGeneratorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // 时间戳生成器
        $this->app->singleton(
            IdGenerator::Timestamp->name(),
            fn () => new TimestampId(config('pin.id-generator.timestamp.start_timestamp')));

        // Redis 自增生成器
        $this->app->singleton(
            IdGenerator::Redis->name(),
            fn () => new RedisId(config('pin.id-generator.redis'))
        );

        // Snowflake 算法生成器
        $this->app->singleton(
            IdGenerator::Snowflake->name(),
            fn () => new SnowflakeId(config('pin.id-generator.snowflake'))
        );
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            IdGenerator::Redis->name(),
            IdGenerator::Timestamp->name(),
            IdGenerator::Snowflake->name(),
        ];
    }
}
